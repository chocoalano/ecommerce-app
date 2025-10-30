(function ($) {
  // ====== ELEMENTS ======
  const $table   = $('table').first();                            // atau: $('#ordersTable')
  const $thead   = $table.find('thead');
  const $tbody   = $table.find('tbody');
  const $tfoot   = $table.find('tfoot');
  const $navWrap = $table.closest('.space-y-6').find('a[href*="?type="]').parent().parent(); // container 3 tab

  // ====== HELPERS ======
  const STATUS_LABEL = {
    pending   : 'Menunggu Pembayaran',
    confirmed : 'Dikonfirmasi',
    processing: 'Diproses',
    shipped   : 'Dikirim',
    completed : 'Selesai',
    cancelled : 'Dibatalkan'
  };
  const STATUS_BADGE = {
    completed : 'bg-green-100 text-green-800',
    shipped   : 'bg-green-100 text-green-800',
    processing: 'bg-yellow-100 text-yellow-800',
    pending   : 'bg-yellow-100 text-yellow-800',
    cancelled : 'bg-red-100 text-red-800',
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
    // Struktur footer: <div class="flex ... gap-4"><div>Halaman X</div><div>[links]</div></div>
    const $flex = $tfoot.find('.flex.justify-end.items-center.gap-4');
    // jika markup tailwind default dipakai, aman; jika tidak, fallback:
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

  // Ambil urutan kolom dari header (supaya JS mengikuti header Blade)
  function readHeaderOrder(){
    return $thead.find('th').map(function(){ return $(this).text().trim(); }).get();
  }

  // Render satu baris sesuai header
  function renderRow(order, headerOrder){
    // Ambil data pokok
    const id     = order.order_no || `#${order.id}`;
    const date   = formatDate(order.placed_at || order.created_at);
    const total  = toIDR(order.grand_total);

    // Produk & qty
    const items  = Array.isArray(order.items) ? order.items : [];
    const names  = items.map(i => i.name || '').filter(Boolean);
    const qtySum = items.reduce((a,i)=> a + (parseInt(i.qty,10) || 0), 0);

    let namaProduk = '-';
    if (names.length === 1) namaProduk = names[0];
    else if (names.length > 1) namaProduk = `${names[0]} (+${names.length-1})`;

    // Status
    const skey   = String(order.status||'').toLowerCase();
    const sLabel = STATUS_LABEL[skey] || (order.status ? String(order.status).charAt(0).toUpperCase()+String(order.status).slice(1) : '-');
    const sClass = STATUS_BADGE[skey] || STATUS_BADGE.default;
    const statusHTML = `<span class="inline-flex items-center rounded-full ${sClass} px-2 py-1 text-xs font-semibold">${sLabel}</span>`;

    // Map menurut header yang ditampilkan di Blade:
    // ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status']
    const cellMap = {
      'ID Order'     : id,
      'Tanggal'      : date,
      'Nama Produk'  : namaProduk,
      'Kuantitas'    : qtySum,
      'Total (IDR)'  : total,
      'Status'       : statusHTML
    };

    // Build <td> sesuai urutan header
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

    // toggle kelas aktif pada 3 tab
    const activeClass = 'bg-zinc-700 text-white shadow-md';
    const normalClass = 'text-zinc-700 hover:bg-gray-100';

    // cari semua link ?type=...
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
    // pakai path sekarang, kirim query yang sama + Accept JSON → controller balas JSON
    const url = new URL(window.location.href);
    // Controller sudah membaca: type, search, status, status_in[], date_from, date_to, per_page, page
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
  // Klik tab type (Pending/Berbayar/Selesai) → AJAX tanpa reload
  $(document).on('click', 'a[href*="?type="]', function(e){
    // batasi hanya tabs pada section ini (agar tidak ganggu link lain yg kebetulan punya ?type=)
    if (!$(this).closest('.space-y-6').length) return;

    e.preventDefault();
    const href = $(this).attr('href');
    const url  = new URL(href, window.location.origin);

    // ambil query eksisting dan replace type
    const sp = getQuery();
    sp.set('type', url.searchParams.get('type') || 'All');
    sp.delete('page'); // reset ke page 1 ketika ganti type
    setQuery(sp);
    fetchOrders();
    window.scrollTo({top:0, behavior:'smooth'});
  });

  // Pagination Prev/Next
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

  // Back/Forward browser
  window.addEventListener('popstate', function(){ fetchOrders(); });

  // ====== INIT ======
  fetchOrders();
})(jQuery);
