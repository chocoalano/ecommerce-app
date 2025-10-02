@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex">
    {{-- Kiri: Visual / Branding --}}
    <div class="hidden lg:block relative w-2/3 bg-zinc-100 border-r border-gray-200">
        <div class="absolute inset-0 bg-zinc-900/90 mix-blend-multiply"></div>

        <img class="w-full h-full object-cover"
             src="https://placehold.co/1200x800/27272a/ffffff?text=Buat+Akun+%7C+Diskon+Eksklusif"
             alt="Latar Belakang E-commerce Modern">

        <div class="absolute inset-0 flex flex-col justify-center items-center p-12 text-center">
            <h2 class="text-5xl font-extrabold tracking-tight text-white mb-4">
                Buat Akun & Nikmati Keuntungannya
            </h2>
            <p class="mt-4 text-lg text-zinc-300/90 max-w-md">
                Simpan alamat, kelola pesanan lebih cepat, dan dapatkan penawaran khusus member.
            </p>
            <flux:button as="a" href="#"
                class="mt-8 bg-white text-zinc-900 shadow-xl hover:bg-gray-100 border border-transparent">
                Lihat Katalog
            </flux:button>
        </div>
    </div>

    {{-- Kanan: Form Register --}}
    <div class="flex flex-1 flex-col justify-center items-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-zinc-50">
        <div class="mx-auto w-full max-w-sm lg:w-96 p-8 bg-white rounded-xl shadow-2xl shadow-zinc-200/50">
            {{-- Logo & Header --}}
            <div class="mb-8">
                <div class="flex items-center space-x-2 justify-center">
                    <svg class="h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-3xl font-extrabold text-zinc-900">EcomStore</span>
                </div>

                <h2 class="mt-6 text-2xl font-bold text-center text-zinc-900">
                    Daftar Akun Baru
                </h2>
                <p class="mt-2 text-center text-sm text-zinc-500">
                    Sudah punya akun?
                    <flux:link href="{{ route('auth.login') }}" class="font-semibold text-blue-600 hover:text-blue-700">
                        Masuk di sini
                    </flux:link>
                </p>
            </div>

            {{-- Alert Validasi --}}
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Register --}}
            <div class="mt-6">
                <form method="POST" action="{{ route('auth.register') }}" class="space-y-5" x-data="{ showPass:false, showPass2:false, pwd:'' }">
                    @csrf

                    {{-- Nama --}}
                    <flux:input
                        name="name"
                        label="Nama Lengkap"
                        type="text"
                        placeholder="Nama Anda"
                        value="{{ old('name') }}"
                        required
                    />
                    @error('name')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Email --}}
                    <flux:input
                        name="email"
                        label="Alamat Email"
                        type="email"
                        placeholder="email@anda.com"
                        value="{{ old('email') }}"
                        required
                    />
                    @error('email')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Password --}}
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-zinc-800">Kata Sandi</label>
                        <div class="relative">
                            <flux:input
                                id="password"
                                name="password"
                                :type="showPass ? 'text' : 'password'"
                                placeholder="••••••••"
                                required
                                x-model="pwd"
                                class="w-full pr-10"
                            />
                            <button type="button" @click="showPass=!showPass"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-zinc-400 hover:text-zinc-600"
                                    aria-label="Tampilkan/sembunyikan password">
                                <svg x-show="!showPass" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg x-show="showPass" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18M9.88 9.88A3 3 0 0 0 12 15c4.5 0 8-3 9-3-.33-.49-1.41-1.86-3.2-3.02M6.2 6.2C4.41 7.36 3.33 8.83 3 9c1 .67 4.5 3 9 3 .73 0 1.43-.08 2.08-.24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Indikator Kekuatan Password (opsional) --}}
                        <div class="mt-1">
                            <div class="h-1.5 w-full bg-zinc-200 rounded">
                                <div class="h-1.5 rounded transition-all"
                                     :class="[
                                        (pwd.length>=10 && /[A-Z]/.test(pwd) && /\d/.test(pwd) && /[^A-Za-z0-9]/.test(pwd)) ? 'bg-emerald-500 w-full' :
                                        (pwd.length>=8 && /[A-Z]/.test(pwd) && /\d/.test(pwd)) ? 'bg-yellow-500 w-2/3' :
                                        (pwd.length>=6) ? 'bg-orange-500 w-1/3' : 'w-0'
                                     ]">
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-zinc-500">
                                Gunakan min. 8 karakter, kombinasikan huruf besar, angka, & simbol.
                            </p>
                        </div>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Konfirmasi Password --}}
                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-sm font-medium text-zinc-800">Konfirmasi Kata Sandi</label>
                        <div class="relative">
                            <flux:input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPass2 ? 'text' : 'password'"
                                placeholder="••••••••"
                                required
                                class="w-full pr-10"
                            />
                            <button type="button" @click="showPass2=!showPass2"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-zinc-400 hover:text-zinc-600"
                                    aria-label="Tampilkan/sembunyikan konfirmasi password">
                                <svg x-show="!showPass2" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg x-show="showPass2" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18M9.88 9.88A3 3 0 0 0 12 15c4.5 0 8-3 9-3-.33-.49-1.41-1.86-3.2-3.02M6.2 6.2C4.41 7.36 3.33 8.83 3 9c1 .67 4.5 3 9 3 .73 0 1.43-.08 2.08-.24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Terms & Newsletter --}}
                    <div class="space-y-3">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <flux:checkbox name="terms" required />
                            <span class="text-sm text-zinc-700">
                                Saya menyetujui
                                <a href="{{ url('/terms') }}" class="text-blue-600 hover:text-blue-700 font-medium">Syarat & Ketentuan</a>
                                serta
                                <a href="{{ url('/privacy') }}" class="text-blue-600 hover:text-blue-700 font-medium">Kebijakan Privasi</a>.
                            </span>
                        </label>

                        <label class="flex items-start gap-2 cursor-pointer">
                            <flux:checkbox name="newsletter" />
                            <span class="text-sm text-zinc-700">
                                Kirimi saya promo & update produk via email.
                            </span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <div>
                        <flux:button type="submit" class="w-full font-semibold bg-blue-600 hover:bg-blue-700">
                            Buat Akun EcomStore
                        </flux:button>
                    </div>
                </form>

                {{-- Divider / Social Sign-up (opsional) --}}
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-zinc-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-zinc-500">
                                Atau daftar dengan
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3">
                        <flux:button variant="outline" :accent="false"
                            class="col-span-1 border-zinc-300 text-zinc-700 hover:bg-zinc-50">
                            {{-- Google --}}
                            <img class="w-5 h-5" src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
                        </flux:button>
                        <flux:button variant="outline" :accent="false"
                            class="col-span-1 border-zinc-300 text-zinc-700 hover:bg-zinc-50">
                            {{-- Facebook --}}
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H7.75v-3H10V9c0-2.2 1.35-3.47 3.36-3.47 1.05 0 1.95.08 2.22.12v2.54h-1.49c-1.12 0-1.34.53-1.34 1.31V12h2.82l-.45 3h-2.37v6.8c4.56-.93 8-4.96 8-9.8z"/>
                            </svg>
                        </flux:button>
                        <flux:button variant="outline" :accent="false"
                            class="col-span-1 border-zinc-300 text-zinc-700 hover:bg-zinc-50">
                            {{-- Twitter/X --}}
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21.5,11.2h-9.9v2.4h5.5c-0.3,1.6-1.6,2.7-3.6,2.7c-2.1,0-3.9-1.8-3.9-4s1.8-4,3.9-4c1.1,0,2,0.4,2.7,1.1l1.9-1.8
                                    c-1.2-1.1-2.9-1.8-4.6-1.8c-4.2,0-7.6,3.4-7.6,7.6c0,4.2,3.4,7.6,7.6,7.6c4.1,0,7.2-2.9,7.2-7.4C22,11.7,21.8,11.4,21.5,11.2z"/>
                            </svg>
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alpine untuk toggle password jika belum include di layout --}}
<script defer src="//unpkg.com/alpinejs"></script>
@endsection
