@extends('layouts.auth')

@section('content')
    <div class="min-h-screen flex bg-gray-50">
        {{-- Kiri: Visual / Branding (Sembunyi di Mobile, Tampil di Tablet/Desktop) --}}
        <div class="hidden lg:block relative w-2/3 bg-zinc-900">
            <div class="absolute inset-0 bg-zinc-900/90 mix-blend-multiply"></div>

            <img class="w-full h-full object-cover opacity-30" src="{{ asset('svg/Ecommerce web page-rafiki.svg') }}"
                alt="Latar Belakang E-commerce Modern">

            <div class="absolute inset-0 flex flex-col justify-center items-center p-12 text-center">
                <h2 class="text-5xl font-extrabold tracking-tight text-white mb-4">
                    Buat Akun & Nikmati Keuntungannya
                </h2>
                <p class="mt-4 text-lg text-zinc-300 max-w-md">
                    Simpan alamat, kelola pesanan lebih cepat, dan dapatkan penawaran khusus member.
                </p>
                {{-- Flux Button diganti <a> Flowbite Primary --}}
                    <a href="#"
                        class="mt-8 inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-center text-zinc-900
                               bg-white rounded-full shadow-xl hover:bg-gray-100 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                        Lihat Katalog
                    </a>
            </div>
        </div>

        {{-- Kanan: Form Register --}}
        <div
            class="flex flex-1 flex-col justify-center items-center px-4 py-12 sm:px-6 lg:flex-none lg:px-16 xl:px-20 bg-gray-50">
            <div class="mx-auto w-full max-w-md lg:w-96 p-8 bg-white rounded-xl">
                {{-- Logo & Header --}}
                <div class="mb-8">
                    <div class="flex items-center space-x-2 justify-center">
                        {{-- Ikon Keranjang Belanja SVG --}}
                        <img src="{{ asset('images/logo-puranura-id.png') }}" alt="{{ config('app.name') }}"
                            class="h-30 w-auto">
                    </div>

                    <h2 class="mt-6 text-2xl font-bold text-center text-zinc-900">
                        Daftar Akun Baru
                    </h2>
                    <p class="mt-2 text-center text-sm text-zinc-500">
                        Sudah punya akun?
                        {{-- Flux Link diganti <a> Flowbite Link --}}
                            <a href="{{ route('auth.login') }}"
                                class="font-semibold text-zinc-600 hover:text-zinc-700 hover:underline">
                                Masuk di sini
                            </a>
                    </p>
                </div>

                {{-- Alert Validasi (Flowbite Alert Style) --}}
                @if ($errors->any())
                    <div id="alert-2" class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.444 14.5a1.5 1.5 0 1 1 2.808-.574L12 13.5a1 1 0 0 0-2 0l-.556 1.426ZM10 10a1 1 0 0 0 1-1V5a1 1 0 1 0-2 0v4a1 1 0 0 0 1 1Z" />
                        </svg>
                        <span class="sr-only">Kesalahan</span>
                        <div class="font-medium">
                            Terdapat kesalahan pada formulir Anda:
                            <ul class="mt-1.5 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Form Register --}}
                <div class="mt-6">
                    <form method="POST" action="{{ route('auth.register.submit') }}" class="space-y-5"
                        x-data="{ showPass:false, showPass2:false, pwd:'' }">
                        @csrf

                        {{-- Nama --}}
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                            <input type="text" id="name" name="name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5"
                                placeholder="Nama Anda" value="{{ old('name') }}" required autocomplete="name" aria-describedby="name-error">
                            @error('name')
                                <p id="name-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Alamat Email</label>
                            <input type="email" id="email" name="email"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5"
                                placeholder="email@anda.com" value="{{ old('email') }}" required autocomplete="email" aria-describedby="email-error">
                            @error('email')
                                <p id="email-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-gray-900">Kata Sandi</label>
                            <div class="relative">
                                {{-- Flux Input diganti <input> Flowbite Style --}}
                                <input id="password" name="password" :type="showPass ? 'text' : 'password'"
                                    placeholder="••••••••" required x-model="pwd"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5 pr-10"
                                    autocomplete="new-password" aria-describedby="password-error" minlength="8" />
                                @error('password')
                                    <p id="password-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                {{-- Tombol Toggle Password --}}
                                <button type="button" @click="showPass=!showPass"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-zinc-400 hover:text-zinc-600"
                                    aria-label="Tampilkan/sembunyikan password">
                                    {{-- SVG Mata Terbuka --}}
                                    <svg x-show="!showPass" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.437 0 .645C20.577 16.49 16.639 19.5 12 19.5c-4.639 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    {{-- SVG Mata Tertutup --}}
                                    <svg x-show="showPass" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.41a3.003 3.003 0 0 0 3.2 3.2h.75A1.5 1.5 0 0 0 10.5 9.87v-1.5a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5a1.5 1.5 0 0 0 1.5 1.5h.75a3.003 3.003 0 0 0 3.2-3.2m-10.27 10.27-10.87-10.88a1.5 1.5 0 0 0-2.12-2.12L1.8 1.8a1.5 1.5 0 0 0-2.12 2.12L1.8 1.8 22.2 22.2a1.5 1.5 0 0 0 2.12 2.12L22.2 22.2ZM12 18.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Indikator Kekuatan Password --}}
                            <div class="mt-1">
                                <div class="h-1.5 w-full bg-zinc-200 rounded">
                                    <div class="h-1.5 rounded transition-all" :class="[
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

                        {{-- Konfirmasi Password --}}
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-900">Konfirmasi
                                Kata Sandi</label>
                            <div class="relative">
                                {{-- Flux Input diganti <input> Flowbite Style --}}
                                <input id="password_confirmation" name="password_confirmation"
                                    :type="showPass2 ? 'text' : 'password'" placeholder="••••••••" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5 pr-10"
                                    autocomplete="new-password" aria-describedby="password_confirmation-error" />
                                @error('password_confirmation')
                                    <p id="password_confirmation-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                {{-- Tombol Toggle Password --}}
                                <button type="button" @click="showPass2=!showPass2"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-zinc-400 hover:text-zinc-600"
                                    aria-label="Tampilkan/sembunyikan konfirmasi password">
                                    {{-- SVG Mata Terbuka --}}
                                    <svg x-show="!showPass2" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.437 0 .645C20.577 16.49 16.639 19.5 12 19.5c-4.639 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    {{-- SVG Mata Tertutup --}}
                                    <svg x-show="showPass2" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.41a3.003 3.003 0 0 0 3.2-3.2h.75A1.5 1.5 0 0 0 10.5 9.87v-1.5a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5a1.5 1.5 0 0 0 1.5 1.5h.75a3.003 3.003 0 0 0 3.2-3.2m-10.27 10.27-10.87-10.88a1.5 1.5 0 0 0-2.12-2.12L1.8 1.8a1.5 1.5 0 0 0-2.12 2.12L1.8 1.8 22.2 22.2a1.5 1.5 0 0 0 2.12 2.12L22.2 22.2ZM12 18.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Terms & Newsletter (Flowbite Checkbox Style) --}}
                        <div class="space-y-3 pt-2">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <div class="flex items-center h-5">
                                    {{-- Flux Checkbox diganti <input> Flowbite Style --}}
                                    <input type="checkbox" id="terms" name="terms" required
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-zinc-300 text-zinc-600" {{ old('terms') ? 'checked' : '' }}>
                                </div>
                                <span class="text-sm text-zinc-700">
                                    Saya menyetujui
                                    <a href="{{ url('/terms') }}"
                                        class="text-zinc-600 hover:text-zinc-700 font-medium hover:underline">Syarat &
                                        Ketentuan</a>
                                    serta
                                    <a href="{{ url('/privacy') }}"
                                        class="text-zinc-600 hover:text-zinc-700 font-medium hover:underline">Kebijakan
                                        Privasi</a>.
                                </span>
                            </label>

                            <label class="flex items-start gap-2 cursor-pointer">
                                <div class="flex items-center h-5">
                                    {{-- Flux Checkbox diganti <input> Flowbite Style --}}
                                    <input type="checkbox" id="newsletter" name="newsletter"
                                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-zinc-300 text-zinc-600" {{ old('newsletter') ? 'checked' : '' }}>
                                </div>
                                <span class="text-sm text-zinc-700">
                                    Kirimi saya promo & update produk via email.
                                </span>
                            </label>
                        </div>

                        <div>
                            <label for="referral_code" class="block mb-2 text-sm font-medium text-gray-900">Kode Referal (opsional)</label>
                            <input type="text" id="referral_code" name="referral_code"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5"
                                placeholder="Kode Referal" value="{{ old('referral_code') }}" autocomplete="off">
                            @error('referral_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div>
                            {{-- Flux Button diganti <button> Flowbite Primary --}}
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center py-3 text-base font-semibold text-center text-white
                                          bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                                    Buat Akun EcomStore
                                </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Catatan: Alpine harus di-include di layout induk (layouts.auth) agar fungsi x-data bekerja. --}}
@endsection
