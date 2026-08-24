#!/bin/bash
# High-traffic concurrency smoke test against the Sail HTTP server.
# Fires 15 parallel signing requests for the same role and asserts the DB
# guard allows exactly one signature (no duplicates, no double sealing).
set -u
B="${1:-http://127.0.0.1:8090}"
JAR=/tmp/opencode/conc_jar.txt
SIG=""
cleanup(){ rm -f "$JAR"; }
trap cleanup EXIT

fail(){ echo "FAIL: $1"; exit 1; }
ok(){ echo "OK: $1"; }

# 1. Login as test user (created by the seeder on the Sail DB), with retries.
ok=""
for attempt in 1 2 3; do
  curl -s -c "$JAR" "$B/login" > /dev/null
  T=$(curl -s -c "$JAR" "$B/login" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
  redir=$(curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "%{redirect_url}" -X POST "$B/login" -d "_token=$T" -d "email=test@example.com" -d "password=password")
  if [[ "$redir" == */dashboard ]]; then ok="yes"; break; fi
  sleep 2
done
[ "$ok" = "yes" ] || fail "login"
ok login

# 2. Create a contract.
curl -s -b "$JAR" "$B/contracts/create" > /dev/null
T=$(curl -s -b "$JAR" "$B/contracts/create" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "%{http_code}" -X POST "$B/contracts" \
  -d "_token=$T" -d "contract_type=vehiculos" -d "title=Concurrencia" -d "object_type=Vehículo" \
  -d "object_description=Coche de concurrencia." -d "quantity=1" -d "price_amount=5000" -d "tax_amount=0" \
  -d "currency=EUR" -d "city=Madrid" -d "signing_date=2026-08-18" \
  -d "seller[party_type]=particular" -d "seller[full_name]=Ana García" -d "seller[tax_id]=12345678Z" -d "seller[tax_id_country]=ES" -d "seller[country]=ES" -d "seller[address]=C/ M 1" -d "seller[postal_code]=28001" -d "seller[city]=Madrid" \
  -d "buyer[party_type]=particular" -d "buyer[full_name]=Luis Pérez" -d "buyer[tax_id]=87654321X" -d "buyer[tax_id_country]=ES" -d "buyer[country]=ES" -d "buyer[address]=Av P 2" -d "buyer[postal_code]=28002" -d "buyer[city]=Madrid" > /tmp/opencode/conc_http_code 2>/dev/null
CID=$(curl -s -b "$JAR" "$B/dashboard" | grep -oP '/contracts/\K[0-9]+' | head -1)
[ -z "$CID" ] && fail "no contract id"
ok "contract $CID created"

# 3. Freeze + send to signature (with retries: the dev server can be flaky).
SIG=""
for attempt in 1 2 3; do
  curl -s -b "$JAR" -o /dev/null -X POST "$B/contracts/$CID/accept-final" -d "_token=$T"
  curl -s -b "$JAR" -o /dev/null -X POST "$B/contracts/$CID/send-signature" -d "_token=$T" -d "signer_email=comprador@ejemplo.com"
  SIG=$(curl -s -b "$JAR" "$B/contracts/$CID/signing-link" -o /dev/null -w "%{redirect_url}" | grep -oP '/sign/\K[0-9a-f-]+')
  [ -n "$SIG" ] && break
  sleep 2
done
[ -z "$SIG" ] && fail "no sign token"
ok "sign token $SIG"

# 4. OTP flow (only when enabled): request a code once per email and reuse it.
#    When disabled we validate the pure DB-level race safety of the signing.
OTP=$(grep -oE '^SIGNING_OTP_ENABLED=(.*)$' .env | cut -d= -f2)
OTP=${OTP:-true}
for role in comprador vendedor; do
  email="${role}@ejemplo.com"
  OTPARG=""
  if [ "$OTP" = "true" ]; then
    curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "otp-status:%{http_code}\n" -X POST "$B/sign/$SIG/otp" -d "_token=$T" -d "role=$role" -d "signer_email=$email" 2>/dev/null
    CODE=$(cd /home/david/Proyectos/webs/contratos && ./vendor/bin/sail artisan tinker --execute="echo \\Illuminate\\Support\\Facades\\Cache::get('sign_otp:$SIG:$email','');" 2>/dev/null | grep -oE '[0-9]{6}' | head -1)
    [ ${#CODE} -ne 6 ] && fail "no otp for $email (got '$CODE')"
    OTPARG="otp_code=$CODE"
    ok "otp received for $role"
  fi

  # 15 parallel sign attempts for the SAME role, each with its OWN session
  # (realistic: signer double-clicking / sharing the link). This exercises the
  # DB-level unique guard: exactly one signature per role must win.
  for i in $(seq 1 15); do
    (
      j="$i"
      jar="/tmp/opencode/conc_jar_${role}_${j}.txt"
      rm -f "$jar"
      curl -s -c "$jar" "$B/sign/$SIG" -o "/tmp/opencode/conc_page_${role}_${j}.html"
      tt=$(grep -oP 'name="_token" value="\K[^"]+' "/tmp/opencode/conc_page_${role}_${j}.html" | head -1)
      curl -s -b "$jar" -c "$jar" -o /dev/null -w "%{http_code}" -X POST "$B/sign/$SIG" \
        -d "_token=$tt" -d "role=$role" -d "signer_name=Test $role $j" -d "signer_email=$email" \
        -d "signature_type=fes-click" -d "signature_image=" -d "consent=1" $OTPARG \
        > "/tmp/opencode/conc_${role}_${j}.txt"
      rm -f "$jar" "/tmp/opencode/conc_page_${role}_${j}.html"
    ) &
  done
  wait
  codes=$(cat /tmp/opencode/conc_${role}_*.txt | sort | uniq -c)
  echo "  $role responses: $codes"
done

# 5. Assert DB state via Sail.
RESULT=$(cd /home/david/Proyectos/webs/contratos && ./vendor/bin/sail artisan tinker --execute='
$c = \App\Models\Contract::where("title","Concurrencia")->orderBy("id","desc")->first();
echo "sigs:".$c->signatures()->count()."\n";
echo "sellersigs:".$c->signatures()->where("party_role","vendedor")->count()."\n";
echo "buyersigs:".$c->signatures()->where("party_role","comprador")->count()."\n";
echo "status:".$c->status."\n";
echo "consents:".$c->consents()->count()."\n";
$r = app(\App\Services\ContractService::class)->verifyIntegrity($c);
echo "integrity:".var_export($r["valid"],true)."\n";
echo "tsa:".(\Illuminate\Support\Facades\Storage::disk("local")->exists("contracts/{$c->reference}/evidence-tsr.txt")?"yes":"no")."\n";
' 2>/dev/null | tail -7)
echo "$RESULT"
echo "$RESULT" | grep -q "sigs:2" && ok "exactly 2 signatures total" || fail "expected 2 signatures"
echo "$RESULT" | grep -q "sellersigs:1" || fail "expected 1 seller signature"
echo "$RESULT" | grep -q "buyersigs:1" || fail "expected 1 buyer signature"
echo "$RESULT" | grep -q "status:firmado" || fail "contract not firmado"
echo "$RESULT" | grep -q "consents:2" || fail "expected 2 consents"
echo "$RESULT" | grep -q "integrity:true" || fail "integrity broken"
echo "$RESULT" | grep -q "tsa:yes" || fail "no tsa token"
echo "ALL CONCURRENCY CHECKS PASSED"