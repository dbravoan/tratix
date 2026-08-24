# Guía de uso

La aplicación genera contratos de compraventa **con validez legal** (firma
electrónica simple eIDAS + hoja de evidencias con hash SHA-256) y **guía los
trámites y documentos** de cada operación paso a paso.

## Ciclo completo de un contrato

```
Crear ──▶ Borrador ──▶ Revisión ──▶ Acordado ──▶ Firma ──▶ Firmado y sellado
           (opcional, negociación)
```

---

## 1. Registro y acceso

- Regístrate en `/register` (o entra en `/login`).
- Cada usuario dispone de su panel **"Mis contratos"** con el estado de cada
  operación.

## 2. Crear un contrato (4 pasos)

Desde **+ Nuevo contrato**:

1. **Datos**: tipo de contrato (bienes muebles, inmuebles, vehículo, servicios,
   internacional), objeto, cantidad, ciudad y fecha de firma.
2. **Partes**: vendedor y comprador. Indica si cada uno es particular, autónomo
   o sociedad, su NIF/CIF/NIE (o número de IVA europeo) y país. El sistema
   **detecta el régimen automáticamente**: B2B, B2C, C2C o C2B y el ámbito
   (nacional, intracomunitario, internacional).
   - Botón **"Verificar"** junto al NIF/IVA para validarlo al instante.
3. **Condiciones**: precio, impuestos, condiciones de pago y entrega.
4. **Revisar**: pulsa crear y verás el documento completo con todo el
   articulado legal generado según el régimen (IVA, ITP, garantías, jurisdicción,
   protección de datos, etc.).

> El texto legal se genera solo. El régimen fiscal y las obligaciones se
> explican en la sección **"Notas fiscales"**.

## 3. Revisión y negociación (opcional)

- Pulsa **"Enviar a revisión"** con el email de la otra parte. El contrato pasa
  a estado *En revisión* y se genera un **enlace público de revisión**.
- La contraparte abre el enlace, elige su rol y puede:
  - **Aceptar el borrador**, o
  - **Proponer un cambio** en una cláusula concreta (con texto alternativo y
    motivo), a modo de *track-changes*.
- Tú ves la propuesta con "Antes / Propuesta" y puedes **Aprobar** (se aplica al
  documento) o **Rechazar**.
- Cuando ambas partes estén conformes, pulsa **"Aceptar versión final y
  congelar"**. A partir de ahí **el documento queda bloqueado**: ya no admite
  edición. Cualquier cambio futuro exige cancelar y crear una versión nueva.

> Si no necesitáis negociar, puedes **congelar directamente** desde el borrador
> y saltar a la firma.

## 4. Trámites y documentos (guía por caso)

La pestaña **"Trámites y documentos"** muestra una checklist ordenada específica
para tu tipo de contrato. Cada documento incluye:

- **¿Por qué se necesita?** (lenguaje claro)
- **Cómo conseguirlo** (pasos)
- **Referencia legal** y enlace al trámite oficial cuando existe.

### Ejemplo: compraventa de vehículo entre particulares

1. Contrato privado firmado (se genera aquí).
2. DNI/NIE de ambas partes.
3. Informe de tráfico (titularidad y cargas) — gratis en la sede DGT.
4. Ficha técnica / permiso de circulación.
5. ITV en vigor.
6. Justificante del pago.
7. Autoliquidación del **ITP** (30 días hábiles, ~4 % según CCAA).
8. Tasa de la DGT por el **cambio de titularidad** (trámite TIV).
9. Justificante del **cambio de titularidad** (telemático en la sede DGT con
   certificado, o presencial en Jefatura con cita).
10. Informe de tráfico final (verificación del nuevo titular).

Sube cada documento (PDF, PNG, JPG) y márcalo como revisado. La barra de
progreso indica lo que falta.

## 5. Firma (Firma Electrónica Simple)

1. Pulsa **"Enviar a firmar"** con el email de la contraparte. El contrato pasa
   a *En firma* y se muestra el **enlace público de firma**.
2. La otra parte abre el enlace desde el móvil o el ordenador, elige su rol,
   escribe su nombre y email, y **verifica el correo con un código OTP de 6
   dígitos** (FEA). Si no llega, puede reenviarlo.
3. Antes de firmar, la página muestra los **derechos y obligaciones** de ambas
   partes adaptados a la jurisdicción del contrato.
4. **Dibuja su firma** en el recuadro (o firma con un clic) y marca el
   consentimiento.
5. Cuando **ambas partes** firman, el contrato se **sella automáticamente**:
   se genera el PDF final con las firmas y la **Hoja de evidencias**
   (hash SHA-256, fecha UTC, IP y agente de cada firmante, verificación OTP y
   sello de tiempo RFC 3161 si la autoridad está disponible).

> La verificación OTP puede desactivarse con `SIGNING_OTP_ENABLED=false` si no
> necesitas el nivel FEA.

## 6. Notificaciones por correo

La plataforma envía correos (a Mailpit en desarrollo, a tu SMTP en producción):

- **Invitación de revisión** con el enlace de revisión.
- **Invitación de firma** con el enlace de firma.
- **Código OTP** de verificación.
- **Contrato firmado y sellado** al creador **y a la contraparte**, con el
  enlace de descarga. Así **ambas partes conservan el contrato sellado**, algo
  clave ante posibles pleitos futuros.

## 6bis. Compartir el contrato (Email / WhatsApp)

En cada fase, el botón **"Compartir"** (en la vista del contrato y en el panel
"Siguiente paso") abre un pequeño diálogo con tres acciones:

- **Email**: abre el cliente de correo con el asunto, un mensaje y el enlace
  ya escritos, dirigido al email de la contraparte si lo guardaste.
- **WhatsApp**: abre WhatsApp con el mensaje y el enlace. Si guardaste el
  teléfono de la contraparte (campo "Teléfono (WhatsApp)" al crear), el mensaje
  se envía directamente a ese número; si no, se muestra el selector de
  contactos de WhatsApp.
- **Copiar enlace**: copia el enlace al portapapeles con un "✓ Copiado".

El enlace compartido depende del estado:
- En **revisión** → enlace de revisión (para que proponga cambios).
- En **firma** → enlace de firma.
- **Firmado** → enlace de descarga del PDF sellado.

> **Importante**: para que el WhatsApp y el email se dirijan automáticamente a
> la contraparte, al crear el contrato indica **"tú eres el vendedor o el
> comprador"** y, opcionalmente, guarda el teléfono de la otra parte.

## 7. Descargar y verificar

Una vez sellado, desde la vista del contrato:

- **Descargar PDF firmado** — el documento final con firmas y hoja de evidencias.
- **Hoja de evidencias** — el certificado de integridad.
- **Verificar integridad** — recalcula el hash SHA-256 del PDF y lo compara con
  el sellado. Si el archivo fue alterado, lo detecta y avisa.

## 8. Cancelación

Puedes **cancelar** un contrato en cualquier fase previa a la firma. La
cancelación queda registrada en la traza de auditoría.

## 9. Planes y límites

- **Gratis**: hasta 2 contratos activos al mes, PDF con marca Tratix y 1 plantilla.
- **Pro** (~9€/mes): contratos ilimitados, todas las plantillas, exportación y
  enlaces de firma de 30 días.
- **Business** (~19€/mes): todo lo de Pro + **marca blanca** en el PDF y soporte
  prioritario.

Pagos en producción con **Stripe**; en desarrollo se usa el modo demo
(`BILLING_GATEWAY=demo`) que activa el plan al instante.

Cuando agotas el cupo gratuito, la creación de contratos te redirige a
**/pricing** para actualizar tu plan.

## 10. Referidos

En **"Referir y ganar"** (menú de usuario) tienes tu enlace personal
`/ref/{código}`. Cuando alguien se registra con él, **ambos** ganáis **1 mes de
Pro gratis** y **créditos** para sellos/verificaciones extra.

## 11. Idiomas

El idioma de la interfaz se elige automáticamente según el navegador (`es` o
`en`), o con el parámetro `?lang=en`. El contenido legal generado siempre se
adapta a la **jurisdicción** del contrato (España y América Latina).

## Consejos de validez legal

- La **firma electrónica simple** es plenamente válida según el art. 25 del
  Reglamento (UE) 910/2014 (eIDAS): a una firma no se le pueden denegar efectos
  jurídicos por ser electrónica.
- La **traza de auditoría** (quién, cuándo, desde dónde) y el **hash sellado**
  cumplen los requisitos internacionales (ESIGN Act / UETA).
- En operaciones complejas (inmuebles, sociedades, gran importe) se recomienda
  **revisión por un profesional** y, en su caso, elevación a escritura pública.
