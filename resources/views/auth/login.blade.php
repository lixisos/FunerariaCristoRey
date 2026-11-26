<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión — Funeraria Cristo Rey</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-serif-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-sans-ui { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans-ui text-[#251f54] bg-[#dcd5f2] antialiased selection:bg-violet-300 selection:text-indigo-950"
      x-data="{ verPassword: false }">

    <!-- FONDO FIJO A PANTALLA COMPLETA -->
    <div class="fixed inset-0 w-full h-full -z-10 pointer-events-none">
        <img src="{{ asset('images/login-cristo-rey.jpg') }}"
             onerror="this.onerror=null;this.src='{{ asset('images/hero-cristo-rey.jpg') }}';"
             alt="Funeraria Cristo Rey"
             class="w-full h-full object-cover object-center">
        <!-- Velo sutil para asegurar lectura perfecta de textos blancos y tarjeta -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#231b4d]/30 via-[#2a2159]/15 to-[#ebe6f8]/35"></div>
    </div>

    <div class="min-h-screen w-full flex flex-col justify-between relative overflow-x-hidden">

        <!-- 1. ENCABEZADO SUPERIOR CURVO (Onda lavanda + Logo + "Siempre acompañándote...") -->
        <header class="relative w-full pt-4 pb-8 px-6 sm:px-10 lg:px-16 z-20">
            <!-- Fondo curvo suave superior -->
            <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-[#ebe7f8]/95 via-[#e5dff6]/85 to-transparent pointer-events-none -z-10"></div>

            <!-- Ramas / Hojas decorativas esquina superior derecha -->
            <svg class="hidden sm:block absolute top-0 right-0 w-52 h-44 text-[#584ba3]/45 pointer-events-none -z-10" viewBox="0 0 200 180" fill="currentColor">
                <path d="M195 5 C165 35, 145 75, 130 130 C142 95, 162 55, 198 15 Z"/>
                <ellipse cx="152" cy="34" rx="16" ry="6" transform="rotate(-35 152 34)"/>
                <ellipse cx="176" cy="48" rx="15" ry="6" transform="rotate(20 176 48)"/>
                <ellipse cx="136" cy="64" rx="17" ry="6" transform="rotate(-40 136 64)"/>
                <ellipse cx="164" cy="78" rx="16" ry="6" transform="rotate(15 164 78)"/>
                <ellipse cx="124" cy="96" rx="16" ry="6" transform="rotate(-35 124 96)"/>
                <ellipse cx="150" cy="110" rx="15" ry="6" transform="rotate(20 150 110)"/>
            </svg>

            <div class="w-full flex items-center justify-between">
                <!-- Logo Izquierda -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-[#2c2566] transition-transform group-hover:scale-105">
                        <svg class="w-12 h-12" viewBox="0 0 64 64" fill="none">
                            <path d="M22 50C13 44 9 33 12 22C13.5 16.5 17 12 21 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            <path d="M42 50C51 44 55 33 52 22C50.5 16.5 47 12 43 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            <path d="M14 34L9 31M13 26L9 22M16 18L13 14M50 34L55 31M51 26L55 22M48 18L51 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M32 13V51M21 25H43" stroke="currentColor" stroke-width="3.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="font-serif-display leading-tight text-[#262058]">
                        <span class="block text-lg font-semibold tracking-tight">Funeraria</span>
                        <span class="block text-xl font-bold tracking-tight -mt-1">Cristo Rey</span>
                    </div>
                </a>

                <!-- Lema Central en Cursiva Serif -->
                <p class="hidden md:block font-serif-display italic text-2xl lg:text-[28px] text-[#28215c] tracking-wide drop-shadow-sm">
                    Siempre acompañándote...
                </p>

                <!-- Enlace discreto para volver al portal -->
                <a href="{{ route('home') }}" class="text-xs sm:text-sm font-semibold text-[#362c78] hover:text-[#1f194d] bg-white/70 hover:bg-white px-4 py-2 rounded-full shadow-sm transition">
                    ← Volver al inicio
                </a>
            </div>
        </header>

        <!-- 2. CUERPO CENTRAL: CITA EMOTIVA (IZQUIERDA) + TARJETA LOGIN GLASSMORPHISM (DERECHA) -->
        <main class="w-full flex-1 flex items-center px-6 sm:px-10 lg:px-16 xl:px-24 py-4 z-10">
            <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

                <!-- Columna Izquierda/Centro: Cita y Ornamento con Cruz -->
                <div class="lg:col-span-7 flex flex-col items-center lg:items-center justify-center text-center px-2 sm:px-8 lg:pt-12">
                    <blockquote class="font-serif-display italic text-2xl sm:text-3xl lg:text-[34px] text-white leading-snug drop-shadow-[0_3px_14px_rgba(24,18,58,0.75)] max-w-lg">
                        “En los momentos más difíciles,<br>
                        <span class="font-semibold">estamos contigo.”</span>
                    </blockquote>

                    <!-- Línea ornamental con cruz y puntos blancos -->
                    <div class="flex items-center justify-center gap-3 mt-4 w-full max-w-xs text-white drop-shadow-[0_2px_8px_rgba(24,18,58,0.8)]">
                        <span class="h-[1.5px] flex-1 bg-gradient-to-r from-transparent to-white/90"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <svg class="w-5 h-6 text-white" viewBox="0 0 20 26" fill="currentColor">
                            <rect x="8.5" y="1" width="3" height="24" rx="1"/>
                            <rect x="3" y="7" width="14" height="3" rx="1"/>
                        </svg>
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <span class="h-[1.5px] flex-1 bg-gradient-to-l from-transparent to-white/90"></span>
                    </div>
                </div>

                <!-- Columna Derecha: Tarjeta Translúcida de Inicio de Sesión -->
                <div class="lg:col-span-5 flex justify-center lg:justify-end">
                    <div class="w-full max-w-[440px] bg-white/80 backdrop-blur-xl rounded-[30px] p-7 sm:p-9 shadow-[0_24px_60px_rgba(27,22,66,0.28)] border border-white/90 space-y-6">

                        <!-- Encabezado de la Tarjeta -->
                        <div class="text-center space-y-2">
                            <div class="inline-flex items-center justify-center gap-3">
                                <svg class="w-11 h-11 text-[#2c2566]" viewBox="0 0 64 64" fill="none">
                                    <path d="M22 50C13 44 9 33 12 22C13.5 16.5 17 12 21 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                    <path d="M42 50C51 44 55 33 52 22C50.5 16.5 47 12 43 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                    <path d="M14 34L9 31M13 26L9 22M16 18L13 14M50 34L55 31M51 26L55 22M48 18L51 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M32 13V51M21 25H43" stroke="currentColor" stroke-width="3.6" stroke-linecap="round"/>
                                </svg>
                                <div class="font-serif-display text-left leading-tight text-[#251f54]">
                                    <span class="block text-base font-semibold">Funeraria</span>
                                    <span class="block text-xl font-bold -mt-1">Cristo Rey</span>
                                </div>
                            </div>

                            <h1 class="font-serif-display text-2xl sm:text-[28px] font-bold text-[#251f54] pt-1">
                                Iniciar Sesión
                            </h1>
                            <p class="text-xs text-[#575085]">
                                Accede a tu cuenta para gestionar nuestros servicios.
                            </p>
                        </div>

                        <!-- Mensajes de Error o Estado -->
                        @if (session('status'))
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium text-center">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                                @foreach ($errors->all() as $error)
                                    <p>• {{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <!-- Formulario Laravel Breeze -->
                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            <!-- Campo Correo Electrónico -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#5c548f] pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                       placeholder="Correo Electrónico"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/90 border border-[#d6cff0] focus:border-[#5b4db5] focus:ring-2 focus:ring-[#5b4db5]/25 text-sm text-[#251f54] placeholder-[#7871a3] shadow-inner transition">
                            </div>

                            <!-- Campo Contraseña con Ojo Mostrar/Ocultar -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#5c548f] pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <input id="password" :type="verPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                       placeholder="Contraseña"
                                       class="w-full pl-11 pr-11 py-3 rounded-xl bg-white/90 border border-[#d6cff0] focus:border-[#5b4db5] focus:ring-2 focus:ring-[#5b4db5]/25 text-sm text-[#251f54] placeholder-[#7871a3] shadow-inner transition">
                                <button type="button" @click="verPassword = !verPassword"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#6c649c] hover:text-[#251f54] transition"
                                        title="Mostrar u ocultar contraseña">
                                    <svg x-show="!verPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                    <svg x-show="verPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Recordar sesión + Olvidaste contraseña -->
                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <label for="remember_me" class="inline-flex items-center text-[#453e75] cursor-pointer">
                                    <input id="remember_me" type="checkbox" name="remember" class="rounded border-[#b9b0e0] text-[#4b3f96] focus:ring-[#4b3f96] mr-2">
                                    <span>Recordar mi sesión</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-[#453e75] hover:text-[#251f54] font-medium hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>

                            <!-- Botón Principal Ingresar -->
                            <div class="pt-2">
                                <button type="submit"
                                        class="w-full py-3.5 px-6 rounded-full bg-gradient-to-r from-[#594ab0] to-[#3c3082] hover:from-[#6656c2] hover:to-[#483a99] text-white text-sm font-bold shadow-lg shadow-indigo-950/25 flex items-center justify-center gap-2.5 transition transform active:scale-[0.99]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Ingresar</span>
                                </button>
                            </div>
                        </form>

                        <!-- Pie de Tarjeta -->
                        <div class="pt-3 border-t border-[#ded8f2] text-center">
                            <p class="text-[11px] text-[#575085]">
                                ¿No tienes una cuenta?
                                <a href="https://wa.me/56912345678?text=Hola%20Administraci%C3%B3n%20Cristo%20Rey,%20solicito%20habilitaci%C3%B3n%20de%20cuenta%20de%20personal." target="_blank" class="font-bold text-[#3b3086] hover:underline">
                                    Contacta con nosotros
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- 3. PIE DE PÁGINA CURVO OSCURO ÍNDIGO -->
        <footer class="relative w-full bg-[#1b1642] text-white py-4 px-6 sm:px-10 lg:px-16 z-20 mt-4">
            <!-- Onda curva decorativa sobre el borde superior del footer -->
            <svg class="absolute -top-6 left-0 w-full h-7 text-[#1b1642] pointer-events-none" viewBox="0 0 1440 48" preserveAspectRatio="none" fill="currentColor">
                <path d="M0,16 C360,48 1080,0 1440,28 L1440,48 L0,48 Z"/>
            </svg>

            <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full border border-white/35 flex items-center justify-center">
                        <span class="text-base">✝</span>
                    </div>
                    <div class="font-serif-display leading-tight">
                        <span class="block text-xs font-semibold">Funeraria</span>
                        <span class="block text-sm font-bold -mt-0.5">Cristo Rey</span>
                    </div>
                </div>

                <p class="text-[11px] text-indigo-300/80">
                    © {{ date('Y') }} Funeraria Cristo Rey — Acceso Exclusivo Personal Autorizado
                </p>
            </div>
        </footer>
    </div>

</body>
</html>