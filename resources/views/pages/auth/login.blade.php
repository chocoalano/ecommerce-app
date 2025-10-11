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
                    {{-- Alert Messages --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('auth.login.submit') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Input Email -->
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Alamat Email</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5"
                                   placeholder="email@anda.com"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Input Kata Sandi -->
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Kata Sandi</label>
                            <div class="relative">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5 pr-10"
                                       placeholder="••••••••"
                                       required>
                                <button type="button"
                                        onclick="togglePassword()"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg id="eye-open" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eye-closed" class="w-5 h-5 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                    id="login-button"
                                    class="w-full inline-flex items-center justify-center py-3 text-base font-semibold text-center text-white
                                          bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="login-text">Masuk ke EcomStore</span>
                                <svg id="login-spinner" class="hidden animate-spin ml-2 -mr-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>

                    <!-- JavaScript untuk fungsionalitas form -->
                    <script>
                        function togglePassword() {
                            const passwordInput = document.getElementById('password');
                            const eyeOpen = document.getElementById('eye-open');
                            const eyeClosed = document.getElementById('eye-closed');

                            if (passwordInput.type === 'password') {
                                passwordInput.type = 'text';
                                eyeOpen.classList.add('hidden');
                                eyeClosed.classList.remove('hidden');
                            } else {
                                passwordInput.type = 'password';
                                eyeOpen.classList.remove('hidden');
                                eyeClosed.classList.add('hidden');
                            }
                        }

                        // Loading state untuk form submit
                        document.querySelector('form').addEventListener('submit', function(e) {
                            const button = document.getElementById('login-button');
                            const text = document.getElementById('login-text');
                            const spinner = document.getElementById('login-spinner');

                            // Disable button dan show loading
                            button.disabled = true;
                            text.textContent = 'Memproses...';
                            spinner.classList.remove('hidden');
                        });

                        // Auto focus pada email jika ada error
                        @if($errors->has('email'))
                            document.getElementById('email').focus();
                        @elseif($errors->has('password'))
                            document.getElementById('password').focus();
                        @endif

                        // Real-time validation feedback
                        document.getElementById('email').addEventListener('blur', function() {
                            const email = this.value;
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                            if (email && !emailRegex.test(email)) {
                                this.classList.add('border-red-500');
                                this.classList.remove('border-gray-300');
                            } else {
                                this.classList.remove('border-red-500');
                                this.classList.add('border-gray-300');
                            }
                        });

                        document.getElementById('password').addEventListener('input', function() {
                            const password = this.value;

                            if (password.length > 0 && password.length < 6) {
                                this.classList.add('border-red-500');
                                this.classList.remove('border-gray-300');
                            } else {
                                this.classList.remove('border-red-500');
                                this.classList.add('border-gray-300');
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
