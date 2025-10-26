<div>
    <div class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <input type="text" wire:model.debounce.500ms="search" placeholder="Cari artikel..." class="border rounded px-3 py-2 w-full md:w-1/3">
        <select wire:model="category" class="border rounded px-3 py-2 w-full md:w-1/4">
            <option value="">Semua Kategori</option>
            {{-- Contoh kategori, ganti dengan @foreach jika ada relasi kategori --}}
            <option value="news">News</option>
            <option value="tips">Tips</option>
        </select>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($articles as $article)
            <a href="{{ url('/blog/'.$article->slug) }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <img src="{{ $article->featured_image ? asset('storage/'.$article->featured_image) : asset('images/no-image.png') }}" alt="{{ $article->title }}" class="w-full h-40 object-cover">
                <div class="p-4">
                    <div class="font-bold text-lg mb-2">{{ $article->title }}</div>
                    <div class="text-sm text-gray-500 mb-2">{{ $article->published_at ? $article->published_at->format('d M Y') : '' }}</div>
                    <div class="text-sm text-gray-700 line-clamp-3">{{ Str::limit(strip_tags($article->content), 120) }}</div>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center text-gray-500 py-12">Tidak ada artikel ditemukan.</div>
        @endforelse
    </div>
    <div class="mt-8">
        {{ $articles->links() }}
    </div>
</div>
