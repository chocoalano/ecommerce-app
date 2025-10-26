<aside class="col-span-12 md:col-span-3">
    <div class="sticky top-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-zinc-300">Menu</h4>
        <nav aria-label="Sidebar">
            @php
                // Define sidebar menu as an array for easier maintenance
                $sidebarMenu = [
                    [
                        'label' => 'Dashboard',
                        'type' => 'link',
                        'href' => route('auth.dashboard'),
                        'icon' => 'chart',
                    ],
                    [
                        'label' => 'Member',
                        'type' => 'group',
                        'id' => 'dropdown-member',
                        'icon' => 'users',
                        'children' => [
                            ['label' => 'Member Aktif', 'href' => route('auth.network-list', ['members'=>'active'])],
                            ['label' => 'Member Pasif', 'href' => route('auth.network-list', ['members'=>'inactive'])],
                            ['label' => 'Prospek Member', 'href' => route('auth.network-list', ['members'=>'prospect'])],
                        ],
                    ],
                    [
                        'label' => 'Network',
                        'type' => 'group',
                        'id' => 'dropdown-network',
                        'icon' => 'network',
                        'children' => [
                            ['label' => 'Binary', 'href' => route('auth.network.info', ['info'=>'binary'])],
                            ['label' => 'Sponsorship', 'href' => route('auth.network.info', ['info'=>'sponsorship'])],
                        ],
                    ],
                    [
                        'label' => 'Komisi',
                        'type' => 'group',
                        'id' => 'dropdown-komisi',
                        'icon' => 'trophy',
                        'children' => [
                            ['label' => 'Komisi Sponsor', 'href' => '#'],
                            ['label' => 'Komisi Pairing', 'href' => '#'],
                            ['label' => 'Komisi Matching', 'href' => '#'],
                            ['label' => 'Reward', 'href' => '#'],
                        ],
                    ],
                    [
                        'label' => 'Ewallet',
                        'type' => 'group',
                        'id' => 'dropdown-ewallet',
                        'icon' => 'wallet',
                        'children' => [
                            ['label' => 'Transaksi Ewallet', 'href' => '#'],
                            ['label' => 'Penarikan Komisi', 'href' => '#'],
                        ],
                    ],
                    [
                        'label' => 'Transaksi Pesanan',
                        'type' => 'group',
                        'id' => 'dropdown-transaksi',
                        'icon' => 'cart',
                        'children' => [
                            ['label' => 'Pending', 'href' => '#'],
                            ['label' => 'Berbayar', 'href' => '#'],
                            ['label' => 'Selesai', 'href' => '#'],
                        ],
                    ],
                ];
            @endphp

            <ul class="space-y-2 font-medium">
                @foreach ($sidebarMenu as $item)
                    @if ($item['type'] === 'link')
                        <li>
                            <a href="{{ $item['href'] }}"
                                class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                                {{-- icon switch --}}
                                @switch($item['icon'])
                                    @case('chart')
                                        <svg class="w-5 h-5 text-zinc-500 transition duration-75 dark:text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 5L7 9m3 2 3-2m-3 5a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z"/></svg>
                                        @break
                                    @default
                                        <svg class="w-5 h-5 text-zinc-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"/></svg>
                                @endswitch
                                <span class="ms-3">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @elseif ($item['type'] === 'group')
                        <li>
                            <button type="button"
                                class="flex items-center w-full p-2 text-zinc-900 transition duration-75 rounded-lg group hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700"
                                aria-controls="{{ $item['id'] }}" data-collapse-toggle="{{ $item['id'] }}">
                                {{-- icon switch for groups --}}
                                @switch($item['icon'])
                                    @case('users')
                                        <svg class="w-5 h-5 text-zinc-500 transition duration-75 dark:text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c0 1.657-2.686 3-6 3S0 5.657 0 4c0-1.657 2.686-3 6-3s6 1.343 6 3Zm-2 4h4.5c1.4 0 2.5 1.12 2.5 2.5v2.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10c0-1.4 1.1-2.5 2.5-2.5H10Z"/></svg>
                                        @break
                                    @case('network')
                                        <svg class="w-5 h-5 text-zinc-500 transition duration-75 dark:text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.25 4.5-.477.477L15 6.25l-.477.477M4.5 4.5l.477.477L3 6.25l.477.477M9 2.25a2.25 2.25 0 1 0 0 4.5A2.25 2.25 0 0 0 9 2.25ZM9 12a2.25 2.25 0 1 0 0 4.5A2.25 2.25 0 0 0 9 12ZM4.845 9.75l.628.718L7.5 8.75m3.655 1.75l.628-.718L10.5 8.75m-6 3.75 3 2.5 3-2.5m-6-8.5 3 2.5 3-2.5"/></svg>
                                        @break
                                    @case('trophy')
                                        <svg class="w-5 h-5 text-zinc-500 transition duration-75 dark:text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v3m0 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM8 7v3m0 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM12 13a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-4 0v3m8-3v3m0-6v3m0 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-4 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
                                        @break
                                    @case('wallet')
                                        <svg class="w-5 h-5 text-zinc-500 transition duration-75 dark:text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 4h.01M10 4h.01M5 4h.01M1 1v18h18V1H1Zm15 11a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                                        @break
                                    @case('cart')
                                        <svg class="w-5 h-5 text-zinc-500 transition duration-75 dark:text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15V4M1 4h16m-12 7V4m4 7V4m0 12v3"/></svg>
                                        @break
                                    @default
                                        <svg class="w-5 h-5 text-zinc-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"/></svg>
                                @endswitch

                                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{ $item['label'] }}</span>
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>

                            <ul id="{{ $item['id'] }}" class="hidden py-2 space-y-2">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a href="{{ $child['href'] }}"
                                            class="flex items-center w-full p-2 text-zinc-900 transition duration-75 rounded-lg pl-11 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700">{{ $child['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
