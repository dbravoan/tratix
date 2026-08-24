<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de Privacidad y Protección de Datos – {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 font-sans text-slate-200 antialiased min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
        {{-- Header & Back --}}
        <div class="flex items-center justify-between pb-6 border-b border-slate-800">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-emerald-400 hover:text-emerald-300 font-medium transition">
                <span>← Volver al inicio</span>
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="btn-outline text-xs px-3 py-1.5 flex items-center gap-1 text-slate-300 hover:text-white">
                    <span>🖨️ Imprimir / Guardar en PDF</span>
                </button>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6 sm:p-10 mt-8 space-y-8">
            {{-- Title Banner --}}
            <div class="border-b border-slate-800 pb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-500/30 text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-3">
                    <span>🛡️ RGPD (UE) 2016/679 · LOPDGDD 3/2018</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Política de privacidad y protección de datos</h1>
                <p class="text-sm text-slate-400 mt-2">Última actualización: 23 de agosto de 2026 · Versión 2.0 (Conformidad Integral de Máxima Seguridad)</p>
            </div>

            {{-- Table of Summary / Layer 1 Box --}}
            <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-5 space-y-3">
                <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                    <span>📋 Resumen Informativo de Primera Capa (Art. 11 LOPDGDD / Art. 13 RGPD)</span>
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-800">
                        <span class="font-bold text-slate-300 block mb-1">Responsable:</span>
                        <span class="text-slate-400">{{ config('app.name') }} (Canal DPO: {{ 'privacidad@' . request()->getHost() }})</span>
                    </div>
                    <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-800">
                        <span class="font-bold text-slate-300 block mb-1">Finalidades principales:</span>
                        <span class="text-slate-400">Generación y gestión contractual, firma electrónica legal eIDAS y custodia de evidencias.</span>
                    </div>
                    <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-800">
                        <span class="font-bold text-slate-300 block mb-1">Bases jurídicas:</span>
                        <span class="text-slate-400">Ejecución del contrato (art. 6.1.b), obligación legal (art. 6.1.c) e interés legítimo de seguridad (art. 6.1.f).</span>
                    </div>
                    <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-800">
                        <span class="font-bold text-slate-300 block mb-1">Destinatarios:</span>
                        <span class="text-slate-400">La contraparte del contrato y encargados de alojamiento/pasarela. Sin cesión a terceros sin mandato legal.</span>
                    </div>
                    <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-800">
                        <span class="font-bold text-slate-300 block mb-1">Conservación:</span>
                        <span class="text-slate-400">Durante la relación contractual y posterior bloqueo durante los plazos de prescripción legal.</span>
                    </div>
                    <div class="p-3 bg-slate-900/90 rounded-lg border border-slate-800">
                        <span class="font-bold text-slate-300 block mb-1">Tus Derechos:</span>
                        <span class="text-slate-400">Acceso, Rectificación, Supresión, Limitación, Portabilidad, Oposición y reclamación ante la AEPD.</span>
                    </div>
                </div>
            </div>

            {{-- Full Content Sections --}}
            <div class="prose prose-invert prose-slate max-w-none text-sm leading-relaxed space-y-6 text-slate-300">
                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">1.</span> Responsable del Tratamiento y Delegado de Protección de Datos (DPO)
                    </h3>
                    <p>
                        La presente Política de Privacidad regula el tratamiento de datos personales llevado a cabo a través de la plataforma tecnológica <strong>{{ config('app.name') }}</strong> (en adelante, la "Plataforma" o el "Responsable").
                    </p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-300">
                        <li><strong>Denominación / Plataforma:</strong> {{ config('app.name') }}</li>
                        <li><strong>Actividad:</strong> Servicios de software para la redacción, negociación colaborativa, verificación de identidad y formalización de contratos con firma electrónica avanzada.</li>
                        <li><strong>Canal Oficial de Privacidad y DPO:</strong> <a href="mailto:{{ 'privacidad@' . request()->getHost() }}" class="text-emerald-400 underline font-mono">{{ 'privacidad@' . request()->getHost() }}</a></li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">2.</span> Principios Rectores del Tratamiento (Art. 5 RGPD)
                    </h3>
                    <p>
                        En {{ config('app.name') }} aplicamos de forma estricta los principios fundamentales de la normativa europea y española:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                        <li><strong>Licitud, lealtad y transparencia:</strong> Tratamos los datos sobre bases legales legítimas y facilitamos información clara en todo momento.</li>
                        <li><strong>Limitación de la finalidad:</strong> Los datos se recogen con fines contractuales y probatorios explícitos y no se tratarán de manera incompatible con ellos.</li>
                        <li><strong>Minimización de datos:</strong> Únicamente solicitamos los datos estrictamente necesarios para la validez jurídica del contrato y la prevención del fraude.</li>
                        <li><strong>Exactitud y actualización:</strong> Permitimos a los usuarios y a sus contrapartes rectificar sus datos antes del cierre definitivo de la firma.</li>
                        <li><strong>Limitación del plazo de conservación:</strong> Los datos se conservan exclusivamente durante el tiempo necesario y se someten a un estricto régimen de bloqueo legal tras la firma.</li>
                        <li><strong>Integridad y confidencialidad:</strong> Aplicamos cifrado de almacenamiento, sellado criptográfico SHA-256 e intransferibilidad de accesos.</li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">3.</span> Categorías de Datos Personales Tratados
                    </h3>
                    <p>
                        En el desarrollo de los servicios contractuales y de firma, tratamos las siguientes categorías de datos:
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border border-slate-800 rounded-lg overflow-hidden">
                            <thead class="bg-slate-950 text-slate-300 text-left">
                                <tr>
                                    <th class="p-2.5 border-b border-slate-800">Categoría</th>
                                    <th class="p-2.5 border-b border-slate-800">Datos Concretos</th>
                                    <th class="p-2.5 border-b border-slate-800">Origen / Procedencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 bg-slate-900/50">
                                <tr>
                                    <td class="p-2.5 font-semibold text-slate-200">Datos identificativos</td>
                                    <td class="p-2.5 text-slate-300">Nombre, apellidos, NIF/NIE/CIF/Pasaporte, país de residencia y nacionalidad.</td>
                                    <td class="p-2.5 text-slate-400">Facilitados por el usuario o la contraparte contratante.</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-semibold text-slate-200">Documentos de Identidad (DNI/NIE)</td>
                                    <td class="p-2.5 text-slate-300">Fotocopia/escáner de anverso y reverso para extracción OCR de datos y cotejo de identidad.</td>
                                    <td class="p-2.5 text-slate-400">Subidos voluntariamente para adjuntar al expediente contractual.</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-semibold text-slate-200">Datos de contacto y ubicación</td>
                                    <td class="p-2.5 text-slate-300">Dirección postal completa, código postal, municipio, provincia, correo electrónico y teléfono/WhatsApp.</td>
                                    <td class="p-2.5 text-slate-400">Facilitados en el formulario de creación o revisión.</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-semibold text-slate-200">Datos contractuales y patrimoniales</td>
                                    <td class="p-2.5 text-slate-300">Bienes u objetos del contrato, matrículas de vehículos, referencias catastrales, importes económicos y plazos.</td>
                                    <td class="p-2.5 text-slate-400">Incorporados a las cláusulas del contrato.</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-semibold text-slate-200">Evidencias electrónicas y firma</td>
                                    <td class="p-2.5 text-slate-300">Trazo de firma manuscrita digitalizada, códigos OTP de doble factor, dirección IP pública, User-Agent, marca de tiempo y hash criptográfico SHA-256.</td>
                                    <td class="p-2.5 text-slate-400">Generados automáticamente durante el acto de firma electrónica con validez eIDAS.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">4.</span> Finalidades y Bases de Legitimación del Tratamiento
                    </h3>
                    <p>
                        Cada tratamiento de datos responde a una base jurídica expresamente prevista en el artículo 6.1 del RGPD:
                    </p>
                    <ul class="list-disc pl-5 space-y-2 text-slate-300">
                        <li>
                            <strong>Ejecución del contrato de servicios (Art. 6.1.b RGPD):</strong> Alta de cuenta de usuario, redacción de borradores, generación de enlaces colaborativos de revisión y expedición de contratos vinculantes.
                        </li>
                        <li>
                            <strong>Formalización de la firma electrónica y generación de evidencias (Art. 6.1.b y 6.1.c RGPD):</strong> Registro de trazas de firma, envío de código OTP y custodia del certificado de evidencias conforme al Reglamento UE 910/2014 (eIDAS) y la Ley 6/2020 de servicios electrónicos de confianza.
                        </li>
                        <li>
                            <strong>Cumplimiento de obligaciones legales (Art. 6.1.c RGPD):</strong> Conservación de facturas y registros tributarios (Ley 58/2003 General Tributaria), prevención del fraude y atención a requerimientos de autoridades judiciales o administrativas.
                        </li>
                        <li>
                            <strong>Interés legítimo de seguridad (Art. 6.1.f RGPD):</strong> Detección y bloqueo de accesos no autorizados, verificación de integridad de documentos contra manipulaciones y protección de la infraestructura.
                        </li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">5.</span> Tratamiento Específico de Copias de Documentos de Identidad (DNI / NIE / Pasaporte)
                    </h3>
                    <div class="bg-amber-950/30 border border-amber-500/30 rounded-lg p-4 text-xs text-amber-200 space-y-2">
                        <p class="font-bold">⚠️ Compromiso reforzado de minimización y seguridad en documentos oficiales de identidad:</p>
                        <p>
                            Las copias de documentos de identidad subidas para el escaneo OCR o incorporadas como anexo a los contratos se tratan bajo las directrices de la Agencia Española de Protección de Datos (AEPD):
                        </p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Se almacenan en discos de almacenamiento privado aislados de la web pública (sin URLs estáticas accesibles).</li>
                            <li>Solo son accesibles por las partes legítimas del contrato debidamente autenticadas mediante sesión o token criptográfico activo no revocado.</li>
                            <li>Los archivos temporales de escaneo no asociados a ningún contrato formalizado son destruidos de manera automatizada y segura.</li>
                        </ul>
                    </div>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">6.</span> Destinatarios, Encargados de Tratamiento y Transferencias Internacionales
                    </h3>
                    <p>
                        Tus datos personales no serán vendidos ni transferidos a terceros. Únicamente acceden a ellos:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                        <li><strong>La contraparte contratante:</strong> Para posibilitar la revisión, negociación y perfeccionamiento del contrato pactado.</li>
                        <li><strong>Proveedores de infraestructura (Encargados de Tratamiento):</strong> Proveedores de alojamiento en servidores seguros en la Unión Europea, proveedores de mensajería transaccional para el envío de códigos OTP y notificaciones de firma, y pasarela de pago Stripe (conforme a su Data Processing Agreement y Cláusulas Contractuales Tipo).</li>
                        <li><strong>Organismos públicos y Juzgados:</strong> Cuando exista una obligación legal preceptiva o requerimiento judicial expreso.</li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">7.</span> Plazos de Conservación y Política de Bloqueo de Datos (Art. 32 LOPDGDD)
                    </h3>
                    <p>
                        Los datos se conservan mientras se mantenga activa la cuenta del usuario o la vigencia del contrato. Una vez solicitada la baja o finalizado el contrato:
                    </p>
                    <div class="bg-slate-950/70 p-3.5 rounded-lg border border-slate-800 text-xs space-y-1.5">
                        <p class="font-semibold text-slate-200">🔒 Bloqueo legal de datos:</p>
                        <p>
                            De conformidad con el artículo 32 de la Ley Orgánica 3/2018 (LOPDGDD), los datos relativos a contratos formalizados y sus evidencias de firma no se destruyen inmediatamente, sino que <strong>se bloquean</strong> (impidiendo su tratamiento para fines ordinarios) para conservarse a disposición exclusiva de jueces, tribunales, Ministerio Fiscal y Administraciones Públicas para la exigencia de posibles responsabilidades durante los plazos de prescripción:
                        </p>
                        <ul class="list-disc pl-4 space-y-0.5 text-slate-400">
                            <li><strong>4 años:</strong> Obligaciones fiscales y tributarias (Ley General Tributaria).</li>
                            <li><strong>5 años:</strong> Acciones personales derivadas de obligaciones contractuales (Art. 1964 Código Civil).</li>
                            <li><strong>6 años:</strong> Libros y documentación comercial (Art. 30 Código de Comercio).</li>
                        </ul>
                    </div>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">8.</span> Ejercicio de Derechos ARCO-POL de los Interesados
                    </h3>
                    <p>
                        En cualquier momento puedes ejercer de forma gratuita tus derechos reconocidos por los artículos 15 a 22 del RGPD:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-lg">
                            <span class="font-bold text-emerald-400 block mb-1">🔍 Derecho de Acceso (Art. 15):</span>
                            <span>Conocer qué datos personales tratamos sobre ti y obtener copia de los mismos.</span>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-lg">
                            <span class="font-bold text-emerald-400 block mb-1">✏️ Derecho de Rectificación (Art. 16):</span>
                            <span>Solicitar la modificación o corrección de datos inexactos o incompletos.</span>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-lg">
                            <span class="font-bold text-emerald-400 block mb-1">🗑️ Derecho de Supresión (Art. 17):</span>
                            <span>Solicitar el borrado de tus datos cuando ya no sean necesarios (sujeto al bloqueo legal).</span>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-lg">
                            <span class="font-bold text-emerald-400 block mb-1">📦 Derecho a la Portabilidad (Art. 20):</span>
                            <span>Descargar todos tus datos personales en formato electrónico estructurado e interoperable (JSON).</span>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-lg">
                            <span class="font-bold text-emerald-400 block mb-1">⏸️ Derecho a la Limitación (Art. 18):</span>
                            <span>Solicitar la suspensión cautelar del tratamiento mientras se resuelven reclamaciones o rectificaciones.</span>
                        </div>
                        <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-lg">
                            <span class="font-bold text-emerald-400 block mb-1">✋ Derecho de Oposición (Art. 21):</span>
                            <span>Oponerte al tratamiento de tus datos para finalidades específicas basadas en interés legítimo.</span>
                        </div>
                    </div>
                    <div class="bg-emerald-950/30 border border-emerald-500/30 p-4 rounded-lg text-xs space-y-2">
                        <p class="font-bold text-emerald-300">🚀 Cómo ejercer tus derechos:</p>
                        <p>
                            1. <strong>Autoservicio instantáneo:</strong> Si dispones de cuenta, puedes descargar todos tus datos en JSON desde <a href="{{ route('profile.edit') }}#gdpr-section" class="text-emerald-400 underline font-semibold">tu Panel de Perfil</a>.<br>
                            2. <strong>Por correo electrónico:</strong> Enviando tu solicitud indicando el derecho que deseas ejercer a <a href="mailto:{{ 'privacidad@' . request()->getHost() }}" class="text-emerald-400 underline font-mono">{{ 'privacidad@' . request()->getHost() }}</a> adjuntando copia o acreditación fehaciente de tu identidad.
                        </p>
                        <p class="text-slate-400 text-[11px]">
                            Responderemos a tu solicitud en el plazo máximo de <strong>1 mes</strong> legalmente establecido (prorrogable a 2 meses en casos de alta complejidad).
                        </p>
                    </div>
                </section>

                <section class="space-y-3">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center gap-2">
                        <span class="text-emerald-400">9.</span> Derecho a Reclamar ante la Autoridad de Control
                    </h3>
                    <p>
                        Si consideras que el tratamiento de tus datos personales vulnera la normativa aplicable o no has obtenido satisfacción en el ejercicio de tus derechos, tienes el derecho legal inalienable de presentar una reclamación ante la autoridad de control:
                    </p>
                    <div class="bg-slate-950 p-3.5 rounded-lg border border-slate-800 text-xs">
                        <span class="font-bold text-slate-100 block">Agencia Española de Protección de Datos (AEPD)</span>
                        <span class="text-slate-400 block">C/ Jorge Juan, 6 · 28001 Madrid (España)</span>
                        <span class="text-slate-400 block">Sede electrónica: <a href="https://www.aepd.es" target="_blank" rel="noopener" class="text-emerald-400 underline">www.aepd.es</a> · Teléfono: 901 100 099 / 912 663 517</span>
                    </div>
                </section>
                <section id="cookies" class="space-y-4">
                    <h3 class="text-base font-bold text-slate-100 border-b border-slate-800 pb-1.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-400">10.</span> Política de Cookies y Tecnologías de Terceros
                        </div>
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('tratix:open-cookie-preferences'))" class="btn-outline text-xs px-3 py-1 text-emerald-400 border-emerald-500/50">
                            ⚙️ Configurar Preferencias
                        </button>
                    </h3>
                    <p>
                        En {{ config('app.name') }} utilizamos cookies y tecnologías de almacenamiento local para garantizar la seguridad en la firma electrónica, analizar el tráfico y la interacción de los usuarios y gestionar espacios publicitarios que financian el servicio gratuito.
                    </p>

                    <div class="space-y-3 text-xs">
                        <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <strong class="text-white text-sm">10.1 Cookies Técnicas y de Seguridad (Obligatorias)</strong>
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] font-bold">Siempre Activas</span>
                            </div>
                            <p class="text-slate-400">
                                Permiten la autenticación del usuario, la protección contra ataques CSRF (Cross-Site Request Forgery), la persistencia de borradores de contratos en curso y la verificación OTP de firma electrónica bajo estándar eIDAS.
                            </p>
                        </div>

                        <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <strong class="text-white text-sm">10.2 Cookies Analíticas (Google Analytics 4)</strong>
                                <span class="px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800 text-[10px] font-bold">Requiere Consentimiento</span>
                            </div>
                            <p class="text-slate-400">
                                Proveedor: <strong>Google Ireland Limited</strong>. Finalidad: Medición de audiencia anónima, rendimiento de páginas y detección de errores de carga. La dirección IP se anonimiza antes de su almacenamiento.
                            </p>
                        </div>

                        <div class="p-3.5 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <strong class="text-white text-sm">10.3 Cookies de Publicidad y Monetización (Google AdSense)</strong>
                                <span class="px-2 py-0.5 rounded bg-amber-950 text-amber-400 border border-amber-800 text-[10px] font-bold">Requiere Consentimiento</span>
                            </div>
                            <p class="text-slate-400">
                                Proveedor: <strong>Google Ireland Limited</strong>. Finalidad: Gestión de espacios publicitarios patrocinados en las áreas públicas de la plataforma. Si el usuario no otorga su consentimiento, no se instalarán identificadores publicitarios ni se personalizarán anuncios.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Footer info --}}
            <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <span>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</span>
                <a href="{{ url('/') }}" class="text-emerald-400 hover:text-emerald-300 font-medium underline">Volver a la aplicación</a>
            </div>
        </div>
    </div>

    {{-- Universal Cookie Consent Component --}}
    <x-cookie-consent />
</body>
</html>
