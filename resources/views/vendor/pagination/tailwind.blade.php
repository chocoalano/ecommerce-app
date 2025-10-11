@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Paginasi" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between py-4">
        {{-- Paginasi Mobile --}}
        <div class="flex justify-center gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-base font-semibold text-zinc-400 bg-zinc-800 border border-zinc-700 rounded-full cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 text-base font-semibold text-zinc-100 bg-zinc-900 border border-zinc-700 rounded-full hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500">
                    Sebelumnya
                </a>
            @endif

            <span class="inline-flex items-center px-4 py-2 text-base font-semibold text-zinc-100 bg-zinc-800 border border-zinc-700 rounded-full">
                Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 text-base font-semibold text-zinc-100 bg-zinc-900 border border-zinc-700 rounded-full hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500">
                    Selanjutnya
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-base font-semibold text-zinc-400 bg-zinc-800 border border-zinc-700 rounded-full cursor-not-allowed">
                    Selanjutnya
                </span>
            @endif
        </div>

        {{-- Paginasi Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-zinc-400">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-zinc-900">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-semibold text-zinc-900">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-semibold text-zinc-900">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>
            <div>
                <ul class="inline-flex items-center gap-2 text-base h-10">
                    {{-- Tombol Sebelumnya --}}
                    @if ($paginator->onFirstPage())
                        <li>
                            <span aria-disabled="true" aria-label="Sebelumnya" class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-400 bg-zinc-800 border border-zinc-700 rounded-full cursor-not-allowed">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Sebelumnya
                            </span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-100 bg-zinc-900 border border-zinc-700 rounded-full hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Sebelumnya
                            </a>
                        </li>
                    @endif

                    {{-- Elemen Paginasi --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li>
                                <span class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-400 bg-zinc-800 border border-zinc-700 rounded-full cursor-default">{{ $element }}</span>
                            </li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li>
                                        <span aria-current="page" class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-100 bg-zinc-700 border border-zinc-700 rounded-full cursor-default">{{ $page }}</span>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $url }}" class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-100 bg-zinc-900 border border-zinc-700 rounded-full hover:bg-zinc-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-zinc-500" aria-label="Ke halaman {{ $page }}">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Tombol Selanjutnya --}}
                    @if ($paginator->hasMorePages())
                        <li>
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-100 bg-zinc-900 border border-zinc-700 rounded-full hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500">
                                Selanjutnya
                                <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                    @else
                        <li>
                            <span aria-disabled="true" aria-label="Selanjutnya" class="flex items-center justify-center px-4 h-10 leading-tight text-zinc-400 bg-zinc-800 border border-zinc-700 rounded-full cursor-not-allowed">
                                Selanjutnya
                                <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
