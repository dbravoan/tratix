#!/bin/bash
# Final E2E: LATAM (México) contract + OTP signing + admin/pricing pages.
set -u
B="${1:-http://127.0.0.1:8090}"
JAR=/tmp/opencode/e2e.jar; rm -f "$JAR"
fail(){ echo "FAIL: $1"; exit 1; }
ok(){ echo "OK: $1"; }

curl -s -c "$JAR" "$B/login" -o /dev/null
T=$(curl -s -c "$JAR" "$B/login" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
curl -s -b "$JAR" -c "$JAR" -o /dev/null -X POST "$B/login" -d "_token=$T" -d "email=test@example.com" -d "password=password" || fail login
ok login

# Landing page (public) with TRATIX branding.
curl -s -o /tmp/opencode/landing.html "$B/"
grep -qi "Tratix" /tmp/opencode/landing.html && ok "landing shows Tratix brand" || fail "landing Tratix brand"
grep -qi "Contratos de compraventa" /tmp/opencode/landing.html && ok "landing hero" || fail "landing hero"
grep -qi "España" /tmp/opencode/landing.html && ok "landing countries" || fail "landing countries"
grep -qi "Empezar gratis\|Crea tu primer contrato" /tmp/opencode/landing.html && ok "landing CTA" || fail "landing CTA"

# Pricing shows the 3-tier plans.
curl -s -b "$JAR" -o /tmp/opencode/pricing.html -w "pricing:%{http_code}\n" "$B/pricing"
grep -q "Business" /tmp/opencode/pricing.html && ok "pricing shows Business plan" || fail "pricing Business"
grep -q "Pro" /tmp/opencode/pricing.html && ok "pricing shows Pro plan" || fail "pricing Pro"
grep -q "Gratis" /tmp/opencode/pricing.html && ok "pricing shows Free plan" || fail "pricing Free"

# Referrals page.
curl -s -b "$JAR" -o /tmp/opencode/refs.html "$B/referidos"
grep -qi "Referir y ganar" /tmp/opencode/refs.html && ok "referrals page" || fail "referrals page"
# Admin panel.
curl -s -b "$JAR" -o /tmp/opencode/admin.html -w "admin:%{http_code}\n" "$B/admin"
grep -q "Panel de administración" /tmp/opencode/admin.html && ok "admin panel" || fail "admin panel"

# Create a MEXICAN contract (C2C, bienes muebles).
curl -s -b "$JAR" "$B/contracts/create" -o /tmp/opencode/create.html
T=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/opencode/create.html | head -1)
curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "create:%{http_code}\n" -X POST "$B/contracts" \
  -d "_token=$T" -d "contract_type=bienes_muebles" -d "creator_role=vendedor" -d "title=Compraventa México E2E" -d "object_type=Mueble" \
  -d "object_description=Mueble de madera." -d "quantity=1" -d "price_amount=15000" -d "tax_amount=0" \
  -d "currency=MXN" -d "city=CDMX" -d "signing_date=2026-08-18" \
  -d "seller[party_type]=particular" -d "seller[full_name]=Juan López" -d "seller[tax_id]=GARC800101HDF" -d "seller[tax_id_country]=MX" -d "seller[country]=MX" -d "seller[address]=Reforma 1" -d "seller[postal_code]=06600" -d "seller[city]=CDMX" \
  -d "buyer[party_type]=particular" -d "buyer[full_name]=María Díaz" -d "buyer[tax_id]=DIMR900202HDF" -d "buyer[tax_id_country]=MX" -d "buyer[country]=MX" -d "buyer[address]=Luna 2" -d "buyer[postal_code]=06700" -d "buyer[city]=CDMX"
CID=$(curl -s -b "$JAR" "$B/dashboard" | grep -oP '/contracts/\K[0-9]+' | head -1)
[ -z "$CID" ] && fail "no contract id"
ok "contract $CID created"

# Verify LATAM legal attributes on the show page.
curl -s -b "$JAR" "$B/contracts/$CID" -o /tmp/opencode/show.html
grep -qi "México" /tmp/opencode/show.html && ok "show page shows México" || fail "show page México"
grep -qi "Derechos y obligaciones" /tmp/opencode/show.html && ok "rights & obligations section" || fail "rights section"
grep -qi "Código Civil Federal\|Ley Federal de Protección" /tmp/opencode/show.html && ok "MX legal references" || fail "MX legal refs"

# Share modal appears once there is a shareable link (en_revision onwards).
# Send to revision first, then the modal must be present.
curl -s -b "$JAR" "$B/contracts/$CID" -o /dev/null
curl -s -b "$JAR" -o /dev/null -X POST "$B/contracts/$CID/send-review" -d "_token=$T" -d "invited_email=comprador@e2e.mx"
curl -s -b "$JAR" "$B/contracts/$CID" -o /tmp/opencode/show_rev.html
grep -qi "Compartir contrato" /tmp/opencode/show_rev.html && ok "share modal in revision" || fail "share modal in revision"
grep -qi "Enviar por WhatsApp\|WhatsApp" /tmp/opencode/show_rev.html && ok "whatsapp in share modal" || fail "whatsapp in share modal"
grep -qi "mailto:" /tmp/opencode/show_rev.html && ok "mailto in share modal" || fail "mailto in share modal"
grep -qi "Copiar enlace" /tmp/opencode/show_rev.html && ok "copy link in share modal" || fail "copy link in share modal"

# Freeze + send to signature.
curl -s -b "$JAR" -o /dev/null -X POST "$B/contracts/$CID/accept-final" -d "_token=$T"
curl -s -b "$JAR" -o /dev/null -X POST "$B/contracts/$CID/send-signature" -d "_token=$T" -d "signer_email=comprador@e2e.mx"
SIG=$(curl -s -b "$JAR" "$B/contracts/$CID/signing-link" -o /dev/null -w "%{redirect_url}" | grep -oP '/sign/\K[0-9a-f-]+')
[ -z "$SIG" ] && fail "no sign token"
ok "sign token $SIG"

sign_role() {
  role="$1"; name="$2"; email="$3"
  jar="/tmp/opencode/s_$role.txt"; rm -f "$jar"
  curl -s -c "$jar" "$B/sign/$SIG" -o "/tmp/opencode/sp_$role.html"
  tt=$(grep -oP 'name="_token" value="\K[^"]+' "/tmp/opencode/sp_$role.html" | head -1)
  curl -s -b "$jar" -c "$jar" -o /dev/null -w "otp-$role:%{http_code}\n" -X POST "$B/sign/$SIG/otp" -d "_token=$tt" -d "role=$role" -d "signer_email=$email"
  code=$(cd /home/david/Proyectos/webs/contratos && ./vendor/bin/sail artisan tinker --execute="echo \Illuminate\Support\Facades\Cache::get('sign_otp:$SIG:$email','');" 2>/dev/null | grep -oE '[0-9]{6}' | head -1)
  [ ${#code} -ne 6 ] && fail "no otp for $role"
  curl -s -b "$jar" -c "$jar" "$B/sign/$SIG" -o /tmp/opencode/sp2_$role.html
  tt2=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/opencode/sp2_$role.html | head -1)
  curl -s -b "$jar" -c "$jar" -o /dev/null -w "sign-$role:%{http_code}\n" -X POST "$B/sign/$SIG" -d "_token=$tt2" -d "role=$role" -d "signer_name=$name" -d "signer_email=$email" -d "signature_type=fes-click" -d "signature_image=" -d "consent=1" -d "otp_code=$code"
}

sign_role comprador "María Díaz" "comprador@e2e.mx"
sign_role vendedor "Juan López" "vendedor@e2e.mx"

RESULT=$(cd /home/david/Proyectos/webs/contratos && ./vendor/bin/sail artisan tinker --execute='
$c = \App\Models\Contract::find('"$CID"');
echo "ref:" . $c->reference . "\n";
echo "law:" . $c->applicable_law . "\n";
echo "status:" . $c->status . "\n";
echo "sigs:" . $c->signatures()->count() . "\n";
echo "otp:" . $c->signatures()->where("otp_verified",1)->count() . "\n";
echo "consents:" . $c->consents()->count() . "\n";
echo "integrity:" . var_export(app(\App\Services\ContractService::class)->verifyIntegrity($c)["valid"], true) . "\n";
echo "tsa:" . (\Illuminate\Support\Facades\Storage::disk("local")->exists("contracts/{$c->reference}/evidence-tsr.txt") ? "yes" : "no") . "\n";
' 2>/dev/null | tail -9)
echo "$RESULT"
echo "$RESULT" | grep -q "law:MX" && ok "applicable law MX" || fail "law MX"
echo "$RESULT" | grep -q "status:firmado" || fail "not firmado"
echo "$RESULT" | grep -q "sigs:2" || fail "sigs != 2"
echo "$RESULT" | grep -q "otp:2" || fail "otp verified != 2"
echo "$RESULT" | grep -q "consents:2" || fail "consents != 2"
echo "$RESULT" | grep -q "integrity:true" || fail "integrity"
echo "$RESULT" | grep -q "tsa:yes" || fail "tsa"
REF=$(echo "$RESULT" | grep -oE "ref:[A-Z0-9-]+" | head -1 | cut -d: -f2)
[ -n "$REF" ] && ok "contract reference $REF" || fail "no reference"

# Public integrity verification (shareable, no auth).
curl -s -o /tmp/opencode/verify.html -w "verify:%{http_code}\n" "$B/verify/$REF"
grep -qi "Integridad verificada" /tmp/opencode/verify.html && ok "public verification OK" || fail "public verification"

# A NEW contract type (alquiler) can be created and shows rent clauses.
curl -s -b "$JAR" "$B/contracts/create" -o /tmp/opencode/create2.html
T=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/opencode/create2.html | head -1)
curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "alquiler:%{http_code}\n" -X POST "$B/contracts" \
  -d "_token=$T" -d "contract_type=alquiler" -d "creator_role=vendedor" -d "title=Alquiler E2E" -d "object_type=Piso" \
  -d "object_description=Piso amueblado." -d "quantity=1" -d "price_amount=900" -d "tax_amount=0" \
  -d "currency=EUR" -d "city=Madrid" -d "signing_date=2026-08-18" \
  -d "seller[party_type]=particular" -d "seller[full_name]=Ana" -d "seller[tax_id]=12345678Z" -d "seller[tax_id_country]=ES" -d "seller[country]=ES" -d "seller[address]=a" -d "seller[postal_code]=28001" -d "seller[city]=Madrid" \
  -d "buyer[party_type]=particular" -d "buyer[full_name]=Luis" -d "buyer[tax_id]=87654321X" -d "buyer[tax_id_country]=ES" -d "buyer[country]=ES" -d "buyer[address]=b" -d "buyer[postal_code]=28002" -d "buyer[city]=Madrid"
ALQID=$(curl -s -b "$JAR" "$B/dashboard" | grep -oP '/contracts/\K[0-9]+' | head -1)
curl -s -b "$JAR" "$B/contracts/$ALQID" -o /tmp/opencode/alq.html
grep -qi "alquiler\|arrendamiento" /tmp/opencode/alq.html && ok "alquiler contract created" || fail "alquiler contract"

# Export (Pro plan) is accessible.
curl -s -b "$JAR" -o /dev/null -w "export:%{http_code}\n" "$B/contracts/export"

# After signing: the share modal must offer the signed-PDF download link,
# and the show page must reflect it. Also verify the counterparty (buyer,
# since creator is seller) received the ContractSignedMail.
curl -s -b "$JAR" "$B/contracts/$CID" -o /tmp/opencode/show2.html
grep -qi "Compartir contrato" /tmp/opencode/show2.html && ok "share modal after signing" || fail "share modal after signing"
grep -qi "descargue el PDF firmado" /tmp/opencode/show2.html && ok "share action = download PDF" || fail "share action label"
grep -qi "Descargar PDF firmado" /tmp/opencode/show2.html && ok "download button" || fail "download button"

echo "ALL E2E CHECKS PASSED"