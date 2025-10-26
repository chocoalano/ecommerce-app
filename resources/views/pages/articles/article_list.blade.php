<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @forelse($articles as $article)
        <a href="{{ route('article.show', $article->slug) }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
            {{-- {{ dd($article->featured_image($article->id)) }} --}}
            <img src="{{ $article->featured_image($article->id) ? asset('storage/'.$article->featured_image($article->id)) : asset('images/no-image.png') }}" alt="{{ $article->title }}" class="w-full h-40 object-cover">
            <div class="p-4">
                <div class="font-bold text-lg mb-2">{{ $article->title }}</div>
                <div class="text-sm text-gray-500 mb-2">{{ $article->published_at ? $article->published_at->format('d M Y') : '' }}</div>
                <div class="text-sm text-gray-700 line-clamp-3">{{ Str::limit(strip_tags($article->featured_content()), 120) }}</div>
            </div>
        </a>
    @empty
        <div class="col-span-3 text-center text-gray-500 py-12">Tidak ada artikel ditemukan.</div>
    @endforelse
</div>
<div class="mt-8">
    {{ $articles->links() }}
</div>
