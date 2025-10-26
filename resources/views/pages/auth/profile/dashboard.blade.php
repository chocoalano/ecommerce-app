
@extends('layouts.app')

@section('content')
@php
    $customer = $customer ?? null;
@endphp

<section class="bg-white py-8 antialiased md:py-8">
    <div class="mx-auto max-w-7xl px-4 2xl:px-0">
        <div class="grid grid-cols-12 gap-6">
            @include('pages.auth.profile.partial.sidebar')
            <div class="col-span-12 md:col-span-9">
                <!-- Header Dashboard -->
                <div class="mb-6 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100">
                        <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 13h2v-2a7 7 0 0 1 14 0v2h2"/></svg>
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900">Dasbor pengguna</h1>
                </div>

                <!-- Statistik Ringkasan -->
                <div class="mb-8 grid grid-cols-2 gap-6 md:grid-cols-4">
                    @foreach ($overviewStats as $s)
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-zinc-100">
                                    @switch($s['icon'])
                                        @case('users')
                                            <svg class="h-5 w-5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <circle cx="9" cy="8" r="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="17" cy="8" r="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2 21v-1a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4v1"/>
                                            </svg>
                                            @break
                                        @case('truck')
                                            <svg class="h-5 w-5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"/></svg>
                                            @break
                                        @case('star')
                                            <svg class="h-5 w-5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z"/></svg>
                                            @break
                                        @case('heart')
                                            <svg class="h-5 w-5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/></svg>
                                            @break
                                        @default
                                            <svg class="h-5 w-5 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"/></svg>
                                    @endswitch
                                </span>
                                <span class="rounded px-2.5 py-0.5 text-xs font-medium">
                                    {{ $s['delta'] }}
                                </span>
                            </div>
                            <div class="mb-1 text-lg font-bold text-gray-900">{{ $s['value'] }}</div>
                            <div class="text-sm text-gray-500">{{ $s['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
