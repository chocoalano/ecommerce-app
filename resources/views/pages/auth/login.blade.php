@extends('layouts.auth')

@section('content')
    <div class="min-h-screen flex bg-gray-50">
        <!-- Kolom Kiri: Visual/Branding (Sembunyi di Mobile, Tampil di Tablet/Desktop) -->
        <div class="hidden lg:block relative w-2/3 bg-zinc-900">
            <!-- Gambar Latar Belakang Placeholder -->
            <img class="w-full h-full object-cover opacity-30"
                src="{{ asset('svg/Ecommerce web page-rafiki.svg') }}"
                alt="Latar Belakang E-commerce Modern">

            <!-- Konten Branding di Atas Overlay -->
            <div class="absolute inset-0 flex flex-col justify-center items-center p-12 text-center">
                <h2 class="text-5xl font-extrabold tracking-tight text-white mb-4">
                    Temukan Produk Impian Anda
                </h2>
                <p class="mt-4 text-lg text-zinc-300 max-w-md">
                    Masuk untuk melihat riwayat pesanan, menyimpan produk favorit, dan menikmati diskon eksklusif.
                </p>
                <!-- Tombol tetap menonjol dengan latar putih (Flowbite Style) -->
                <a href="#"
                    class="mt-8 inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-center text-zinc-900
                           bg-white rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                    Jelajahi Sekarang
                </a>
            </div>
        </div>

        <!-- Kolom Kanan: Formulir Login -->
        <div
            class="flex flex-1 flex-col justify-center items-center px-4 py-12 sm:px-6 lg:flex-none lg:px-16 xl:px-20 bg-gray-50">
            <div class="mx-auto item-center w-full max-w-md lg:w-96 p-8 bg-white rounded-xl border border-gray-100">

                <!-- Logo dan Header -->
                <div class="mb-8">
                    <div class="flex items-center space-x-2 justify-center">
                        <!-- Ikon Keranjang Belanja SVG -->
                        <img src="{{ asset('images/logo-puranura-id.png') }}" alt="{{ config('app.name') }}" class="h-30 w-auto">
                    </div>

                    <h2 class="mt-6 text-2xl font-bold text-center text-zinc-900">
                        Selamat Datang Kembali!
                    </h2>
                    <!-- Teks Informatif/Instruksi -->
                    <p class="mt-2 text-center text-sm text-zinc-500">
                        Akses *dashboard* Anda atau buat akun baru dalam hitungan detik.
                    </p>
                    <p class="mt-4 text-center text-sm text-zinc-600">
                        Belum punya akun?
                        {{-- Flux Link diganti <a> Flowbite Link --}}
                        <a href="{{ route('auth.register') }}" class="font-semibold text-zinc-600 hover:text-zinc-700 hover:underline">
                            Daftar di sini
                        </a>
                    </p>
                </div>

                <!-- Formulir Login -->
                <div class="mt-6">
                    <form action="{{ route('auth.login.submit') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Input Email -->
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Alamat Email</label>
                            <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" placeholder="email@anda.com" required>
                        </div>

                        <!-- Input Kata Sandi -->
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Kata Sandi</label>
                            <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" placeholder="••••••••" required>
                        </div>

                        <!-- Opsi Lain -->
                        <div class="flex items-center justify-between">
                            <!-- Checkbox Ingat Saya (Flowbite Checkbox) -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="remember" name="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-zinc-300 text-zinc-600">
                                </div>
                                <label for="remember" class="ml-3 text-sm font-medium text-gray-900">Ingat Saya</label>
                            </div>

                            <!-- Lupa Kata Sandi (Flowbite Link) -->
                            <a href="#" class="text-sm font-medium text-zinc-600 hover:text-zinc-600 hover:underline">
                                Lupa kata sandi?
                            </a>
                        </div>

                        <!-- Tombol Submit (Flowbite Primary Button) -->
                        <div>
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center py-3 text-base font-semibold text-center text-white
                                      bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                                Masuk ke EcomStore
                            </button>
                        </div>
                    </form>

                    <!-- Divider atau Opsi Sosial Login -->
                    <div class="mt-8">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-zinc-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-zinc-500">
                                    Atau lanjutkan dengan
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            {{-- Tombol Sosial (Flowbite Outline Button) --}}
                            <button type="button"
                                class="col-span-1 w-full inline-flex items-center justify-center py-3 text-base font-medium text-gray-700
                                       border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition duration-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M12 0C5.373 0 0 5.373 0 12c0 5.373 3.438 9.94 8.212 11.53c.6.11.82-.26.82-.57v-1.99c-3.344.726-4.04-1.608-4.04-1.608c-.546-1.385-1.332-1.756-1.332-1.756c-1.087-.745.084-.73.084-.73c1.205.084 1.838 1.234 1.838 1.234c1.07 1.838 2.809 1.305 3.49.998c.108-.777.42-1.305.762-1.608c-2.665-.3-5.466-1.332-5.466-5.937c0-1.305.465-2.378 1.234-3.227c-.108-.3-.546-1.55.108-3.227c0 0 1-.32 3.227 1.234c.957-.266 1.983-.4 3.003-.4s2.046.134 3.003.4c2.227-1.554 3.227-1.234 3.227-1.234c.654 1.677.216 2.927.108 3.227c.77.849 1.234 1.922 1.234 3.227c0 4.614-2.801 5.637-5.474 5.937c.435.378.82 1.137.82 2.296v3.39c0 .31.22.68.82.57C20.562 21.94 24 17.373 24 12c0-6.627-5.373-12-12-12z" />
                                </svg>
                            </button>

                            <button type="button"
                                class="col-span-1 w-full inline-flex items-center justify-center py-3 text-base font-medium text-gray-700
                                       border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition duration-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.25v-3h2.25V9c0-2.2 1.35-3.47 3.36-3.47 1.05 0 1.95.08 2.22.12v2.54h-1.49c-1.12 0-1.34.53-1.34 1.31V12h2.82l-.45 3h-2.37v6.8c4.56-.93 8-4.96 8-9.8z" />
                                </svg>
                            </button>
                            
                            <button type="button"
                                class="col-span-1 w-full inline-flex items-center justify-center py-3 text-base font-medium text-gray-700
                                       border border-gray-300 rounded-full hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition duration-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M21.5,11.2h-9.9v2.4h5.5c-0.3,1.6-1.6,2.7-3.6,2.7c-2.1,0-3.9-1.8-3.9-4s1.8-4,3.9-4c1.1,0,2,0.4,2.7,1.1l1.9-1.8c-1.2-1.1-2.9-1.8-4.6-1.8c-4.2,0-7.6,3.4-7.6,7.6c0,4.2,3.4,7.6,7.6,7.6c4.1,0,7.2-2.9,7.2-7.4C22,11.7,21.8,11.4,21.5,11.2L21.5,11.2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection