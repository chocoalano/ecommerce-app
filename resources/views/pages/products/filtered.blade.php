@extends('layouts.app')

@section('content')

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        {{-- Hero Section --}}
        <livewire:components.promotion-hero-secondary show-on="HERO" cache-ttl="100" />

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-1 mt-8">
            <livewire:components.filter-sidebar />
            <livewire:components.product-grid />
        </div>
    </div>
@endsection
