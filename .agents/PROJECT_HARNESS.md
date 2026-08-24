# TRATIX · System Developer Harness (Context & Token Optimizer)
> **Purpose**: Single-source-of-truth reference for AI coding agents to understand the entire architecture, database, conventions, and business rules in under 1,500 tokens without re-reading multiple source files.

---

## 1. Stack & Directory Layout
- **Framework**: Laravel 12 (PHP 8.2+), Blade, Alpine.js, Tailwind CSS (Dark Slate `#0F172A` / Emerald `#10B981`), DomPDF.
- **Directories**:
  - `app/Models/`: `Contract`, `Party`, `Signature`, `ContractVersion`, `ClauseProposal`, `AuditEvent`, `Consent`, `ContractDocument`, `DocumentRequirement`, `Referral`, `User`.
  - `app/Services/`: `ContractService`, `ContractWorkflowService`, `SignatureService`, `SealingService`, `NegotiationService`, `ContractLegalValidator`, `SpanishTaxIdValidator`, `LatinAmericanTaxIdValidator`, `EuVatValidator`, `CountryLegalConfig`, `TransactionResolver`, `ClauseBuilder`, `PartyRightsObligations`, `DocumentGuidanceService`, `CreditService`, `ReferralService`, `ContractSharing`, `TsaService`, `Billing/` (`BillingGateway`, `DemoGateway`, `StripeGateway`).
  - `app/Http/Controllers/`: `ContractController`, `ContractWorkflowController`, `SignatureController`, `ReviewController`, `NegotiationController`, `DocumentController`, `BillingController`, `BillingWebhookController`, `PublicVerificationController`, `ReferralController`, `AdminController`.
  - `routes/`: `web.php` (all app & public endpoints), `auth.php` (Breeze), `console.php` (Schedules: backup 03:00, reminders 09:00, summary monthly).
  - `storage/app/private/`: Signed PDFs, signature PNGs, uploaded contract documents, evidence sidecars (`contracts/{ref}/...`).

---

## 2. Database Schema Matrix

| Model / Table | Key Columns | Relationships / Invariants |
|---|---|---|
| `User` / `users` | `id`, `name`, `email`, `plan` (free/pro/business), `is_admin`, `credits`, `referral_code` | `hasMany(Contract)` |
| `Contract` / `contracts` | `id`, `user_id`, `creator_role` (vendedor/comprador), `reference` (C-YYYY-NNNN, unique), `contract_type`, `transaction_type` (b2b/b2c/c2c/c2b), `jurisdiction` (nacional/intracomunitario/internacional), `applicable_law` (ES/AR/MX/CO/CL/PE/UY), `title`, `price_amount`, `tax_amount`, `total_amount`, `status`, `clauses` (json), `access_token` (uuid), `access_token_expires_at`, `final_pdf_path`, `final_hash`, `sealed_at`, `signed_version` | BelongsTo(User), HasMany(Party, ContractVersion, ClauseProposal, Signature, Consent, AuditEvent, ContractDocument). Scopes: `seller()`, `buyer()`, `counterparty()`, `latestVersion()`, `tokenIsValid()`. |
| `Party` / `parties` | `id`, `contract_id`, `role` (vendedor/comprador), `party_type` (particular/autonomo/sociedad), `full_name`, `company_name`, `tax_id`, `tax_id_country`, `country`, `address`, `postal_code`, `city`, `email`, `phone`, `registered_vat`, `acting_in_own_name` | BelongsTo(Contract). Maximum 2 parties per contract (seller + buyer). |
| `Signature` / `signatures` | `id`, `contract_id`, `contract_version_id`, `party_id`, `party_role`, `signer_name`, `signer_email`, `signature_type` (fes-canvas/fes-click), `signature_image_path`, `signed_at`, `ip`, `user_agent`, `otp_verified`, `consent_text` | BelongsTo(Contract). **DB Unique constraint: `(contract_id, party_role)`**. |
| `ContractVersion` | `id`, `contract_id`, `version` (int), `hash` (SHA-256 canonical JSON), `clauses` (json), `frozen_at`, `frozen_by` | Immutable once frozen at transition to `lista_para_firma`. |
| `ClauseProposal` | `id`, `contract_id`, `contract_version_id`, `clause_key`, `original_text`, `proposed_text`, `proposed_by`, `status` (pending/approved/rejected) | BelongsTo(Contract). Track-changes negotiation in `borrador`/`en_revision`. |
| `AuditEvent` | `id`, `contract_id`, `event`, `actor_type`, `actor_id`, `ip`, `detail`, `payload` (json), `created_at` | Append-only tamper log. |
| `ContractDocument`| `id`, `contract_id`, `requirement_key`, `filename`, `path`, `mime`, `size`, `status` (uploaded/validated) | Attached checklist files in `private/documents/{ref}/`. |
| `Referral` | `id`, `referrer_id`, `referred_id`, `code` | Tracks referral rewards between users. |

---

## 3. Workflow State Machine & Permissions

```
[borrador] ──sendReview()──▶ [en_revision] ──acceptFinal()──▶ [lista_para_firma] (version frozen)
    │                               ▲                                │
    └─────────acceptFinal()─────────┴─────────sendSignature()────────▼
                                                                [en_firma] ──all signed──▶ [firmado] (sealed)
(Any non-signed state can transition to [cancelado])
```

- **Authenticated Endpoints** (`auth`, `verified`):
  - `GET /dashboard` → `ContractController@index`
  - `GET/POST /contracts` → `create`/`store` (gated by `CreditService@canCreate`)
  - `GET /contracts/{contract}` → `show` (Policy check: `view`)
  - `POST /contracts/{contract}/send-review` → `ContractWorkflowController@sendReview`
  - `POST /contracts/{contract}/accept-final` → `ContractWorkflowController@acceptFinal` (freezes version)
  - `POST /contracts/{contract}/send-signature` → `ContractWorkflowController@sendSignature`
  - `POST /contracts/{contract}/cancel` → `ContractWorkflowController@cancel`
  - `POST /contracts/{contract}/proposals[/{id}/approve|reject]` → `NegotiationController`
  - `POST /contracts/{contract}/documents` → `DocumentController@upload`
  - `GET /contracts/{contract}/documents/{doc}/download` → `DocumentController@download` (**Must check `$doc->contract_id === $contract->id`**)
- **Public Endpoints** (No login required, access by UUID `token` or `reference`):
  - `GET/POST /review/{token}` → Counterparty draft review & proposal submission.
  - `GET/POST /sign/{token}` → Counterparty FES signature + OTP verification + consent recording.
  - `POST /sign/{token}/otp` → Sends 6-digit OTP code to signer email (cached 10 min).
  - `GET /sign/{token}/download` → Public download of draft or signed PDF for token holder.
  - `GET /verify/{reference}` → Public hash verification against stored PDF SHA-256.

---

## 4. Key Domain Services & Contract Conventions

1. **`ContractLegalValidator`**: Validates tax IDs (ES: NIF/CIF/NIE, EU: VIES, LATAM: CUIT/RUT/RFC/NIT/RUC/DNI), economic arithmetic (`price + tax == total`), B2C consumer rights, and cross-border intra-EU rules.
2. **`ClauseBuilder`**: Generates structured array of clauses adapted to `contract_type`, `transaction_type`, and `applicable_law` (`ES`, `AR`, `MX`, `CO`, `CL`, `PE`, `UY`).
3. **`PartyRightsObligations`**: Generates plain-language rights and obligations per role.
4. **`SignatureService`**: Records signature, stores canvas PNG binary, records RGPD `Consent`, enforces 1 signature per role via DB unique index, and initiates sealing on completion.
5. **`SealingService`**: Generates final PDF, calculates final SHA-256, queries RFC 3161 TSA server, creates evidence payload sidecar, and locks row (`lockForUpdate`).
6. **`CreditService`**: Free tier = 2 contracts/month, Pro & Business = unlimited (`null`). Considers `$user->credits`.
7. **`ContractSharing`**: Produces WhatsApp (`wa.me`) and `mailto:` links directing counterparties to public routes (`/review/{token}`, `/sign/{token}`, `/sign/{token}/download`).

---

## 5. Testing & CLI Commands (Run via Laravel Sail)
> **Crucial Rule**: Always use `./vendor/bin/sail` or `docker compose exec laravel.test ...` for all CLI/PHP/Artisan executions.

- `./vendor/bin/sail php artisan test` — Run full PHPUnit suite (137 tests, 394 assertions passing).
- `./vendor/bin/sail pint` — Code style enforcement (PSR-12 / Laravel standards).
- `./vendor/bin/sail npm run build` — Build Vite frontend assets (production build).
- `./vendor/bin/sail php artisan app:backup` — Creates database and storage archive securely.
- `./vendor/bin/sail php artisan contracts:reminders` — Triggers automated signature/review reminders with deduplication.
