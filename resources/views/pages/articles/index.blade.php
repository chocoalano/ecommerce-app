@extends('layouts.app')

@section('title', 'Daftar Artikel')
@section('meta-description', 'Temukan berbagai artikel menarik seputar topik terkini, tips, dan informasi bermanfaat lainnya di blog kami. Baca sekarang untuk memperluas wawasan Anda!')

@section('meta')
    <meta name="keywords" content="artikel, blog, informasi, tips, {{ config('app.name') }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $articles->count() > 0 ? 'Daftar Artikel' : 'Tidak Ada Artikel' }}">
    <meta property="og:description" content="Temukan berbagai artikel menarik seputar topik terkini, tips, dan informasi bermanfaat lainnya di blog kami. Baca sekarang untuk memperluas wawasan Anda!">
@endsection

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Blog & Artikel</h1>
    <form method="GET" action="{{ route('article.index') }}" class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <input type="text" name="title" value="{{ request('title') }}" placeholder="Cari judul artikel..." class="border border-gray-200 rounded px-3 py-2 w-full md:w-1/3">
        <button type="submit" class="px-4 py-2 bg-zinc-950 text-white rounded hover:bg-zinc-700">Filter</button>
    </form>

    @include('pages.articles.article_list', ['articles' => $articles])
</div>
@endsection
