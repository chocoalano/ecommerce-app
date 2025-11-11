@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
<h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b pb-2">Jaringan Sponsor & Referensi</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Referral Link Card -->
        <div class="lg:col-span-2 bg-white  rounded-xl p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-zinc-700 mb-3 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.885l2.495-2.495M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 10-5.656-5.656l-1.1 1.1"></path></svg>
                Tautan Referensi Anda
            </h2>
            <p class="text-sm text-gray-600 mb-2">Gunakan tautan ini untuk mengundang anggota baru ke jaringan Anda.</p>

            <div class="flex flex-col sm:flex-row gap-2">
                <input
                    type="text"
                    id="referral-link"
                    readonly
                    value="{{ route('auth.register', ['ref' => $customer->code]) }}"
                    class="flex-grow p-3 border border-gray-300 rounded-lg bg-gray-50 text-sm font-medium focus:border-zinc-500"
                >
                <button
                    onclick="copyLink()"
                    class="bg-zinc-600 hover:bg-zinc-700 text-white font-bold py-3 px-4 rounded-lg text-sm transition duration-150 shadow-md flex-shrink-0"
                >
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m-3 5v3m0 0l-2-2m2 2l2-2"></path></svg>
                    Salin
                </button>
            </div>
        </div>

        <!-- Upline / Sponsor Info Card -->
        <div class="bg-white rounded-xl p-6 border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H4v-2a3 3 0 015-2.454m0 0a3 3 0 013-3m-6 3h6m-6 0a3 3 0 00-3 3v2M9 20h9"></path></svg>
                Sponsor Anda
            </h2>
            @if ($sponsor)
                <p class="text-gray-800 font-medium">{{ $sponsor->name }}</p>
                <p class="text-sm text-gray-500">{{ $sponsor->email }}</p>
                <p class="text-xs text-gray-400 mt-2">Kode: {{ $sponsor->code }}</p>
            @else
                <p class="text-sm text-red-500">Anda belum memiliki sponsor.</p>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Referensi</p>
                    <p class="text-3xl font-bold mt-2">{{ $totalReferrals }}</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H4v-2a3 3 0 015-2.454m0 0a3 3 0 013-3m-6 3h6m-6 0a3 3 0 00-3 3v2M9 20h9"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Member Aktif</p>
                    <p class="text-3xl font-bold mt-2">{{ $activeReferrals }}</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Member Tidak Aktif</p>
                    <p class="text-3xl font-bold mt-2">{{ $inactiveReferrals }}</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Downline/Direct Referrals Table -->
    <div class="bg-white shadow-xl rounded-xl p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Referensi Langsung</h2>

        @if($directReferrals->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H4v-2a3 3 0 015-2.454m0 0a3 3 0 013-3m-6 3h6m-6 0a3 3 0 00-3 3v2M9 20h9"></path></svg>
                <p class="text-gray-500 text-lg">Anda belum memiliki referensi langsung.</p>
                <p class="text-gray-400 text-sm mt-2">Bagikan link referensi Anda untuk mengundang member baru!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Aktif</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($directReferrals as $referral)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $referral->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $referral->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $referral->code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($referral->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $referral->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $referral->updated_at->diffForHumans() }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('auth.network.binary') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-800 transition duration-150">
                Lihat Pohon Jaringan Lengkap &rarr;
            </a>
        </div>
    </div>

</div>

<script>
    function copyLink() {
        const linkInput = document.getElementById('referral-link');

        // Pilih teks di input
        linkInput.select();
        linkInput.setSelectionRange(0, 99999); // Untuk perangkat mobile

        try {
            // Salin teks ke clipboard
            document.execCommand('copy');

            // Beri umpan balik ke pengguna
            const button = linkInput.nextElementSibling;
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!';

            // Kembalikan tombol ke teks asli setelah 2 detik
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        } catch (err) {
            console.error('Gagal menyalin:', err);
        }
    }
</script>


@endsection
