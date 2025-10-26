@extends('layouts.app')

@php
    use App\Helpers\ContentRenderer;
@endphp

@section('title', $article->title)
@section('meta-description', Str::limit(strip_tags($article->content), 150))

@section('meta')
    <meta name="keywords" content="{{ $article->content->tags }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $article->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($article->content), 150) }}">
@endsection

@section('content')
    <article class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- --- HEADER ARTIKEL --- --}}
    <header class="mb-8 lg:mb-12 not-format">

        {{-- Breadcrumb Sederhana --}}
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse text-sm font-medium text-gray-700">
                <li><a href="/" class="hover:text-purple-600">Beranda</a></li>
                <li><span class="text-gray-400 mx-1">/</span></li>
                <li><a href="/blog" class="hover:text-purple-600">Blog</a></li>
                <li><span class="text-gray-400 mx-1">/</span></li>
                <li aria-current="page"><span class="text-gray-500">{{ Str::limit($article->title, 30) }}</span></li>
            </ol>
        </nav>

        {{-- Tags --}}
        @if (isset($article->content->tags) && is_array($article->content->tags))
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($article->content->tags as $tag)
                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ $tag }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Judul Utama --}}
        <h1 class="mb-6 text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight text-gray-900">
            {{ $article->title }}
        </h1>

        {{-- Meta Info (Author & Date) --}}
        <address class="flex items-center mb-6 not-italic">
            <div class="inline-flex items-center mr-3 text-base text-gray-900">
                <img class="mr-4 w-12 h-12 rounded-full ring-2 ring-purple-400"
                    src="https://ui-avatars.com/api/?name={{ urlencode($article->author->name ?? 'Admin') }}&background=6b21a8&color=fff"
                    alt="{{ $article->author->name ?? 'Admin' }}">
                <div>
                    <p rel="author" class="text-lg font-bold text-gray-900">{{ $article->author->name ?? 'Tim Redaksi' }}</p>
                    <p class="text-sm text-gray-500">
                        Diterbitkan pada
                        <time datetime="{{ $article->published_at?->format('Y-m-d') }}" title="{{ $article->published_at?->format('d M Y H:i') }}">
                            {{ $article->published_at?->diffForHumans() }}
                        </time>
                    </p>
                </div>
            </div>
        </address>

        {{-- Featured Image (Ditempatkan sebelum konten untuk menarik perhatian) --}}
        @if ($article->featured_image($article->id))
            <figure class="mt-8 mb-6">
                <img class="w-full h-auto max-h-[500px] object-cover rounded-xl border-4 border-white"
                    src="{{ asset('storage/' . $article->featured_image($article->id)) }}" alt="{{ $article->title }}"
                    onerror="this.onerror=null;this.src='https://placehold.co/1200x500/6b21a8/ffffff?text=Featured+Image+Missing'">
            </figure>
        @endif

    </header>

    {{-- --- KONTEN UTAMA ARTIKEL --- --}}
    <div class="prose max-w-none prose-purple">
        {{-- Menggantikan render_content_blocks dengan logika looping Blade --}}
        @if (is_array($article->content->content ?? null))
            @foreach ($article->content->content as $block)
                @switch($block['type'] ?? null)
                    @case('heading')
                        @php
                            $level = $block['data']['level'] ?? 'h2';
                            $content = $block['data']['content'] ?? '';
                        @endphp
                        @if (!empty($content))
                        {{-- Menggunakan kelas dari helper sebelumnya untuk konsistensi desain --}}
                        <{{ $level }} class="text-gray-900 font-bold mb-4 mt-8 text-xl md:text-2xl">{{ $content }}</{{ $level }}>
                        @endif
                        @break
                    @case('paragraph')
                        @if(isset($block['data']['content']))
                        <p class="text-gray-700 mb-6 leading-relaxed">{{ $block['data']['content'] }}</p>
                        @endif
                        @break
                    @case('image')
                        @if(isset($block['data']['url']))
                        <figure class="my-8">
                            <img src="{{ asset('storage/' . $block['data']['url']) }}"
                                alt="{{ $block['data']['alt'] ?? 'Gambar Artikel' }}"
                                class="w-full h-auto rounded-lg object-cover"
                                onerror="this.onerror=null;this.src='https://placehold.co/1200x600/6b21a8/ffffff?text=Gambar+Tidak+Tersedia';" />
                            @if(isset($block['data']['alt']))
                            <figcaption class="mt-2 text-sm text-center text-gray-500">{{ $block['data']['alt'] }}</figcaption>
                            @endif
                        </figure>
                        @endif
                        @break
                    @default
                        {{-- Abaikan blok yang tidak dikenali --}}
                @endswitch
            @endforeach
        @else
            <p class="text-gray-500">Konten belum tersedia.</p>
        @endif
    </div>
</article>

{{-- --- REKOMENDASI ARTIKEL (LEBAR PENUH) --- --}}
<section class="mt-16 pt-12 border-t border-gray-200 mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <h2 class="mb-8 text-3xl font-extrabold tracking-tight text-gray-900">Lebih Banyak untuk Dibaca</h2>

    {{-- Menggunakan $recommendedArticles (common convention) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        @forelse ($reccomendationArticles as $recommended)
            <div
                class="bg-white border border-gray-200 rounded-lg transition duration-300 hover:shadow-2xl hover:translate-y-[-2px]">
                @if ($recommended->featured_image($recommended->id))
                    <a href="{{ route('article.show', $recommended->slug) }}">
                        <img class="rounded-t-lg h-40 w-full object-cover"
                            src="{{ asset('storage/' . $recommended->featured_image($recommended->id)) }}"
                            alt="{{ $recommended->title }}"
                            onerror="this.onerror=null;this.src='https://placehold.co/400x160/6b21a8/ffffff?text=No+Image'" />
                    </a>
                @endif
                <div class="p-5">
                    <h5 class="mb-2 text-lg font-bold tracking-tight text-gray-900">
                        <a href="{{ route('article.show', $recommended->slug) }}"
                            class="hover:text-purple-600 line-clamp-2">
                            {{ $recommended->title }}
                        </a>
                    </h5>
                    <p class="mb-3 font-normal text-gray-700 text-sm line-clamp-3">
                        {{ Str::limit(strip_tags($recommended->featured_content() ?? $recommended->title), 100) }}
                    </p>
                    <a href="{{ route('article.show', $recommended->slug) }}"
                        class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-800 transition duration-150">
                        Baca Selengkapnya
                        <svg class="rtl:rotate-180 w-3 h-3 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-4">Tidak ada artikel rekomendasi saat ini.</p>
        @endforelse
    </div>
</section>
@endsection
