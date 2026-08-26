<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tratix · Contratos de compraventa legales, firmados y sellados en 3 minutos</title>

    <meta name="description" content="Olvídate de descargar plantillas genéricas de internet. Tratix genera contratos adaptados a la legislación vigente, escanea tu DNI por OCR, permite negociar en tiempo real y sella la firma electrónica conforme a eIDAS y RGPD.">
    <meta name="keywords" content="contrato compraventa vehiculo, contrato arras penitenciales, firma electronica eIDAS, contrato compraventa entre particulares, modelo contrato legal espana, escanear dni ocr contrato, contrato alquiler vivienda, contrato prestacion servicios">
    <meta name="author" content="Tratix Legal Tech">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Geo Tags & Multi-region hreflang --}}
    <meta name="geo.region" content="ES">
    <meta name="geo.placename" content="Madrid, España">
    <link rel="alternate" hreflang="es-ES" href="{{ url('/') }}">
    <link rel="alternate" hreflang="es-AR" href="{{ url('/?country=AR') }}">
    <link rel="alternate" hreflang="es-MX" href="{{ url('/?country=MX') }}">
    <link rel="alternate" hreflang="es-CO" href="{{ url('/?country=CO') }}">
    <link rel="alternate" hreflang="es-CL" href="{{ url('/?country=CL') }}">
    <link rel="alternate" hreflang="es-PE" href="{{ url('/?country=PE') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:site_name" content="Tratix">
    <meta property="og:title" content="Tratix · Contratos legales sin papel ni plantillas obsoletas">
    <meta property="og:description" content="Crea, negocia, firma y custodia acuerdos legales con validez jurídica eIDAS, escaneo OCR de DNI y expediente documental.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('favicon.svg') }}">
    <meta property="og:locale" content="es_ES">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Tratix · Contratos legales en minutos">
    <meta name="twitter:description" content="Firma electrónica eIDAS, escáner OCR de identidad y redacción jurídica profesional.">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Structured Data: JSON-LD for SEO & Rich Snippets -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@graph": [
        {
          "@@type": "SoftwareApplication",
          "name": "Tratix",
          "applicationCategory": "BusinessApplication",
          "operatingSystem": "All",
          "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "EUR"
          },
          "description": "Plataforma de redacción legal automatizada, verificación de identidad por OCR y firma electrónica conforme a eIDAS y RGPD.",
          "featureList": [
            "Escáner OCR de DNI y NIE de anverso y reverso",
            "Firma electrónica con validez legal eIDAS",
            "Negociación colaborativa de cláusulas en tiempo real",
            "Delegación de formulario a la otra parte sin registro obligatorio",
            "Expediente de trámites y documentación obligatoria por ley (ITP, DGT)",
            "Sellado criptográfico de integridad SHA-256"
          ]
        },
        {
          "@@type": "Organization",
          "name": "Tratix Legal Tech",
          "url": "{{ url('/') }}",
          "logo": "{{ asset('favicon.svg') }}",
          "contactPoint": {
            "@@type": "ContactPoint",
            "contactType": "Customer Support & DPO",
            "email": "dpo@@tratix.com",
            "availableLanguage": ["Spanish", "English"]
          }
        },
        {
          "@@type": "FAQPage",
          "mainEntity": [
            {
              "@@type": "Question",
              "name": "¿Por qué es mejor Tratix que descargar un PDF gratis de internet?",
              "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Los modelos de PDF descargables de internet suelen estar desactualizados, contienen cláusulas ilegales o nulas según la jurisprudencia actual, obligan a imprimir y rellenar a mano con erratas, y carecen de firma electrónica certificada. Tratix genera cláusulas válidas según el régimen legal (B2B, B2C, C2C), verifica las identidades con OCR y certifica la firma con registro de evidencias y sello de tiempo inmutable."
              }
            },
            {
              "@@type": "Question",
              "name": "¿Tiene la misma validez legal que un contrato firmado en papel?",
              "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Sí. La firma electrónica generada en Tratix cumple estrictamente con el Reglamento Europeo eIDAS (UE) Nº 910/2014, la Ley 6/2020 de servicios electrónicos de confianza y el Código Civil español, proporcionando plena admisibilidad probatoria en juicio mediante hoja de evidencias auditada."
              }
            },
            {
              "@@type": "Question",
              "name": "¿Necesita la otra parte crearse una cuenta para firmar o completar sus datos?",
              "acceptedAnswer": {
                "@@type": "Answer",
                "text": "No. Puedes enviar un enlace privado y seguro a la contraparte por correo electrónico o WhatsApp para que revise el borrador, complete sus datos y firme desde su móvil u ordenador en menos de 1 minuto sin necesidad de registrarse."
              }
            },
            {
              "@@type": "Question",
              "name": "¿Cómo cumple Tratix con el RGPD y la protección de datos?",
              "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Tratix aplica el principio de privacidad desde el diseño (art. 25 del RGPD), cifrado de datos en reposo y en tránsito, servidores en la Unión Europea, purga periódica de escaneos temporales y mecanismos directos para ejercer los derechos de acceso, rectificación, portabilidad y supresión."
              }
            }
          ]
        }
      ]
    }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased selection:bg-emerald-500 selection:text-slate-950" 
    x-data="{
        activeSection: 'inicio',
        scrolled: false,
        mobileMenu: false,
        initScrollSpy() {
            const sections = ['inicio', 'simulador', 'comparativa', 'como-funciona', 'modelos', 'referidos', 'precios', 'sobre-nosotros', 'contacto'];
            window.addEventListener('scroll', () => {
                this.scrolled = window.scrollY > 40;
                const scrollPos = window.scrollY + 200;
                for (const section of sections) {
                    const el = document.getElementById(section);
                    if (el) {
                        const top = el.offsetTop;
                        const height = el.offsetHeight;
                        if (scrollPos >= top && scrollPos < top + height) {
                            this.activeSection = section;
                        }
                    }
                }
            }, { passive: true });
        }
    }" 
    x-init="initScrollSpy()">

    {{-- Top Legal & Trust Announcement Bar --}}
    <div class="bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950 border-b border-emerald-900/60 py-2 px-4 text-center text-xs text-emerald-300 font-medium">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
            <span class="inline-flex items-center gap-1.5 font-bold text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Legalidad 100% garantizada
            </span>
            <span>· Conforme al Reglamento UE eIDAS 910/2014</span>
            <span class="hidden sm:inline">· Ley de Consumidores y Código Civil</span>
            <span class="hidden md:inline">· Sellado criptográfico SHA-256 RFC 3161</span>
        </div>
    </div>

    <!-- CLEAN, STREAMLINED NAVIGATION BAR -->
    <nav class="sticky top-0 z-40 transition-all duration-300"
        :class="scrolled ? 'bg-slate-950/95 backdrop-blur-md border-b border-slate-800 shadow-xl shadow-slate-950/50 py-3' : 'bg-slate-900/80 backdrop-blur-sm border-b border-slate-800/80 py-4'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            
            {{-- Brand Logo --}}
            <a href="#inicio" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-slate-950 font-black text-xl shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition">
                    T
                </div>
                <div>
                    <span class="text-white font-extrabold text-xl tracking-tight block">Tratix</span>
                    <span class="text-[10px] text-emerald-400 uppercase font-bold tracking-widest block">Legal Tech</span>
                </div>
            </a>

            {{-- Simplified Interactive Hub Pills (Only 4 Clean Groups) --}}
            <div class="hidden md:flex items-center gap-1.5 p-1.5 rounded-full bg-slate-900/90 border border-slate-800 shadow-inner text-xs font-semibold text-slate-300">
                <a href="#comparativa" 
                    :class="activeSection === 'comparativa' || activeSection === 'como-funciona' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'hover:text-white hover:bg-slate-800'"
                    class="px-4 py-1.5 rounded-full transition flex items-center gap-1.5">
                    <span>⚡</span>
                    <span>Cómo funciona</span>
                </a>
                
                <a href="#modelos" 
                    :class="activeSection === 'modelos' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'hover:text-white hover:bg-slate-800'"
                    class="px-4 py-1.5 rounded-full transition flex items-center gap-1.5">
                    <span>📋</span>
                    <span>Modelos legales</span>
                </a>

                <a href="#precios" 
                    :class="activeSection === 'precios' || activeSection === 'referidos' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'hover:text-white hover:bg-slate-800'"
                    class="px-4 py-1.5 rounded-full transition flex items-center gap-1.5">
                    <span>💰</span>
                    <span>Planes y referidos</span>
                </a>

                <a href="#contacto" 
                    :class="activeSection === 'contacto' || activeSection === 'sobre-nosotros' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'hover:text-white hover:bg-slate-800'"
                    class="px-4 py-1.5 rounded-full transition flex items-center gap-1.5">
                    <span>🛡️</span>
                    <span>Soporte y DPO</span>
                </a>
            </div>

            {{-- Auth Action CTAs --}}
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary text-xs px-5 py-2.5 font-bold shadow-lg shadow-emerald-950 hover:scale-105 transition">
                        Ir a mi panel →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-300 hover:text-white text-xs font-semibold px-3 py-2 transition">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary text-xs px-5 py-2.5 font-bold shadow-lg shadow-emerald-950 hover:scale-105 transition-transform flex items-center gap-1.5">
                        <span>Empezar gratis</span>
                        <span>→</span>
                    </a>
                @endauth
            </div>

            {{-- Mobile Menu Button --}}
            <div class="flex md:hidden">
                <button type="button" @click="mobileMenu = !mobileMenu" class="p-2.5 rounded-xl text-slate-400 hover:text-white bg-slate-900 border border-slate-800">
                    <span class="sr-only">Abrir menú</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div x-show="mobileMenu" x-transition.opacity class="md:hidden bg-slate-950 border-b border-slate-800 px-4 pt-3 pb-6 space-y-2 mt-2">
            <a href="#comparativa" @click="mobileMenu = false" class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-900 hover:text-emerald-400">⚡ Cómo funciona (Tratix vs. PDF)</a>
            <a href="#modelos" @click="mobileMenu = false" class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-900 hover:text-emerald-400">📋 Catálogo de modelos</a>
            <a href="#precios" @click="mobileMenu = false" class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-900 hover:text-emerald-400">💰 Planes y precios</a>
            <a href="#referidos" @click="mobileMenu = false" class="block px-4 py-2.5 rounded-xl text-sm font-bold text-amber-400 hover:bg-slate-900">🎁 Invita y gana Pro (1 mes gratis mutuo)</a>
            <a href="#contacto" @click="mobileMenu = false" class="block px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-900 hover:text-emerald-400">🛡️ Contacto y DPO</a>
            
            <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary text-center text-xs py-3 font-bold">Ir a mi panel</a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline text-center text-xs py-2.5">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="btn-primary text-center text-xs py-3 font-bold">Crear cuenta gratis</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- HERO SECTION & INTERACTIVE LIVE CONTRACT SIMULATOR -->
    <header id="inicio" class="relative overflow-hidden pt-10 pb-20 lg:pt-16 lg:pb-28">
        {{-- Background Glow --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[600px] bg-gradient-to-b from-emerald-500/15 via-teal-500/5 to-transparent blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative space-y-12">
            
            <div class="text-center max-w-4xl mx-auto space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900/90 border border-emerald-500/40 text-emerald-300 text-xs font-semibold shadow-inner">
                    <span class="text-base">⚖️</span>
                    <span>Di adiós a descargar plantillas genéricas y firmar en papel</span>
                </div>

                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white leading-tight tracking-tight">
                    Contratos legales con validez jurídica.<br>
                    <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">
                        Firmados y sellados en 3 minutos.
                    </span>
                </h1>

                <p class="text-base sm:text-xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
                    Descargar plantillas PDF de internet es arriesgado: cláusulas obsoletas, errores a mano y nulo valor probatorio. <strong class="text-white">Tratix</strong> redacta automáticamente según el régimen legal aplicable (B2B, B2C, C2C), escanea el DNI por OCR, permite negociar en vivo y sella la firma electrónica con plena validez <strong class="text-emerald-400">eIDAS y RGPD</strong>.
                </p>

                {{-- Action CTAs --}}
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto text-sm sm:text-base px-8 py-4 font-extrabold shadow-xl shadow-emerald-500/25 hover:scale-105 transition-all text-center flex items-center justify-center gap-2">
                        <span>🚀 Crear mi primer contrato gratis</span>
                        <span>→</span>
                    </a>
                    <a href="#simulador" class="btn-outline w-full sm:w-auto text-sm sm:text-base px-7 py-4 font-bold text-slate-200 hover:border-emerald-500 hover:text-emerald-300 transition-all text-center flex items-center justify-center gap-2">
                        <span>🎮 Probar el simulador en vivo</span>
                        <span>↓</span>
                    </a>
                </div>

                <p class="text-xs text-slate-400 pt-1 flex items-center justify-center gap-3">
                    <span>✓ Sin tarjeta de crédito</span>
                    <span>·</span>
                    <span>✓ 2 contratos gratis al mes</span>
                    <span>·</span>
                    <span>✓ La otra parte firma gratis sin registrarse</span>
                </p>
            </div>

            {{-- INTERACTIVE LIVE SIMULATOR WIDGET (Rich Interactivity) --}}
            <div id="simulador" class="max-w-5xl mx-auto pt-4" x-data="{
                contractType: 'vehiculos',
                types: {
                    vehiculos: {
                        name: '🚗 Compraventa de vehículo',
                        badge: 'Coches, motos y furgonetas',
                        price: '8.500 €',
                        buyer: 'Carlos Mendoza',
                        seller: 'María Fernández',
                        object: 'Volkswagen Golf 2.0 TDI (Matrícula 1234-KMT)',
                        clause: 'El COMPRADOR declara haber inspeccionado el vehículo a su entera satisfacción, eximiendo al VENDEDOR de vicios ocultos no dolosos y acordando la liquidación del ITP (Modelo 620) en 30 días.',
                        dgt: 'Incluye guía oficial para la transferencia en la DGT.',
                        cta: 'Crear contrato de vehículo'
                    },
                    arras: {
                        name: '🏠 Arras penitenciales',
                        badge: 'Art. 1454 del Código Civil',
                        price: '15.000 € (Señal)',
                        buyer: 'Javier Navarro',
                        seller: 'Inmobiliaria Costa S.L.',
                        object: 'Vivienda sita en C/ Mayor 12, 3ºB (Madrid)',
                        clause: 'Si el COMPRADOR desiste perderá la señal entregada; si el VENDEDOR rescinde el acuerdo devolverá el duplo de la cantidad percibida conforme al art. 1454 del Código Civil.',
                        dgt: 'Incluye verificación registral de cargas y certificado de eficiencia energética.',
                        cta: 'Crear contrato de arras'
                    },
                    servicios: {
                        name: '💼 Servicios profesionales',
                        badge: 'B2B y freelance',
                        price: '3.200 € / mes',
                        buyer: 'Tech Solutions S.A.',
                        seller: 'Lucía Morales (Diseñadora)',
                        object: 'Desarrollo de plataforma web e identidad corporativa',
                        clause: 'La titularidad de los derechos de propiedad intelectual se transferirá de forma plena e irrevocable tras la recepción del pago final correspondiente al hito 3.',
                        dgt: 'Incluye cláusulas de confidencialidad y propiedad intelectual.',
                        cta: 'Crear contrato de servicios'
                    },
                    nda: {
                        name: '🔒 Confidencialidad (NDA)',
                        badge: 'Protección de información',
                        price: 'Penalización: 50.000 €',
                        buyer: 'Inversor / Socio B',
                        seller: 'Startup Innovadora S.L.',
                        object: 'Intercambio de algoritmos, código fuente y datos financieros',
                        clause: 'Toda la información técnica revelada mantendrá el deber de secreto por un periodo de 5 años. El incumplimiento facultará la indemnización automática pactada.',
                        dgt: 'Admisible ante cualquier tribunal conforme a eIDAS y Ley 6/2020.',
                        cta: 'Crear acuerdo NDA'
                    }
                }
            }">
                <div class="bg-slate-900 border-2 border-emerald-500/50 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-emerald-950/60 space-y-6">
                    
                    {{-- Simulator Header & Interactive Switcher Tabs --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800 pb-5">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3 py-1 rounded-full">
                                Simulador interactivo en vivo
                            </span>
                            <h3 class="text-xl font-black text-white mt-1">Comprueba cómo Tratix redacta tu contrato en tiempo real</h3>
                        </div>

                        {{-- Type Selector Buttons --}}
                        <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl bg-slate-950 border border-slate-800">
                            <button type="button" @click="contractType = 'vehiculos'"
                                :class="contractType === 'vehiculos' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md' : 'text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                                <span>🚗</span>
                                <span>Vehículo</span>
                            </button>
                            <button type="button" @click="contractType = 'arras'"
                                :class="contractType === 'arras' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md' : 'text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                                <span>🏠</span>
                                <span>Arras</span>
                            </button>
                            <button type="button" @click="contractType = 'servicios'"
                                :class="contractType === 'servicios' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md' : 'text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                                <span>💼</span>
                                <span>Servicios</span>
                            </button>
                            <button type="button" @click="contractType = 'nda'"
                                :class="contractType === 'nda' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md' : 'text-slate-400 hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                                <span>🔒</span>
                                <span>NDA</span>
                            </button>
                        </div>
                    </div>

                    {{-- Live Generated Preview Card --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                        
                        {{-- Meta details --}}
                        <div class="space-y-4 bg-slate-950/80 p-5 rounded-2xl border border-slate-800 text-xs">
                            <div>
                                <span class="text-slate-500 block">Tipo de acuerdo:</span>
                                <strong class="text-white text-sm" x-text="types[contractType].name"></strong>
                                <span class="text-[10px] text-emerald-400 font-semibold block" x-text="types[contractType].badge"></span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-900">
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Parte A (Vendedor):</span>
                                    <span class="text-slate-200 font-medium" x-text="types[contractType].seller"></span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Parte B (Comprador):</span>
                                    <span class="text-slate-200 font-medium" x-text="types[contractType].buyer"></span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-900">
                                <span class="text-slate-500 block text-[11px]">Importe / Cuantía:</span>
                                <span class="text-emerald-400 font-mono font-bold text-sm" x-text="types[contractType].price"></span>
                            </div>

                            <div class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-800/60 space-y-1 text-[11px]">
                                <span class="text-emerald-400 font-bold flex items-center gap-1.5">
                                    <span>🪪</span>
                                    <span>OCR y eIDAS verificados</span>
                                </span>
                                <p class="text-slate-400">Fotos de DNI adjuntas y firma certificada.</p>
                            </div>
                        </div>

                        {{-- Generated Legal Clause Window --}}
                        <div class="lg:col-span-2 space-y-4 bg-slate-950 p-6 rounded-2xl border border-slate-800 font-mono text-xs">
                            <div class="flex items-center justify-between border-b border-slate-900 pb-3 text-slate-500">
                                <span class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                    <span>Cláusula especial generada automáticamente</span>
                                </span>
                                <span class="text-[10px] text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-800">
                                    Validez jurídica eIDAS
                                </span>
                            </div>

                            <p class="text-slate-200 font-sans text-xs leading-relaxed italic bg-slate-900/60 p-4 rounded-xl border border-slate-800/80" x-text="'« ' + types[contractType].clause + ' »'"></p>

                            <div class="flex items-center justify-between pt-2 text-[11px] font-sans">
                                <span class="text-slate-400 flex items-center gap-1">
                                    <span>🛡️</span>
                                    <span x-text="types[contractType].dgt"></span>
                                </span>
                                <a href="{{ route('register') }}" class="btn-primary text-xs px-4 py-2 font-bold shadow-md shadow-emerald-950 shrink-0">
                                    <span x-text="types[contractType].cta"></span> →
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- Trust Metrics Ribbon --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-4xl mx-auto text-left">
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/90 shadow-lg">
                    <span class="text-2xl font-black text-emerald-400 block">3 min</span>
                    <span class="text-xs text-slate-300 font-semibold block mt-0.5">Tiempo de redacción</span>
                    <span class="text-[11px] text-slate-500 block">Frente a varios días con métodos tradicionales.</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/90 shadow-lg">
                    <span class="text-2xl font-black text-teal-400 block">100%</span>
                    <span class="text-xs text-slate-300 font-semibold block mt-0.5">Validez eIDAS</span>
                    <span class="text-[11px] text-slate-500 block">Admisible en cualquier tribunal de la UE.</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/90 shadow-lg">
                    <span class="text-2xl font-black text-cyan-400 block">5 seg</span>
                    <span class="text-xs text-slate-300 font-semibold block mt-0.5">Escaneo OCR de DNI</span>
                    <span class="text-[11px] text-slate-500 block">Extracción rápida de datos fiscales.</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/90 shadow-lg">
                    <span class="text-2xl font-black text-amber-400 block">SHA-256</span>
                    <span class="text-xs text-slate-300 font-semibold block mt-0.5">Sellado criptográfico</span>
                    <span class="text-[11px] text-slate-500 block">Custodia inalterable de evidencias.</span>
                </div>
            </div>

            </div>
        </div>
    </header>

    <!-- COMPARISON MATRIX: TRATIX VS EL VIEJO PDF DE GOOGLE -->
    <section id="comparativa" class="py-20 bg-slate-900/60 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-widest text-rose-400 bg-rose-950/80 border border-rose-900 px-3.5 py-1 rounded-full">
                    El riesgo de lo «gratis» en internet
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">¿Por qué rellenar un PDF descargado es una trampa legal?</h2>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Buscar plantillas genéricas en internet parece rápido, pero genera la mayoría de los litigios y problemas legales entre particulares y profesionales.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                {{-- THE OLD WAY: PDF / WORD --}}
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-950 border border-rose-900/40 shadow-xl space-y-6 relative overflow-hidden">
                    <div class="flex items-center justify-between border-b border-rose-950 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-rose-950 border border-rose-800 flex items-center justify-center text-rose-400 font-bold text-xl">✕</span>
                            <div>
                                <h3 class="text-lg font-bold text-white">El archivo PDF o Word descargado de internet</h3>
                                <span class="text-xs text-rose-400 font-semibold">Lento, desactualizado e inseguro</span>
                            </div>
                        </div>
                    </div>

                    <ul class="space-y-4 text-xs sm:text-sm text-slate-300">
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                            <span><strong>Cláusulas nulas o ilegales:</strong> Modelos antiguos que ignoran la normativa vigente o el régimen fiscal aplicable (ITP frente a IVA).</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                            <span><strong>Papeleo físico engorroso:</strong> Requiere impresora, bolígrafo, tachones, escanear hojas torcidas y riesgo de pérdida de copias.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                            <span><strong>Sin verificación de identidad:</strong> Cualquiera puede inventar un documento falso o firmar por otra persona sin control.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                            <span><strong>Firma manual escaneada fácilmente impugnable:</strong> Pegar una imagen de una firma carece de valor probatorio fehaciente en juicio.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                            <span><strong>Negociación caótica:</strong> Cruces interminables de correos con versiones contradictorias («contrato_final_v3.pdf»).</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                            <span><strong>Olvido de trámites obligatorios:</strong> Nadie te informa de los plazos de Hacienda (modelos 620/600), DGT o certificados preceptivos.</span>
                        </li>
                    </ul>
                </div>

                {{-- THE TRATIX WAY --}}
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border-2 border-emerald-500/70 shadow-2xl shadow-emerald-950/50 space-y-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-emerald-500 text-slate-950 text-[10px] font-black uppercase px-4 py-1 rounded-bl-xl tracking-wider">
                        Recomendado por abogados
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-emerald-950 border border-emerald-700 flex items-center justify-center text-emerald-400 font-bold text-xl">✓</span>
                            <div>
                                <h3 class="text-lg font-bold text-white">La solución Tratix</h3>
                                <span class="text-xs text-emerald-400 font-semibold">100% digital, rápida y con rigor jurídico</span>
                            </div>
                        </div>
                    </div>

                    <ul class="space-y-4 text-xs sm:text-sm text-slate-200">
                        <li class="flex items-start gap-3">
                            <span class="text-emerald-400 font-bold text-base shrink-0">✓</span>
                            <span><strong>Articulado jurídico adaptado:</strong> Generación exacta de cláusulas según el régimen de las partes (B2B, B2C, C2C) y la legislación vigente.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-emerald-400 font-bold text-base shrink-0">✓</span>
                            <span><strong>Escáner OCR de DNI (anverso y reverso):</strong> Captura con el móvil, lectura de datos y documento incorporado al expediente legal.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-emerald-400 font-bold text-base shrink-0">✓</span>
                            <span><strong>Delegación a la otra parte:</strong> Si no tienes sus datos, envíale un enlace privado para que los complete sin necesidad de cuenta.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-emerald-400 font-bold text-base shrink-0">✓</span>
                            <span><strong>Firma electrónica con validez eIDAS:</strong> Sellado de tiempo RFC 3161, IP registrada y hash criptográfico SHA-256.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-emerald-400 font-bold text-base shrink-0">✓</span>
                            <span><strong>Negociación con control de cambios en vivo:</strong> Propón modificaciones cláusula a cláusula con total transparencia.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-emerald-400 font-bold text-base shrink-0">✓</span>
                            <span><strong>Expediente de trámites legales:</strong> Guía con enlaces directos a la DGT, Registro y modelos tributarios correspondientes.</span>
                        </li>
                    </ul>
                </div>

            </div>

            {{-- Google AdSense Sponsored Unit --}}
            <x-ad-slot class="pt-6" />
        </div>
    </section>

    <!-- HOW IT WORKS: 4 SIMPLE STEPS -->
    <section id="como-funciona" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3.5 py-1 rounded-full">
                    Flujo 100% sin fricción
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">De la idea a la firma en 4 pasos</h2>
                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Diseñado para que cualquier persona, sin conocimientos jurídicos previos, formalice un contrato impecable desde su teléfono u ordenador.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Step 1 --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 hover:border-emerald-500/50 transition">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-emerald-950 border border-emerald-800 flex items-center justify-center text-emerald-400 font-black text-lg">1</span>
                        <span class="text-xs font-bold text-slate-500">Paso 01</span>
                    </div>
                    <h3 class="text-lg font-bold text-white">Elige la plantilla y tu rol</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Selecciona el tipo de acuerdo (vehículo, arras, servicios, alquiler...) e indica si actúas como comprador o vendedor.
                    </p>
                    <div class="pt-2 text-[11px] text-emerald-400 font-semibold">
                        ✨ Sugerencias y ejemplos en un clic
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 hover:border-emerald-500/50 transition">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-blue-950 border border-blue-800 flex items-center justify-center text-blue-400 font-black text-lg">2</span>
                        <span class="text-xs font-bold text-slate-500">Paso 02</span>
                    </div>
                    <h3 class="text-lg font-bold text-white">OCR de DNI o delegación</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Fotografía el documento de identidad para completar los datos o envía el enlace a la otra parte para que los rellene.
                    </p>
                    <div class="pt-2 text-[11px] text-blue-400 font-semibold">
                        🪪 Lectura de DNI 3.0 / 4.0 y NIE
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 hover:border-emerald-500/50 transition">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-amber-950 border border-amber-800 flex items-center justify-center text-amber-400 font-black text-lg">3</span>
                        <span class="text-xs font-bold text-slate-500">Paso 03</span>
                    </div>
                    <h3 class="text-lg font-bold text-white">Revisión y negociación</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Ambas partes revisan el borrador, proponen ajustes en precio o plazos y adjuntan la documentación necesaria.
                    </p>
                    <div class="pt-2 text-[11px] text-amber-400 font-semibold">
                        💬 Control de propuestas en vivo
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 hover:border-emerald-500/50 transition">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-purple-950 border border-purple-800 flex items-center justify-center text-purple-400 font-black text-lg">4</span>
                        <span class="text-xs font-bold text-slate-500">Paso 04</span>
                    </div>
                    <h3 class="text-lg font-bold text-white">Firma eIDAS y sellado</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Firma electrónica con plena validez legal. El contrato se bloquea y se custodia con su certificado probatorio de evidencias.
                    </p>
                    <div class="pt-2 text-[11px] text-purple-400 font-semibold">
                        🔒 Sellado de tiempo SHA-256
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- KEY ADVANTAGES -->
    <section id="ventajas" class="py-20 bg-slate-900/40 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-teal-400 bg-teal-950 border border-teal-800 px-3.5 py-1 rounded-full">
                    Tecnología al servicio de tu tranquilidad
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">Todo lo que necesitas para pactar sin riesgos</h2>
                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Tratix combina rigor jurídico y facilidad de uso para que cierres acuerdos con absoluta confianza.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="p-7 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-emerald-500/60 transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-950 border border-emerald-800 flex items-center justify-center text-2xl">
                        🪪
                    </div>
                    <h3 class="text-xl font-bold text-white">Escáner OCR dual de anverso y reverso</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Sube o fotografía el documento de identidad desde tu móvil. El motor OCR extrae los datos fiscales y adjunta las fotos oficiales al expediente contractual de forma inmutable.
                    </p>
                </div>

                <div class="p-7 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-emerald-500/60 transition">
                    <div class="w-12 h-12 rounded-2xl bg-blue-950 border border-blue-800 flex items-center justify-center text-2xl">
                        🤝
                    </div>
                    <h3 class="text-xl font-bold text-white">Delegación a la otra parte sin registro</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Si no conoces el DNI o domicilio de la otra parte, no te preocupes: envíale un enlace privado. Podrá completar su información y verificar su identidad en segundos desde su móvil.
                    </p>
                </div>

                <div class="p-7 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-emerald-500/60 transition">
                    <div class="w-12 h-12 rounded-2xl bg-purple-950 border border-purple-800 flex items-center justify-center text-2xl">
                        ✍️
                    </div>
                    <h3 class="text-xl font-bold text-white">Firma electrónica eIDAS certificada</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Firma digital con registro de evidencias, sellado de tiempo RFC 3161 y generación de la hoja de evidencias con trazabilidad de IP y consentimientos explícitos.
                    </p>
                </div>

                <div class="p-7 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-emerald-500/60 transition">
                    <div class="w-12 h-12 rounded-2xl bg-teal-950 border border-teal-800 flex items-center justify-center text-2xl">
                        📁
                    </div>
                    <h3 class="text-xl font-bold text-white">Expediente documental y guía de trámites</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Guía clasificada entre documentación obligatoria por ley y recomendada. Enlaces directos para tramitar el ITP, transferencias en la DGT o consultas registrales.
                    </p>
                </div>

                <div class="p-7 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-emerald-500/60 transition">
                    <div class="w-12 h-12 rounded-2xl bg-amber-950 border border-amber-800 flex items-center justify-center text-2xl">
                        ⚡
                    </div>
                    <h3 class="text-xl font-bold text-white">Sugerencias y ejemplos en un clic</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Opciones rápidas que insertan ejemplos redactados por abogados para condiciones de pago, garantías, plazos de entrega y estado de cargas adaptados a cada contrato.
                    </p>
                </div>

                <div class="p-7 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 hover:border-emerald-500/60 transition">
                    <div class="w-12 h-12 rounded-2xl bg-rose-950 border border-rose-800 flex items-center justify-center text-2xl">
                        🛡️
                    </div>
                    <h3 class="text-xl font-bold text-white">Máxima privacidad RGPD y custodia segura</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Cumplimiento riguroso del RGPD y la LOPDGDD. Almacenamiento seguro y cifrado en la Unión Europea, purga periódica de temporales y contacto directo con el DPO.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- INTERACTIVE CONTRACT MODELS CATALOG WITH FILTER PILLS -->
    <section id="modelos" class="py-20" x-data="{
        filter: 'all',
        models: [
            { category: 'motor', icon: '🚗', name: 'Compraventa de vehículos usados', desc: 'Coches, motos, furgonetas y embarcaciones. Incluye cláusula de estado técnico, exención de vicios ocultos y guía para la DGT.', tag: 'El más utilizado', tagColor: 'emerald' },
            { category: 'inmueble', icon: '🏠', name: 'Contrato de arras penitenciales', desc: 'Conforme al artículo 1454 del Código Civil español. Reserva de vivienda con penalización por duplicado o desistimiento legal.', tag: 'Inmobiliario', tagColor: 'blue' },
            { category: 'b2b', icon: '💼', name: 'Prestación de servicios profesionales', desc: 'Para autónomos, consultores y agencias. Hitos de entrega, confidencialidad, cesión de propiedad intelectual y penalizaciones.', tag: 'B2B y freelance', tagColor: 'purple' },
            { category: 'b2b', icon: '📦', name: 'Compraventa de bienes muebles', desc: 'Maquinaria, electrónica, mobiliario y existencias comerciales. Adaptable a transacciones nacionales e intracomunitarias (VIES).', tag: 'Mercantil', tagColor: 'slate' },
            { category: 'seguridad', icon: '🔒', name: 'Acuerdo de confidencialidad (NDA)', desc: 'Bilateral o unilateral para proyectos de negocio, software, patentes o intercambio de información confidencial.', tag: 'Protección', tagColor: 'amber' },
            { category: 'finanzas', icon: '💶', name: 'Préstamo y reconocimiento de deuda', desc: 'Préstamos entre familiares o particulares sin intereses, exentos del Impuesto sobre Transmisiones Patrimoniales (ITP).', tag: 'Financiero', tagColor: 'teal' }
        ]
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3.5 py-1 rounded-full">
                    Especialización legal
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">Plantillas jurídicas para cualquier acuerdo</h2>
                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Cada contrato incluye el articulado técnico necesario para asegurar la operación ante Hacienda, tribunales y terceros.
                </p>

                {{-- Interactive Filter Buttons --}}
                <div class="pt-4 flex flex-wrap items-center justify-center gap-2">
                    <button type="button" @click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:border-slate-700'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition">
                        Todos los modelos
                    </button>
                    <button type="button" @click="filter = 'motor'"
                        :class="filter === 'motor' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:border-slate-700'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition flex items-center gap-1">
                        <span>🚗</span> Motor y DGT
                    </button>
                    <button type="button" @click="filter = 'inmueble'"
                        :class="filter === 'inmueble' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:border-slate-700'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition flex items-center gap-1">
                        <span>🏠</span> Inmobiliario
                    </button>
                    <button type="button" @click="filter = 'b2b'"
                        :class="filter === 'b2b' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:border-slate-700'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition flex items-center gap-1">
                        <span>💼</span> Empresas y autónomos
                    </button>
                    <button type="button" @click="filter = 'seguridad'"
                        :class="filter === 'seguridad' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:border-slate-700'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition flex items-center gap-1">
                        <span>🔒</span> Acuerdos NDA y finanzas
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in models" :key="item.name">
                    <div x-show="filter === 'all' || filter === item.category" 
                        x-transition.opacity
                        class="p-6 bg-slate-900 border border-slate-800 rounded-3xl space-y-3 hover:border-emerald-500/80 transition flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl" x-text="item.icon"></span>
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800" x-text="item.tag"></span>
                            </div>
                            <h3 class="text-lg font-bold text-white" x-text="item.name"></h3>
                            <p class="text-xs text-slate-400 leading-relaxed" x-text="item.desc"></p>
                        </div>
                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between">
                            <span class="text-[11px] text-emerald-400 font-semibold">100% legal eIDAS</span>
                            <a href="{{ route('register') }}" class="text-xs font-bold text-white hover:text-emerald-400 transition flex items-center gap-1">
                                <span>Crear</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <!-- REFERRAL PROGRAM & REWARDS CALCULATOR -->
    <section id="referidos" class="py-20 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/40 border-y border-emerald-900/40"
        x-data="{
            referrals: 5,
            proPrice: 19,
            get freeMonths() {
                return this.referrals;
            },
            get totalSavings() {
                return (this.referrals * this.proPrice).toLocaleString('es-ES');
            }
        }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-black uppercase tracking-widest text-amber-400 bg-amber-950/80 border border-amber-800 px-4 py-1.5 rounded-full shadow-md">
                    🎁 Programa de invitaciones y recompensas
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">Invita a tus contactos y ambos conseguís 1 mes Pro gratis</h2>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Comparte tu enlace exclusivo con amigos, clientes, autónomos o empresas. Por cada registro, tú y tu contacto recibís <strong>1 mes de Tratix Pro gratis</strong> (valorado en 19 €) y <strong>1 crédito extra de sellado</strong>.
                </p>
            </div>

            {{-- Interactive Free Months & Savings Calculator Card --}}
            <div class="max-w-3xl mx-auto p-6 sm:p-8 rounded-3xl bg-slate-950 border border-amber-500/40 shadow-2xl shadow-amber-950/20 space-y-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>🧮 Calculadora de ahorro y meses Pro acumulados</span>
                        </h3>
                        <span class="text-xs text-slate-400">Ajusta el número de amigos o colegas que invitarías a Tratix</span>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-amber-400 block" x-text="freeMonths + ' meses Pro GRATIS'"></span>
                        <span class="text-[10px] text-emerald-400 font-bold block" x-text="'(Ahorras ' + totalSavings + ' € en suscripción)'"></span>
                    </div>
                </div>

                {{-- Interactive Range Slider --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs text-slate-300 font-semibold">
                        <span>Contactos o clientes invitados:</span>
                        <span class="text-amber-400 font-bold" x-text="referrals + ' registros completados'"></span>
                    </div>
                    <input type="range" min="1" max="24" step="1" x-model="referrals" 
                        class="w-full h-2.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-amber-400">
                    <div class="flex justify-between text-[10px] text-slate-500">
                        <span>1 amigo</span>
                        <span>6 amigos</span>
                        <span>12 amigos</span>
                        <span>24 amigos (¡2 años gratis!)</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                    <div class="text-slate-300 text-[11px] leading-relaxed">
                        ✓ <strong>Beneficio mutuo:</strong> Tu invitado también recibe 1 mes de Plan Pro gratis de bienvenida y 1 crédito de sellado.
                    </div>
                    <a href="{{ route('register') }}" class="btn-primary text-xs px-6 py-2.5 font-bold shadow-lg shadow-emerald-950 shrink-0">
                        🎁 Obtener mi enlace de invitación →
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto pt-4">
                <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-3 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-950 border border-amber-800 flex items-center justify-center text-xl font-black text-amber-400">1</div>
                    <h3 class="text-base font-bold text-white">Obtén tu enlace único</h3>
                    <p class="text-xs text-slate-400">Copia tu enlace personal desde tu panel de usuario o perfil con un solo clic.</p>
                </div>

                <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-3 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-950 border border-emerald-800 flex items-center justify-center text-xl font-black text-emerald-400">2</div>
                    <h3 class="text-base font-bold text-white">Compártelo con tus contactos</h3>
                    <p class="text-xs text-slate-400">Envíalo por WhatsApp, email o redes a quien necesite redactar o firmar contratos.</p>
                </div>

                <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-3 text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-teal-950 border border-teal-800 flex items-center justify-center text-xl font-black text-teal-400">3</div>
                    <h3 class="text-base font-bold text-white">Recompensas inmediatas</h3>
                    <p class="text-xs text-slate-400">En cuanto se registren, se os activa a ambos 1 mes de Plan Pro y créditos de sellado.</p>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('register') }}" class="btn-primary inline-flex items-center gap-2 text-sm px-8 py-3.5 font-extrabold shadow-xl shadow-emerald-950">
                    <span>🎁 Unirme al programa y conseguir meses Pro gratis</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- PRICING PLANS -->
    <section id="precios" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3.5 py-1 rounded-full">
                    Precios claros y sin sorpresas
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">Empieza gratis y amplía cuando lo necesites</h2>
                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Todos los planes incluyen validez legal eIDAS, custodia criptográfica y escáner OCR de DNI.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                
                {{-- Free Plan --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-xl space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Plan inicial</span>
                        <h3 class="text-2xl font-bold text-white">Gratis</h3>
                        <div class="text-3xl font-black text-white">0 € <span class="text-xs text-slate-500 font-normal">/ para siempre</span></div>
                        <p class="text-xs text-slate-400">Ideal para compras o ventas puntuales entre particulares.</p>

                        <ul class="space-y-3 text-xs text-slate-300 pt-4 border-t border-slate-800">
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> 2 contratos legales al mes</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Escáner OCR de DNI (anverso y reverso)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Firma electrónica eIDAS</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Sellado temporal SHA-256</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Expediente documental básico</li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="btn-outline w-full text-center text-xs py-3 font-bold hover:border-emerald-500 hover:text-emerald-300">
                        Comenzar gratis
                    </a>
                </div>

                {{-- Pro Plan --}}
                <div class="bg-slate-900 border-2 border-emerald-500 rounded-3xl p-8 shadow-2xl shadow-emerald-950/60 space-y-6 flex flex-col justify-between relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-500 text-slate-950 text-[10px] font-black uppercase px-4 py-1 rounded-full tracking-wider shadow-md">
                        Más popular
                    </div>

                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block">Plan Profesional</span>
                        <h3 class="text-2xl font-bold text-white">Pro</h3>
                        <div class="text-3xl font-black text-white">19 € <span class="text-xs text-slate-500 font-normal">/ mes</span></div>
                        <p class="text-xs text-slate-400">Para autónomos, profesionales y usuarios activos.</p>

                        <ul class="space-y-3 text-xs text-slate-200 pt-4 border-t border-slate-800">
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> <strong>Contratos ilimitados</strong></li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Todas las plantillas avanzadas (B2B, arras, acuerdos NDA)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Exportación masiva en archivo ZIP</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Hoja de evidencias extendida</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Soporte prioritario en menos de 4 h</li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="btn-primary w-full text-center text-xs py-3.5 font-bold shadow-lg shadow-emerald-950">
                        Probar Plan Pro
                    </a>
                </div>

                {{-- Business Plan --}}
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-xl space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Plan Empresa</span>
                        <h3 class="text-2xl font-bold text-white">Business</h3>
                        <div class="text-3xl font-black text-white">49 € <span class="text-xs text-slate-500 font-normal">/ mes</span></div>
                        <p class="text-xs text-slate-400">Para agencias, gestorías, concesionarios y despachos.</p>

                        <ul class="space-y-3 text-xs text-slate-300 pt-4 border-t border-slate-800">
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Todo lo incluido en Pro</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Múltiples agentes y usuarios de equipo</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Logotipo y marca corporativa en los contratos</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Integración mediante API y webhooks</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Asesor legal asignado</li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="btn-outline w-full text-center text-xs py-3 font-bold hover:border-emerald-500 hover:text-emerald-300">
                        Contratar Business
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ABOUT US SECTION -->
    <section id="sobre-nosotros" class="py-20 bg-slate-900/60 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3.5 py-1 rounded-full">
                    Sobre nosotros
                </span>
                <h2 class="text-3xl sm:text-5xl font-black text-white">Nuestra misión: democratizar la seguridad jurídica sin burocracia</h2>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Tratix nació en España de la unión de juristas especializados en derecho mercantil y desarrolladores de software con una visión clara: poner fin a la desprotección y a la pérdida de tiempo que sufren particulares y empresas al formalizar acuerdos cotidianos.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-3">
                    <span class="text-2xl font-black text-emerald-400 block">01. Rigor jurídico</span>
                    <h3 class="text-base font-bold text-white">Actualización continua</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Nuestras plantillas y modelos son revisados periódicamente por abogados colegiados en España y juristas para reflejar los últimos cambios legales y jurisprudenciales.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-3">
                    <span class="text-2xl font-black text-blue-400 block">02. Privacidad ética</span>
                    <h3 class="text-base font-bold text-white">Cumplimiento riguroso del RGPD</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        No comercializamos tus datos ni los cedemos a terceros. Tu documentación se almacena cifrada en servidores seguros de la Unión Europea bajo estricta confidencialidad.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-3">
                    <span class="text-2xl font-black text-teal-400 block">03. Facilidad de uso</span>
                    <h3 class="text-base font-bold text-white">Sin curva de aprendizaje</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Creemos que el mejor software es aquel que no requiere manuales. Tratix te guía paso a paso para que solo tengas que confirmar los términos de tu acuerdo.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="py-20" x-data="{ openFaq: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3.5 py-1 rounded-full">
                    Preguntas frecuentes
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-white">Resolvemos tus dudas legales y técnicas</h2>
            </div>

            <div class="space-y-4 text-xs sm:text-sm">
                
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 transition">
                    <button type="button" @click="openFaq = (openFaq === 1 ? null : 1)" class="w-full flex items-center justify-between text-left font-bold text-white">
                        <span>¿Qué validez legal tiene la firma electrónica generada en Tratix?</span>
                        <span class="text-emerald-400 text-lg" x-text="openFaq === 1 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 1" x-transition.opacity class="mt-3 text-slate-400 leading-relaxed pt-2 border-t border-slate-800">
                        La firma electrónica de Tratix cumple rigurosamente con el Reglamento Europeo (UE) Nº 910/2014 (eIDAS), la Ley 6/2020 de servicios electrónicos de confianza y el Código Civil. Cada contrato firmado incluye una hoja de evidencias digital con registro de direcciones IP, fecha y hora oficial sellada (RFC 3161) y el hash criptográfico SHA-256 que garantiza que el documento no ha sido alterado.
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 transition">
                    <button type="button" @click="openFaq = (openFaq === 2 ? null : 2)" class="w-full flex items-center justify-between text-left font-bold text-white">
                        <span>¿La otra parte está obligada a crearse una cuenta para firmar?</span>
                        <span class="text-emerald-400 text-lg" x-text="openFaq === 2 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 2" x-transition.opacity class="mt-3 text-slate-400 leading-relaxed pt-2 border-t border-slate-800">
                        No. Tratix está diseñado para eliminar cualquier fricción: puedes enviar un enlace seguro por correo electrónico o WhatsApp. La contraparte accede directamente, revisa las cláusulas, puede proponer cambios o completar sus datos de DNI y firma de forma inmediata sin pagar ni registrarse.
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 transition">
                    <button type="button" @click="openFaq = (openFaq === 3 ? null : 3)" class="w-full flex items-center justify-between text-left font-bold text-white">
                        <span>¿Cómo protege Tratix las fotos de mi DNI y mis datos personales?</span>
                        <span class="text-emerald-400 text-lg" x-text="openFaq === 3 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 3" x-transition.opacity class="mt-3 text-slate-400 leading-relaxed pt-2 border-t border-slate-800">
                        Tus documentos se procesan mediante canales cifrados TLS 1.3 y se almacenan en repositorios privados con cifrado de alto nivel en la Unión Europea. Solo las partes del contrato tienen acceso a ellos. Además, dispones de herramientas directas para solicitar la portabilidad o supresión de tus datos en cualquier momento conforme al RGPD.
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 transition">
                    <button type="button" @click="openFaq = (openFaq === 4 ? null : 4)" class="w-full flex items-center justify-between text-left font-bold text-white">
                        <span>¿Qué trámites debo realizar después de firmar (Hacienda, DGT, etc.)?</span>
                        <span class="text-emerald-400 text-lg" x-text="openFaq === 4 ? '−' : '+'"></span>
                    </button>
                    <div x-show="openFaq === 4" x-transition.opacity class="mt-3 text-slate-400 leading-relaxed pt-2 border-t border-slate-800">
                        Una vez firmado el contrato, Tratix genera automáticamente un expediente documental con la guía específica para tu tipo de acuerdo: te indicamos cómo y dónde liquidar el Impuesto sobre Transmisiones Patrimoniales (modelos 620/600), cómo tramitar el cambio de titularidad en la DGT en compraventas de vehículos o qué hacer con la fianza en contratos de alquiler.
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CONTACT & SUPPORT SECTION -->
    <section id="contacto" class="py-20 bg-slate-900/80 border-t border-slate-800" x-data="{
        form: { name: '', email: '', subject: '', message: '', gdpr_consent: false, website_hp: '' },
        sending: false,
        sent: false,
        error: '',
        async sendContact() {
            if (!this.form.gdpr_consent) {
                this.error = 'Debes aceptar la política de privacidad para enviar el mensaje.';
                return;
            }
            this.sending = true;
            this.error = '';
            try {
                const res = await fetch('{{ route('contact.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.sent = true;
                } else {
                    this.error = data.message || 'Error al enviar el mensaje. Por favor revisa los campos.';
                }
            } catch (e) {
                this.error = 'Error de conexión. Inténtalo de nuevo en unos momentos.';
            } finally {
                this.sending = false;
            }
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div class="space-y-6">
                    <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 bg-emerald-950 border border-emerald-800 px-3.5 py-1 rounded-full">
                        Atención personalizada
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white leading-tight">
                        ¿Tienes dudas o necesitas un plan a medida?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        Nuestro equipo de soporte técnico y atención jurídica está a tu disposición para ayudarte a formalizar cualquier tipo de acuerdo o integrar Tratix en tu empresa.
                    </p>

                    <div class="space-y-3 pt-4 text-xs sm:text-sm">
                        <div class="flex items-center gap-3 text-slate-300">
                            <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-emerald-400 font-bold">✉️</span>
                            <span>Soporte general: <strong class="text-white">soporte@@tratix.com</strong></span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-300">
                            <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-blue-400 font-bold">🛡️</span>
                            <span>Delegado de Protección de Datos: <strong class="text-white">dpo@@tratix.com</strong></span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-300">
                            <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-teal-400 font-bold">⏱️</span>
                            <span>Tiempo medio de respuesta: <strong class="text-emerald-400">Menos de 2 horas laborables</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Interactive Form Card --}}
                <div class="p-8 rounded-3xl bg-slate-950 border border-slate-800 shadow-2xl">
                    <template x-if="sent">
                        <div class="p-6 text-center space-y-4 bg-emerald-950/40 border border-emerald-500/50 rounded-2xl">
                            <span class="text-4xl">🎉</span>
                            <h3 class="text-lg font-bold text-white">¡Mensaje recibido con éxito!</h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Gracias por escribirnos. Uno de nuestros especialistas se pondrá en contacto contigo en tu correo a la mayor brevedad.
                            </p>
                            <button type="button" @click="sent = false; form = { name: '', email: '', subject: '', message: '', gdpr_consent: false, website_hp: '' }" class="btn-outline text-xs px-4 py-2 mt-2">
                                Enviar otra consulta
                            </button>
                        </div>
                    </template>

                    <template x-if="!sent">
                        <form @submit.prevent="sendContact()" class="space-y-4 text-xs">
                            <div x-show="error" x-text="error" class="p-3 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-xs"></div>

                            {{-- Honeypot anti spam --}}
                            <input type="text" name="website_hp" x-model="form.website_hp" class="hidden" tabindex="-1" autocomplete="off">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Nombre Completo *</label>
                                    <input type="text" x-model="form.name" required placeholder="Tu nombre"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2.5 text-slate-100 focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Correo electrónico *</label>
                                    <input type="email" x-model="form.email" required placeholder="tu@email.com"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2.5 text-slate-100 focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Asunto de la consulta *</label>
                                <input type="text" x-model="form.subject" required placeholder="Ej: Duda sobre contrato de arras o plan Business"
                                    class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2.5 text-slate-100 focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Mensaje o consulta detallada *</label>
                                <textarea x-model="form.message" rows="4" required placeholder="Explícanos brevemente qué necesitas..."
                                    class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2.5 text-slate-100 focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <label class="flex items-start gap-2.5 cursor-pointer text-[11px] text-slate-300">
                                    <input type="checkbox" x-model="form.gdpr_consent" required class="mt-0.5 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500">
                                    <span>
                                        He leído y acepto la <a href="{{ route('privacy') }}" target="_blank" class="text-emerald-400 underline font-semibold">Política de Privacidad</a> y autorizo el tratamiento de mis datos para responder a mi consulta (RGPD).
                                    </span>
                                </label>
                            </div>

                            <button type="submit" :disabled="sending" class="btn-primary w-full text-xs sm:text-sm py-3 font-bold shadow-lg shadow-emerald-950 flex items-center justify-center gap-2">
                                <span x-show="!sending">📨 Enviar mensaje a soporte</span>
                                <span x-show="sending">Enviando mensaje...</span>
                            </button>
                        </form>
                    </template>
                </div>

            </div>
        </div>
    </section>

    <!-- FINAL CTA BANNER -->
    <section class="py-20 text-center relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <h2 class="text-3xl sm:text-5xl font-black text-white">¿Listo para formalizar tu próximo contrato con total tranquilidad?</h2>
            <p class="text-sm sm:text-base text-slate-400 max-w-2xl mx-auto">
                Crea tu cuenta gratuita en 30 segundos y experimenta la forma más rápida y segura de cerrar acuerdos legales.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="btn-primary text-sm sm:text-base px-8 py-4 font-extrabold shadow-2xl shadow-emerald-500/30 hover:scale-105 transition-transform">
                    🚀 Empezar gratis ahora (sin tarjeta)
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-950 border-t border-slate-800 text-slate-400 text-xs py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                
                <div class="col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-slate-950 font-black text-lg">T</div>
                        <span class="text-white font-black text-xl">Tratix</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Plataforma de generación, negociación y firma electrónica de contratos con plena validez legal eIDAS, lectura OCR de DNI y custodia criptográfica SHA-256.
                    </p>
                    <div class="text-[11px] text-slate-500">
                        🇪🇸 Desarrollado y alojado en la Unión Europea. Cumplimiento estricto del RGPD y la LOPDGDD.
                    </div>
                </div>

                <div class="space-y-2.5">
                    <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Modelos</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#modelos" class="hover:text-emerald-400 transition">Compraventa de vehículos</a></li>
                        <li><a href="#modelos" class="hover:text-emerald-400 transition">Arras penitenciales</a></li>
                        <li><a href="#modelos" class="hover:text-emerald-400 transition">Inmuebles y alquiler</a></li>
                        <li><a href="#modelos" class="hover:text-emerald-400 transition">Servicios y freelance</a></li>
                        <li><a href="#modelos" class="hover:text-emerald-400 transition">Acuerdos NDA</a></li>
                    </ul>
                </div>

                <div class="space-y-2.5">
                    <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Plataforma</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#como-funciona" class="hover:text-emerald-400 transition">Cómo funciona</a></li>
                        <li><a href="#comparativa" class="hover:text-emerald-400 transition">Tratix vs. PDF</a></li>
                        <li><a href="{{ route('billing.pricing') }}" class="hover:text-emerald-400 transition">Planes y precios</a></li>
                        <li><a href="{{ route('referrals.index') }}" class="hover:text-emerald-400 transition text-amber-400">Programa de referidos</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition">Acceso de usuarios</a></li>
                    </ul>
                </div>

                <div class="space-y-2.5">
                    <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Legal y RGPD</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">Política de privacidad</a></li>
                        <li><a href="{{ route('privacy') }}#cookies" class="hover:text-white transition">Política de cookies</a></li>
                        <li>
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('tratix:open-cookie-preferences'))" class="hover:text-emerald-400 text-left transition flex items-center gap-1">
                                <span>⚙️</span> Configuración de cookies
                            </button>
                        </li>
                        <li><a href="{{ route('privacy') }}#ejercicio-derechos" class="hover:text-white transition">Derechos ARCO+ (DPO)</a></li>
                        <li><a href="#contacto" class="hover:text-white transition">Contacto y soporte</a></li>
                    </ul>
                </div>

            </div>

            <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-400">
                <p>© {{ date('Y') }} Tratix Legal Tech. Todos los derechos reservados. Cumplimiento eIDAS (UE) 910/2014 y RGPD (UE) 2016/679.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('privacy') }}" class="hover:text-slate-300">Privacidad</a>
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('tratix:open-cookie-preferences'))" class="hover:text-slate-300">
                        Cookies
                    </button>
                    <a href="#contacto" class="hover:text-slate-300">Contacto</a>
                    <a href="{{ route('login') }}" class="hover:text-slate-300">Entrar</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Floating Glassmorphism Quick Section Jump Pill --}}
    <div x-show="scrolled" x-transition.opacity.duration.300ms
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-30 hidden sm:flex items-center gap-1 p-1.5 rounded-full bg-slate-900/90 backdrop-blur-md border border-slate-700/80 shadow-2xl shadow-slate-950 text-[11px] font-semibold text-slate-300">
        <a href="#simulador" 
            :class="activeSection === 'simulador' ? 'bg-emerald-500 text-slate-950 font-bold' : 'hover:text-white hover:bg-slate-800'"
            class="px-3 py-1 rounded-full transition flex items-center gap-1">
            <span>🎮</span>
            <span>Simulador</span>
        </a>
        <a href="#comparativa" 
            :class="activeSection === 'comparativa' ? 'bg-emerald-500 text-slate-950 font-bold' : 'hover:text-white hover:bg-slate-800'"
            class="px-3 py-1 rounded-full transition flex items-center gap-1">
            <span>⚖️</span>
            <span>Tratix vs. PDF</span>
        </a>
        <a href="#modelos" 
            :class="activeSection === 'modelos' ? 'bg-emerald-500 text-slate-950 font-bold' : 'hover:text-white hover:bg-slate-800'"
            class="px-3 py-1 rounded-full transition flex items-center gap-1">
            <span>📋</span>
            <span>Modelos</span>
        </a>
        <a href="#referidos" 
            :class="activeSection === 'referidos' ? 'bg-emerald-500 text-slate-950 font-bold' : 'hover:text-white hover:bg-slate-800'"
            class="px-3 py-1 rounded-full transition flex items-center gap-1 text-amber-400 font-bold">
            <span>🎁</span>
            <span>Invita y gana Pro</span>
        </a>
        <a href="#contacto" 
            :class="activeSection === 'contacto' ? 'bg-emerald-500 text-slate-950 font-bold' : 'hover:text-white hover:bg-slate-800'"
            class="px-3 py-1 rounded-full transition flex items-center gap-1">
            <span>📨</span>
            <span>Contacto</span>
        </a>
        <a href="{{ route('register') }}" class="ml-1 bg-emerald-500 text-slate-950 font-extrabold px-3 py-1 rounded-full hover:bg-emerald-400 transition shadow">
            Empezar gratis →
        </a>
    </div>

    {{-- Universal GDPR/AEPD Cookie Consent & Dynamic Analytics/AdSense Loader --}}
    <x-cookie-consent />

</body>
</html>
