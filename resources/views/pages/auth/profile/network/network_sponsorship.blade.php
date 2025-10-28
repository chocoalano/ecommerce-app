@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
<h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b pb-2">Jaringan Sponsor & Referensi</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Referral Link Card -->
        <div class="lg:col-span-2 bg-white shadow-xl rounded-xl p-6 border border-blue-100">
            <h2 class="text-xl font-semibold text-blue-700 mb-3 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.885l2.495-2.495M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 10-5.656-5.656l-1.1 1.1"></path></svg>
                Tautan Referensi Anda
            </h2>
            <p class="text-sm text-gray-600 mb-2">Gunakan tautan ini untuk mengundang anggota baru ke jaringan Anda.</p>

            <div class="flex flex-col sm:flex-row gap-2">
                <input
                    type="text"
                    id="referral-link"
                    readonly
                    class="flex-grow p-3 border border-gray-300 rounded-lg bg-gray-50 text-sm font-medium focus:border-blue-500"
                >
                <button
                    onclick="copyLink()"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-sm transition duration-150 shadow-md flex-shrink-0"
                >
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m-3 5v3m0 0l-2-2m2 2l2-2"></path></svg>
                    Salin
                </button>
            </div>
        </div>

        <!-- Upline / Sponsor Info Card -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M9 20H4v-2a3 3 0 015-2.454m0 0a3 3 0 013-3m-6 3h6m-6 0a3 3 0 00-3 3v2M9 20h9"></path></svg>
                Sponsor Anda
            </h2>
            @if (auth()->user()->sponsor)
                <p class="text-gray-800 font-medium">{{ auth()->user()->sponsor->name }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->sponsor->email }}</p>
            @else
                <p class="text-sm text-red-500">Anda belum memiliki sponsor.</p>
            @endif
        </div>
    </div>

    <!-- Network Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white shadow rounded-xl p-5 text-center border-t-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">Total Downline</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">
                {{ $networkStats['total_downline'] ?? '0' }}
            </p>
        </div>
        <div class="bg-white shadow rounded-xl p-5 text-center border-t-4 border-yellow-500">
            <p class="text-sm font-medium text-gray-500">Referensi Langsung</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">
                {{ $networkStats['direct_referrals'] ?? '0' }}
            </p>
        </div>
        <div class="bg-white shadow rounded-xl p-5 text-center border-t-4 border-indigo-500">
            <p class="text-sm font-medium text-gray-500">Komisi Tertunda</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">
                Rp {{ number_format($networkStats['pending_commission'] ?? 0, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Downline/Direct Referrals Table -->
    <div class="bg-white shadow-xl rounded-xl p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Referensi Langsung</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung Sejak</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas Terakhir</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Loop Referrals Here -->
                    {{-- @foreach($directReferrals as $referral) --}}
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            Anggota 1 ({{ '@username1' }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            12 Mei 2024
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                            Level 1
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Aktif 2 hari lalu
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            Anggota 2 ({{ '@username2' }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            1 Juni 2024
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                            Level 1
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Aktif hari ini
                        </td>
                    </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="{{ route('auth.network.binary') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition duration-150">
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
