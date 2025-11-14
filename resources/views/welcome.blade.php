<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Toko Kami Segera Hadir! - Coming Soon</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */
                /* ... Biarkan blok <style> yang panjang di sini jika Anda tidak menggunakan Vite/Mix ... */
            </style>
        @endif
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] flex p-4 sm:p-6 lg:p-8 items-center justify-center min-h-screen">
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full flex-col lg:flex-row lg:max-w-5xl shadow-xl rounded-lg overflow-hidden">

                <div class="lg:w-1/2 bg-[#f5f5f5] flex items-center justify-center p-6 sm:p-8 rounded-t-lg lg:rounded-r-none lg:rounded-l-lg">

                    <img src="{{ asset('images/logo-puranura-id.png') }}"
                         alt="Ilustrasi E-commerce Segera Hadir"
                         class="max-w-full h-auto rounded-md" />
                </div>

                <div class="lg:w-1/2 flex flex-col justify-center p-6 sm:p-8 lg:p-12 bg-white rounded-b-lg lg:rounded-l-none lg:rounded-r-lg">

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-4 text-black leading-tight">
                        **Segera Hadir!** 🚀
                    </h1>

                    <h2 class="text-lg sm:text-xl font-semibold mb-4 text-[#1b1b18] dark:text-white">
                        Toko Online Baru Kami Siap Memanjakan Anda.
                    </h2>

                    <p class="text-sm sm:text-base leading-relaxed mb-6 text-[#706f6c] dark:text-[#A1A09A]">
                        Kami sedang bekerja keras untuk membawa koleksi produk terbaik kami kepada Anda.
                        Nantikan pengalaman belanja yang lebih mudah, cepat, dan menyenangkan!
                        <br><br>
                        Estimasi peluncuran: **Secepatnya**.
                    </p>

                </div>
            </main>
        </div>
    </body>
</html>
