# Tratix · Contratos de compraventa (ES/UE + América Latina)

Aplicación Laravel para **generar, negociar, firmar y sellar contratos** con
validación **española y europea** y de **América Latina**, orientada a
operaciones entre **particulares** (C2C), **profesionales** (B2B), ventas a
consumidores (B2C) y operaciones transfronterizas. Incluye además contratos de
**alquiler, préstamo, cesión de derechos y acuerdos de confidencialidad (NDA)**.

## Branding
- **Nombre**: Tratix · **Paleta**: Slate/Esmeralda (fondo `#0F172A`, superficies
  `#1E293B`, acento `#10B981`) · **Tipografía**: Plus Jakarta Sans.
- **Landing page** pública en `/`, logo SVG, favicon y metadatos Open Graph.

Cada contrato incluye:

- **Articulado legal generado** según el régimen (IVA/IGV, inversión del sujeto
  pasivo, ITP, garantía y desistimiento, jurisdicción, protección de datos).
- **Validación de identidades fiscales**: NIF/CIF/NIE, IVA europeo (VIES) y
  documentos fiscales de **América Latina** (CUIT/CUIL, RUT, RFC, NIT, RUC).
- **Derechos y obligaciones de cada parte** en lenguaje claro, adaptados al
  país del contrato (España, Argentina, México, Colombia, Chile, Perú, Uruguay).
- **Negociación cláusula a cláusula** (track-changes) antes del cierre.
- **Firma electrónica simple** (eIDAS art. 25) con **verificación por correo
  (OTP)**, lienzo de firma y consentimiento registrado.
- **Compartir con un clic**: enlace de revisión/firma/PDF por **email y
  WhatsApp** (o copiando el enlace), dirigido automáticamente a la contraparte.
- **Hoja de evidencias sellada**: hash SHA-256, fecha UTC, IP/agente de los
  firmantes y sello de tiempo RFC 3161 (TSA). Verificación de integridad.
  Al sellar, **ambas partes reciben el PDF firmado por correo** (clave ante
  futuros pleitos).
- **Verificación pública de integridad** en `/verify/{referencia}`.
- **Guía de trámites y documentos por país y tipo de contrato**.
- **Recordatorios automáticos** de firma/revisión pendiente.

## Planes y monetización
- **Gratis**: 2 contratos/mes, PDF con marca Tratix, 1 plantilla.
- **Pro** (9€/mes): contratos ilimitados, todas las plantillas, exportación.
- **Business** (19€/mes): + **marca blanca** en el PDF, soporte prioritario.
- **Referidos**: tú y el invitado ganáis 1 mes de Pro y créditos.
- **Exportación** de todos los contratos y evidencias en ZIP (Pro/Business).

## Documentación

| Guía | Contenido |
|---|---|
| [docs/INSTALL.md](docs/INSTALL.md) | Instalación con **Laravel Sail**, despliegue en VPS, colas, backups, seguridad |
| [docs/USAGE.md](docs/USAGE.md) | Manual de uso paso a paso (crear, negociar, firmar con OTP, verificar) |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Arquitectura, motor legal multijurisdiccional, integridad/sellado, escalabilidad, roadmap |

## Inicio rápido (Laravel Sail)

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate

./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
npm run build
```

Entra en **http://localhost:8090** (usuario: `test@example.com` / `password`).
El correo de desarrollo llega a **http://localhost:8026** (Mailpit).

Sin Docker:

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# apunta DB_HOST/DB_PORT a tu MySQL local y crea la base
php artisan migrate --seed
npm run build && php artisan serve
```

## Pruebas

```bash
php artisan test            # 130 tests
vendor/bin/pint             # estilo de código
./tests/concurrency.sh      # concurrencia: 15 firmas paralelas por rol
./tests/e2e.sh              # flujo completo E2E (marca, planes, LATAM, OTP, verificación)
```

## Marco legal aplicado

**España / UE**

- **eIDAS** (Reglamento UE 910/2014): firma electrónica simple y no denegación
  de efectos jurídicos.
- **ESIGN Act / UETA**: requisitos internacionales de consentimiento y traza.
- **RGPD**: tratamiento de datos con base en la ejecución del contrato
  (art. 6.1.b).
- **Ley 37/1992 (IVA)**, **RDL 1/1993 (ITP)**, **RDL 1/2007 (LGDCU)**,
  **Código Civil y de Comercio**, **Reglamento 1215/2012 (Bruselas I bis)**.

**América Latina** (config por país en `CountryLegalConfig`)

- Argentina: Ley 24.240 (consumidor), CCyC, Ley 25.326 (datos).
- México: Ley Federal de Protección al Consumidor, Código Civil Federal, LFPDPPP.
- Colombia: Estatuto del Consumidor (Ley 1480/2011), Ley 1581/2012 (habeas data).
- Chile: Ley 19.496 (consumidor), Ley 19.628 (datos).
- Perú: Ley 29571 (consumidor), Ley 29733 (datos).
- Uruguay: Ley 17.250 (consumo), Ley 18.331 (datos).

## Aviso legal

Esta herramienta ofrece plantillas orientativas y controles de coherencia.
**No sustituye el asesoramiento de un profesional** (abogado, notario o asesor
fiscal). En operaciones complejas (inmuebles, sociedades, gran importe) se
recomienda revisión profesional antes de la firma.
