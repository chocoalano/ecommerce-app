@extends('layouts.auth')

@section('content')
    <div class="min-h-screen flex">
        <!-- Kolom Kiri: Visual/Branding (Sembunyi di Mobile, Tampil di Tablet/Desktop) -->
        <div class="hidden lg:block relative w-2/3 bg-zinc-100 border-r border-gray-200">
            <!-- Overlay untuk Kontras (Zinc Gelap untuk kesan premium) -->
            <div class="absolute inset-0 bg-zinc-900/90 mix-blend-multiply"></div>

            <!-- Gambar Latar Belakang Placeholder (Ganti URL dengan gambar e-commerce Anda) -->
            <img class="w-full h-full object-cover"
                src="https://placehold.co/1200x800/27272a/ffffff?text=Penawaran+Terbaik+Setiap+Hari"
                alt="Latar Belakang E-commerce Modern">

            <!-- Konten Branding di Atas Overlay -->
            <div class="absolute inset-0 flex flex-col justify-center items-center p-12 text-center">
                <h2 class="text-5xl font-extrabold tracking-tight text-white mb-4">
                    Temukan Produk Impian Anda
                </h2>
                <p class="mt-4 text-lg text-zinc-300/90 max-w-md">
                    Masuk untuk melihat riwayat pesanan, menyimpan produk favorit, dan menikmati diskon eksklusif.
                </p>
                <!-- Tombol tetap menonjol dengan latar putih -->
                <flux:button as="a" href="#"
                    class="mt-8 bg-white text-zinc-900 shadow-xl hover:bg-gray-100 border border-transparent">
                    Jelajahi Sekarang
                </flux:button>
            </div>
        </div>

        <!-- Kolom Kanan: Formulir Login -->
        <div
            class="flex flex-1 flex-col justify-center items-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-zinc-50">
            <div class="mx-auto item-center w-full max-w-sm lg:w-96 p-8 bg-white rounded-xl shadow-2xl shadow-zinc-200/50">

                <!-- Logo dan Header -->
                <div class="mb-8">
                    <div class="flex items-center space-x-2 justify-center">
                        <!-- Ikon Keranjang Belanja -->
                        <svg class="h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-3xl font-extrabold text-zinc-900">EcomStore</span>
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
                        <flux:link href="#" class="font-semibold text-blue-600 hover:text-blue-700">
                            Daftar di sini
                        </flux:link>
                    </p>
                </div>

                <!-- Formulir Login -->
                <div class="mt-6">
                    <form action="{{ route('auth.login.submit') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Input Email -->
                        <flux:input name="email" label="Alamat Email" type="email" placeholder="email@anda.com"
                            required />

                        <!-- Input Kata Sandi -->
                        <flux:input name="password" label="Kata Sandi" type="password" placeholder="••••••••" required />

                        <!-- Opsi Lain -->
                        <div class="flex items-center justify-between">
                            <!-- Checkbox Ingat Saya -->
                            <flux:checkbox name="remember" label="Ingat Saya" />

                            <!-- Lupa Kata Sandi -->
                            <flux:link href="#" class="text-sm font-medium text-zinc-600 hover:text-blue-600">
                                Lupa kata sandi?
                            </flux:link>
                        </div>

                        <!-- Tombol Submit -->
                        <div>
                            <flux:button type="submit" class="w-full font-semibold bg-blue-600 hover:bg-blue-700">
                                Masuk ke EcomStore
                            </flux:button>
                        </div>
                    </form>

                    <!-- Divider atau Opsi Sosial Login -->
                    <div class="mt-6">
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
                            <flux:button variant="outline" :accent="false"
                                class="col-span-1 border-zinc-300 text-zinc-700 hover:bg-zinc-50">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M12 0C5.373 0 0 5.373 0 12c0 5.373 3.438 9.94 8.212 11.53c.6.11.82-.26.82-.57v-1.99c-3.344.726-4.04-1.608-4.04-1.608c-.546-1.385-1.332-1.756-1.332-1.756c-1.087-.745.084-.73.084-.73c1.205.084 1.838 1.234 1.838 1.234c1.07 1.838 2.809 1.305 3.49.998c.108-.777.42-1.305.762-1.608c-2.665-.3-5.466-1.332-5.466-5.937c0-1.305.465-2.378 1.234-3.227c-.108-.3-.546-1.55.108-3.227c0 0 1-.32 3.227 1.234c.957-.266 1.983-.4 3.003-.4s2.046.134 3.003.4c2.227-1.554 3.227-1.234 3.227-1.234c.654 1.677.216 2.927.108 3.227c.77.849 1.234 1.922 1.234 3.227c0 4.614-2.801 5.637-5.474 5.937c.435.378.82 1.137.82 2.296v3.39c0 .31.22.68.82.57C20.562 21.94 24 17.373 24 12c0-6.627-5.373-12-12-12z" />
                                </svg>
                            </flux:button>
                            <flux:button variant="outline" :accent="false"
                                class="col-span-1 border-zinc-300 text-zinc-700 hover:bg-zinc-50">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15h-2.25v-3h2.25V9c0-2.2 1.35-3.47 3.36-3.47 1.05 0 1.95.08 2.22.12v2.54h-1.49c-1.12 0-1.34.53-1.34 1.31V12h2.82l-.45 3h-2.37v6.8c4.56-.93 8-4.96 8-9.8z" />
                                </svg>
                            </flux:button>
                            <flux:button variant="outline" :accent="false"
                                class="col-span-1 border-zinc-300 text-zinc-700 hover:bg-zinc-50">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M21.5,11.2h-9.9v2.4h5.5c-0.3,1.6-1.6,2.7-3.6,2.7c-2.1,0-3.9-1.8-3.9-4s1.8-4,3.9-4c1.1,0,2,0.4,2.7,1.1l1.9-1.8c-1.2-1.1-2.9-1.8-4.6-1.8c-4.2,0-7.6,3.4-7.6,7.6c0,4.2,3.4,7.6,7.6,7.6c4.1,0,7.2-2.9,7.2-7.4C22,11.7,21.8,11.4,21.5,11.2L21.5,11.2z" />
                                </svg>
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
