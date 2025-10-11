<nav class="text-sm mb-6" aria-label="Breadcrumb">
    <ol class="list-none p-0 inline-flex items-center">
        {{-- Home Link --}}
        @if($showHome)
        <li class="flex items-center">
            <a href="{{ url($homeUrl) }}" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                {{ $homeLabel }}
            </a>
            @if(count($items) > 0)
            <svg class="w-3 h-3 text-gray-400 mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            @endif
        </li>
        @endif

        {{-- Breadcrumb Items --}}
        @foreach($items as $index => $item)
        <li class="flex items-center">
            @if($item['url'] && !($item['is_active'] ?? false))
                <a href="{{ $item['url'] }}" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                    {{ $item['label'] }}
                </a>
            @else
                <span class="text-gray-900 font-medium truncate max-w-xs sm:max-w-none">
                    {{ $item['label'] }}
                </span>
            @endif

            {{-- Separator (tidak ditampilkan untuk item terakhir) --}}
            @if(!$loop->last)
            <svg class="w-3 h-3 text-gray-400 mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            @endif
        </li>
        @endforeach
    </ol>
</nav>
