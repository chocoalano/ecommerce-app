@extends('layouts.app')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    @php
        // Default agar aman kalau variabel belum dikirim
        $customer = $customer ?? null;
        $title = $title ?? 'Daftar Order';
        $currentType = $currentType ?? 'All';
        $routeBase = 'auth.transaction-order';

        // Header default sesuai renderer JS (jangan ubah labelnya tanpa ubah JS juga)
        $header = $header ?? [];
        if (empty($header)) {
            $header = ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status'];
        }

        $breadcrumbs = $breadcrumbs ?? [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Daftar Order', 'href' => null],
        ];

        $zinc_900 = 'text-zinc-900';
    @endphp

    <section class="bg-gray-50 py-8 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-12 gap-6">
                {{-- Sidebar profil (opsional) --}}
                @include('pages.auth.profile.partial.sidebar')

                <main class="col-span-12 md:col-span-9">
                    {{-- Breadcrumb (opsional) --}}
                    @if(!empty($breadcrumbs))
                        <livewire:components.breadcrumb :breadcrumbs="$breadcrumbs" />
                    @endif

                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm">
                            <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l2.293 2.293c.63.63 1.282.68 1.932.164l.85-1.127m0 0l-2.29-2.29M12 18h.01M21 21h.01M9 19a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            <p class="text-sm text-gray-500">Lihat riwayat order produk Anda.</p>
                        </div>
                    </div>

                    {{-- Alert global untuk feedback JS --}}
                    <div id="ordersFeedback" class="hidden mb-4 rounded border p-3 text-sm"></div>

                    <div class="space-y-6">
                        {{-- Tabs status (Pending / Berbayar / Selesai) — dikendalikan AJAX (pushState) --}}
                        <div class="flex flex-wrap gap-3 rounded-lg border border-gray-200 bg-white p-4">
                            <a href="{{ route($routeBase, ['type' => 'pending']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ strtolower($currentType) === 'pending' ? 'bg-zinc-700 text-white' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Pending Pembayaran
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'shipped']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ strtolower($currentType) === 'shipped' ? 'bg-zinc-700 text-white' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Diproses / Dikirim
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'completed']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ strtolower($currentType) === 'completed' ? 'bg-zinc-700 text-white' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Selesai
                            </a>
                        </div>

                        {{-- Tabel shell: diisi oleh JS lewat AJAX (tanpa reload) --}}
                        <div class="relative overflow-x-auto border border-gray-200 sm:rounded-lg">
                            <table id="ordersTable" class="w-full text-left text-sm text-gray-500">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                                    <tr>
                                        @foreach ($header as $item)
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500">
                                                {{ $item }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200">
                                    {{-- Placeholder awal (akan diganti JS) --}}
                                    <tr>
                                        <td colspan="{{ count($header) }}"
                                            class="px-4 py-6 text-center text-sm text-gray-500">
                                            Memuat data...
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="{{ count($header) }}" class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-4">
                                                {{-- Indikator halaman diisi JS --}}
                                                <div data-page-indicator class="text-sm text-gray-500">Halaman 1</div>
                                                {{-- Pager tombol Prev/Next diisi JS --}}
                                                <div id="ajaxPager"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function ($) {
  // Hanya jalankan di halaman ini yang punya #ordersTable
  const $ordersTable = $('#ordersTable');
  if (!$ordersTable.length) {
    return;
  }

  // ====== ELEMENTS ======
  const $table   = $ordersTable;
  const $thead   = $table.find('thead');
  const $tbody   = $table.find('tbody');
  const $tfoot   = $table.find('tfoot');
  const $navWrap = $table.closest('.space-y-6').find('a[href*="?type="]').parent().parent();

  // ====== HELPERS ======
  const STATUS_LABEL = {
    pending   : 'Menunggu Pembayaran',
    paid      : 'Dibayar',
    confirmed : 'Dikonfirmasi',
    processing: 'Diproses',
    shipped   : 'Dikirim',
    completed : 'Selesai',
    cancelled : 'Dibatalkan',
    canceled  : 'Dibatalkan',
    refunded  : 'Dikembalikan',
    partial_refund: 'Sebagian Dikembalikan'
  };
  const STATUS_BADGE = {
    pending   : 'bg-gray-100 text-gray-800',
    paid      : 'bg-blue-100 text-blue-800',
    confirmed : 'bg-indigo-100 text-indigo-800',
    processing: 'bg-yellow-100 text-yellow-800',
    shipped   : 'bg-green-100 text-green-800',
    completed : 'bg-green-100 text-green-800',
    cancelled : 'bg-red-100 text-red-800',
    canceled  : 'bg-red-100 text-red-800',
    refunded  : 'bg-purple-100 text-purple-800',
    partial_refund: 'bg-violet-100 text-violet-800',
    default   : 'bg-gray-100 text-gray-800',
  };

  function toIDR(n){
    const v = typeof n === 'string' ? parseFloat(n) : (n || 0);
    if (isNaN(v)) return n ?? '-';
    return 'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(v);
  }
  function formatDate(iso){
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleString('id-ID', {
      year:'numeric', month:'short', day:'2-digit',
      hour:'2-digit', minute:'2-digit', hour12:false
    });
  }
  function getQuery(){
    const sp = new URLSearchParams(window.location.search);
    return sp;
  }
  function setQuery(sp){
    const url = new URL(window.location.href);
    url.search = sp.toString();
    window.history.pushState({ q: url.search }, '', url.toString());
  }
  function ensureFooterAreas(){
    const $flex = $tfoot.find('.flex.justify-end.items-center.gap-4');
    const $host = $flex.length ? $flex : $tfoot.find('td').first();

    let $pageInfo = $host.find('[data-page-indicator]');
    if (!$pageInfo.length){
      $pageInfo = $('<div data-page-indicator class="text-sm text-gray-500 mr-2"></div>').prependTo($host);
    }
    let $pager = $host.find('#ajaxPager');
    if (!$pager.length){
      $pager = $('<div id="ajaxPager"></div>').appendTo($host);
    }
    return { $pageInfo, $pager };
  }
  function skuSkeletonRows(cnt=3){
    let tr = '';
    for (let i=0;i<cnt;i++){
      tr += `<tr class="animate-pulse">
        <td colspan="${$thead.find('th').length}" class="px-4 py-3">
          <div class="h-4 w-1/3 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 w-2/3 bg-gray-200 rounded"></div>
        </td>
      </tr>`;
    }
    return tr;
  }
  function emptyRow(){
    return `<tr><td class="px-4 py-6 text-center text-sm text-gray-500" colspan="${$thead.find('th').length}">
      Tidak ada order yang ditemukan dengan status ini.
    </td></tr>`;
  }

  function readHeaderOrder(){
    return $thead.find('th').map(function(){ return $(this).text().trim(); }).get();
  }

  function renderRow(order, headerOrder){
    const id     = order.order_no || `#${order.id}`;
    const date   = formatDate(order.placed_at || order.created_at);
    const total  = toIDR(order.grand_total);

    const items  = Array.isArray(order.items) ? order.items : [];
    const names  = items.map(i => i.name || '').filter(Boolean);
    const qtySum = items.reduce((a,i)=> a + (parseInt(i.qty,10) || 0), 0);

    let namaProduk = '-';
    if (names.length === 1) namaProduk = names[0];
    else if (names.length > 1) namaProduk = `${names[0]} (+${names.length-1})`;

    const skey   = String(order.status||'').trim().toLowerCase();
    console.log('Order:', id, 'Status raw:', order.status, 'skey:', skey, 'Found:', !!STATUS_LABEL[skey]);
    const sLabel = STATUS_LABEL[skey] || (order.status ? String(order.status).charAt(0).toUpperCase()+String(order.status).slice(1) : 'Tidak diketahui');
    const sClass = STATUS_BADGE[skey] || STATUS_BADGE.default;
    console.log('Status Label:', sLabel, 'Status Class:', sClass);
    const statusHTML = `<span class="inline-flex items-center rounded-full ${sClass} px-2 py-1 text-xs font-semibold">${sLabel}</span>`;

    const cellMap = {
      'ID Order'     : id,
      'Tanggal'      : date,
      'Nama Produk'  : namaProduk,
      'Kuantitas'    : qtySum,
      'Total (IDR)'  : total,
      'Status'       : statusHTML
    };

    const tds = headerOrder.map(h => {
      const v = (h in cellMap) ? cellMap[h] : (order[h] ?? '-');
      return `<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${v}</td>`;
    }).join('');

    return `<tr class="hover:bg-gray-50">${tds}</tr>`;
  }

  function renderTable(orders){
    const headerOrder = readHeaderOrder();

    if (!orders || !orders.length){
      $tbody.html(emptyRow());
      return;
    }
    let html = '';
    orders.forEach(o => html += renderRow(o, headerOrder));
    $tbody.html(html);
  }

  function renderPager(pg){
    const { $pageInfo, $pager } = ensureFooterAreas();
    const current = pg?.current_page || 1;
    const last    = pg?.last_page    || 1;

    $pageInfo.text(`Halaman ${current}`);

    if (last <= 1){
      $pager.html('');
      return;
    }
    const prev = Math.max(1, current-1);
    const next = Math.min(last, current+1);
    const prevDis = current<=1 ? 'pointer-events-none opacity-50' : '';
    const nextDis = current>=last ? 'pointer-events-none opacity-50' : '';

    $pager.html(`
      <div class="inline-flex items-center gap-2">
        <a href="#" data-page="${prev}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 ${prevDis}">‹ Sebelumnya</a>
        <a href="#" data-page="${next}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 ${nextDis}">Selanjutnya ›</a>
      </div>
    `);
  }

  function setActiveTypeFromURL(){
    const sp   = getQuery();
    const type = (sp.get('type') || 'All').toLowerCase();

    const activeClass = 'bg-zinc-700 text-white shadow-md';
    const normalClass = 'text-zinc-700 hover:bg-gray-100';

    const $links = $('a[href*="?type="]');
    $links.each(function(){
      const href = $(this).attr('href') || '';
      const u = new URL(href, window.location.origin);
      const t = (u.searchParams.get('type') || 'All').toLowerCase();

      if ((t || 'all') === type){
        $(this).removeClass(normalClass).addClass(activeClass);
      } else {
        $(this).removeClass(activeClass).addClass(normalClass);
      }
    });
  }

  // ====== FETCH ======
  function buildFetchUrl() {
    const url = new URL(window.location.href);
    return url.toString();
  }

  function fetchOrders() {
    $tbody.html(skuSkeletonRows(3));
    const url = buildFetchUrl();

    $.ajax({
      url: url,
      type: 'GET',
      dataType: 'json',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      success: function(resp){
        if (!resp || resp.success !== true){
          $tbody.html(emptyRow());
          renderPager({current_page:1,last_page:1});
          return;
        }
        renderTable(resp.orders || []);
        renderPager(resp.pagination || {});
        setActiveTypeFromURL();
      },
      error: function(){
        $tbody.html(emptyRow());
        renderPager({current_page:1,last_page:1});
      }
    });
  }

  // ====== EVENTS ======
  $(document).on('click', 'a[href*="?type="]', function(e){
    if (!$(this).closest('.space-y-6').length) return;

    e.preventDefault();
    const href = $(this).attr('href');
    const url  = new URL(href, window.location.origin);

    const sp = getQuery();
    sp.set('type', url.searchParams.get('type') || 'All');
    sp.delete('page');
    setQuery(sp);
    fetchOrders();
    window.scrollTo({top:0, behavior:'smooth'});
  });

  $tfoot.on('click', '#ajaxPager a[data-page]', function(e){
    e.preventDefault();
    const pg = parseInt($(this).data('page'), 10);
    if (isNaN(pg)) return;
    const sp = getQuery();
    sp.set('page', String(pg));
    setQuery(sp);
    fetchOrders();
    window.scrollTo({top:0, behavior:'smooth'});
  });

  window.addEventListener('popstate', function(){ fetchOrders(); });

  // ====== INIT ======
  fetchOrders();
})(jQuery);
</script>
@endpush
