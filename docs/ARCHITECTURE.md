# Arquitectura, integridad y escalabilidad

## 1. Visión general

Laravel 12 (Blade + Alpine + Tailwind) · MySQL · `barryvdh/laravel-dompdf`.
Aplicación vertical de contratos de compraventa con validación jurídica
española/europea, negociación, firma electrónica simple (eIDAS), sellado e
integridad verificable y guía de trámites por caso.

```
┌────────────────────────┐     ┌───────────────────────────────────────────┐
│  Creador (auth)        │     │  Contraparte (sin cuenta)                 │
│  Blade + Alpine        │     │  /review/{token} · /sign/{token}          │
└───────────┬────────────┘     └────────────────────┬──────────────────────┘
            │                                       │
┌───────────▼───────────────────────────────────────▼──────────────────────┐
│                        routes/web.php (web)                              │
├───────────────────────────────────────────────────────────────────────────┤
│ Controllers: Contract · Workflow · Negotiation · Document · Review · Sign│
├───────────────────────────────────────────────────────────────────────────┤
│ Servicios de dominio                                                      │
│  ContractService · ContractWorkflowService (máquina de estados)          │
│  NegotiationService · SignatureService (FES) · SealingService            │
│  TsaService (RFC 3161) · DocumentGuidanceService                          │
│  SpanishTaxIdValidator · EuVatValidator · TransactionResolver             │
│  ClauseBuilder · ContractLegalValidator · ContractPdfService              │
├───────────────────────────────────────────────────────────────────────────┤
│ Modelos: Contract, Party, ContractVersion, ClauseProposal, Signature,    │
│          AuditEvent, ContractDocument, DocumentRequirement, User          │
├───────────────────────────────────────────────────────────────────────────┤
│ MySQL (contratos) · storage/app/private (PDF, firmas, adjuntos, evidencia)│
└───────────────────────────────────────────────────────────────────────────┘
```

## 2. Máquina de estados del contrato

```
borrador ──enviar a revisión──▶ en_revision ──aceptar versión final──▶ lista_para_firma
   │   └─────────congelar directo───────────────────────────────▶ lista_para_firma
   └── cancelado ─────────────────────────── (cualquier estado no firmado)
lista_para_firma ──enviar a firmar──▶ en_firma ──ambas firmas──▶ firmado (sellado)
```

Transiciones validadas en `ContractWorkflowService::TRANSITIONS`. Toda
transición escribe un evento en `audit_trail`.

### Inmutabilidad

- Al pasar a `lista_para_firma`, `freezeVersion()` congela el articulado en una
  `contract_versions` con `hash` del contenido canónico (JSON ordenado).
- En fases posteriores **no se puede editar**: la negociación lanza
  `DomainException`. El cambio exige cancelar y crear versión nueva.
- El hash del contenido se usa como evidencia de *qué* se acordó; el hash del
  PDF final como evidencia de *cuál* es el documento firmado.

## 3. Firma y sellado (integridad)

### Firma Electrónica Simple (eIDAS art. 25)

- La contraparte firma en `/sign/{token}` sin cuenta: rol, nombre, email,
  **verificación FEA opcional (código OTP de 6 dígitos por correo, un solo uso,
  10 min de validez)**, firma dibujada (canvas → PNG) o firma por clic, y
  consentimiento explícito.
- Se registra en `signatures`: rol, firmante, tipo, fecha UTC, IP, User-Agent,
  `otp_verified` y consentimiento.
- Cada firma genera un registro en `consents` (tipo, versión de política, IP,
  agente y timestamp) para el expediente RGPD.

### Concurrencia y consistencia

- **Una firma por rol**: índice único de base de datos `(contract_id,
  party_role)` + captura de la excepción de unicidad → ante 15 peticiones
  paralelas del mismo rol solo una gana (verificado con `tests/concurrency.sh`).
- **Sellado idempotente**: `completeContract()` ejecuta la transición a
  `firmado` y el sellado dentro de una transacción con `lockForUpdate()` sobre
  la fila del contrato, de modo que la segunda firma concurrente no sella dos
  veces.

### Sellado (`SealingService`)

1. Al firmar ambas partes → estado `firmado`.
2. Se genera el **PDF final** (documento + firmas incrustadas + Hoja de
   evidencias).
3. `final_hash` = SHA-256 de los bytes almacenados del PDF.
4. `evidence-payload.txt` registra referencia, versión, hash de contenido, hash
   del PDF, hora UTC y firmantes.
5. **Sello TSA (RFC 3161)**: `TsaService` pide un token a la autoridad
   configurada (`TSA_URL`, por defecto FreeTSA). Si la autoridad no responde,
   **degradación silenciosa**: se documenta en la hoja que el sello es del
   servidor. El sellado nunca se bloquea por una dependencia externa.
6. **Notificación a ambas partes**: al sellar, `SignatureService` envía el
   `ContractSignedMail` al creador **y al email de la contraparte** (el que esta
   escribió al firmar, verificado por OTP). Así **ambas partes conservan el
   contrato sellado**, clave ante futuros pleitos.
7. La verificación (`ContractService::verifyIntegrity`) recalcula el hash del
   archivo y lo compara con `final_hash` (`hash_equals`). Detecta cualquier
   manipulación.

> Sobre "blockchain": la combinación **hash SHA-256 + sello de tiempo + hoja
> de evidencias** es el estándar legalmente relevante (lo usan DocuSign,
> Yousign, etc.). Anclar el hash a una cadena pública es opcional y no aporta
> validez jurídica adicional; puede añadirse como plugin sin tocar el núcleo.

## 4. Validación legal (ES/UE + América Latina)

El motor legal es **multijurisdiccional**. La jurisdicción aplicable
(`contracts.applicable_law`) se resuelve automáticamente a partir del país de
las partes (común, o del vendedor; por defecto España).

- **`CountryLegalConfig`**: configuración por país (España + Argentina, México,
  Colombia, Chile, Perú, Uruguay): nombre del impuesto (IVA/IGV), ley de
  consumo, código civil, ley de protección de datos, impuesto de transmisión y
  fuero de jurisdicción.
- **Validadores**: `SpanishTaxIdValidator` (NIF/CIF/NIE), `EuVatValidator`
  (formato + VIES con degradación offline) y `LatinAmericanTaxIdValidator`
  (CUIT/CUIL con dígito verificador, RUT chileno con dígito verificador, RFC
  mexicano, NIT/cédula, RUC/DNI, RUT uruguayo).
- **`ClauseBuilder`**: adapta el articulado (impuestos, garantías, consumo,
  protección de datos, jurisdicción) al país y añade la cláusula
  **"Derechos y obligaciones de las partes"**.
- **`PartyRightsObligations`**: genera en lenguaje claro los derechos y
  obligaciones de cada parte según su rol y el régimen (B2B/B2C/C2C/C2B),
  visibles en la revisión, en la firma y en el PDF final.
- **Guía de documentos**: `DocumentRequirement` ahora es **por país**
  (`country`): la lista específica de España gana; las filas genéricas son el
  respaldo para LATAM. El seeder incluye flujos completos de vehículo,
  inmueble, bienes muebles y servicios para la región.

## 5. Guía de documentos

`DocumentRequirement` (catálogo) × `ContractDocument` (adjuntos). El seeder
`DocumentRequirementSeeder` define la checklist por `(contract_type,
transaction_type, jurisdiction)` con explicaciones en lenguaje llano (vehículo,
inmueble, muebles, servicios, internacional). `DocumentGuidanceService` devuelve
la checklist ordenada con el estado de carga y el progreso.

## 6. Escalabilidad

### Concurrencia

La firma pública soporta picos de tráfico sin duplicar firmas ni sellados
gracias al **índice único `(contract_id, party_role)`** y al **sellado bajo
`lockForUpdate`**. `tests/concurrency.sh` lo valida lanzando 15 peticiones
paralelas por rol contra el servidor (verificado en Sail).

> Nota de despliegue: en desarrollo Sail usa el servidor integrado de PHP
> (limitado a pocos procesos concurrentes). En producción usa **Nginx +
> PHP-FPM** (o Laravel Octane) con `pm.max_children` dimensionado al tráfico,
> y `queue:work` activo para procesar los correos.

### Cómo crecer sin reescribir

1. **Colas ya listas**: emails y tareas pesadas vía `QUEUE_CONNECTION=database`
   + worker supervisor. Mover la generación de PDF/sellado a un job si el
   volumen sube.
2. **Firma asíncrona**: el sellado actual es síncrono (rápido, ~1 s). Para
   volúmenes altos, envolver `SealingService::seal` en un job de cola.
3. **Cache y sesiones**: cambiar `CACHE_STORE`/`SESSION_DRIVER` a `redis` y
   `REDIS_CLIENT` a `phpredis` con un contenedor Redis (p. ej. Docker).
4. **Almacenamiento escalable**: el código usa `Storage::disk('local')`.
   Configurar `FILESYSTEM_DISK=s3` (o compatible S3 en UE, p. ej. Scaleway,
   Exoscale) para PDFs y adjuntos sin tocar el código. Backup garantizado de
   `storage/app/private` (evidencias legales).
5. **Base de datos**: particionar `audit_trail` por fecha, indexar
   `contracts.user_id`, `signatures.contract_id`, `audit_trail.contract_id`.
   Conectar MySQL replicado (lecturas) si crece.
6. **Horizonte de proceso**: PDF pesado → mover a `browsershot`/Puppeteer
   opcional (mejor tipografía) sin cambiar la API del servicio.
7. **Varias instancias**: la app es *stateless* (sesiones en BD/cache); un
   balanceador Nginx + varias instancias PHP-FPM escala horizontalmente.

### Límites actuales y roadmap

| Fase | Alcance |
|---|---|
| Fase 1 (implementada) | Cuentas, workflow, negociación, firma FES, sellado + evidencias, checklist de documentos, guía vehículo, PDF, tests |
| Fase 2 (implementada) | **FEA (OTP email)**, **emails en cola**, **consentimiento RGPD explícito** (`consents`), **backup automático**, **panel admin**, index único anti-duplicados + sellado con bloqueo de fila |
| Fase 3 (implementada) | **Motor legal multijurisdiccional (ES + AR/MX/CO/CL/PE/UY)**, **i18n es/en**, **guía de trámites por país**, **nuevos tipos de contrato** (alquiler, préstamo, cesión de derechos, NDA) |
| Fase 4 (implementada) | **Branding Tratix** (Slate/Esmeralda + Plus Jakarta Sans), **landing pública**, **3 planes** (Free/Pro/Business con **marca blanca**), **referidos** (descuento + créditos), **exportación ZIP**, **verificación pública de integridad**, **recordatorios automáticos**, **enlaces con caducidad**, **plantillas rápidas**, **búsqueda/filtros**, **resumen mensual** |
| Pendiente | eIDAS 2.0 / EU Digital Identity Wallet, OCR de adjuntos, multi-idioma completo de todas las vistas, integración notarial/inmobiliaria, anclaje de hash opcional en cadena pública, **app hermana de consentimientos** (separada) |

## 7. Monetización

- **Planes** en `config/billing.php` (free: 2 contratos/mes · pro: ilimitados ·
  business: + **marca blanca** en el PDF).
- **`CreditService`** cuenta los contratos del mes y bloquea la creación al
  agotar el cupo, redirigiendo a `/pricing`.
- **`BillingGateway`** (interfaz): `DemoGateway` (desarrollo/tests, activa el
  plan al instante) y `StripeGateway` (subscriptiones Checkout + webhook con
  verificación de firma). Se selecciona con `BILLING_GATEWAY=demo|stripe`.
- **Referidos**: `ReferralService` + tabla `referrals`; el invitado y el
  referidor ganan créditos y 1 mes de Pro.
- **Exportación**: `ContractExportService` empaqueta todos los PDF + evidencias
  del usuario en ZIP (Pro/Business).
- **Panel admin** (`/admin`, rol `is_admin`): estadísticas de usuarios,
  contratos por estado, plan Pro/Business y contratos recientes.

## 7bis. Compartir el contrato

- **`ContractSharing`** centraliza el enlace a compartir según el estado
  (revisión / firma / descarga del PDF sellado) y genera los enlaces `mailto:`
  y `wa.me` (WhatsApp) con el mensaje y el destinatario precalculados.
- **Contraparte**: el campo `contracts.creator_role` indica qué parte es el
  dueño de la cuenta; la **contraparte** es la otra parte. Se usa para prellenar
  su email (→ `mailto:`) y su teléfono (→ `wa.me/<nº>` directo) si están
  guardados; si no hay teléfono, se usa `wa.me/?text=` genérico.
- **UI**: partial `_share_modal.blade.php` (Alpine) con Email, WhatsApp y
  "Copiar enlace", integrado en la vista del contrato y en el panel "Siguiente
  paso". El enlace solo aparece cuando existe un token (`en_revision`,
  `en_firma`, `firmado`).

## 8. Seguridad

- Autenticación Breeze con verificación de email (`verified`).
- `ContractPolicy` + `authorize()` en todos los controllers (ownership).
- Enlaces públicos por **token UUID** y validación de estado (403 si el
  contrato no está en la fase correspondiente).
- CSRF en todos los formularios, validación de subida (tipos y tamaño máx.
  10 MB).
- Hash con `hash_equals` (constant-time).
- Parámetros de entorno para TSA; sin secretos en el código.
