// orders-adapted.js
$(function () {
  // ====== ELEMENTS & ENDPOINT CONFIG ======
  const $wrapper        = $('#ordersContainer');
  const $list           = $('#ordersList');
  const $pagination     = $('#ordersPagination');
  const $feedbackGlobal = $('#ordersFeedback');

  const SOURCE_URL            = $wrapper.data('sourceUrl') || window.location.pathname;
  const DETAIL_URL_TEMPLATE   = $wrapper.data('detailUrlTemplate') || '/orders/:id';
  const CANCEL_URL_TEMPLATE   = $wrapper.data('cancelUrlTemplate') || '/orders/:id/cancel';

  const CSRF = $('meta[name="csrf-token"]').attr('content') || '';

  // ====== STATE ======
  let currentPage = 1;
  let orderCache = new Map(); // id -> order object
  let flowbiteCancelModal = null;
  let flowbiteDetailModal = null;

  // ====== HELPERS ======
  const STATUS_MAP = {
    pending   : { badge:'bg-gray-100 text-gray-700',       label:'Menunggu' },
    processing: { badge:'bg-amber-100 text-amber-700',     label:'Diproses' },
    shipped   : { badge:'bg-green-100 text-green-700',     label:'Dikirim' },
    completed : { badge:'bg-emerald-100 text-emerald-700', label:'Selesai' },
    cancelled : { badge:'bg-red-100 text-red-700',         label:'Dibatalkan' },
    default   : { badge:'bg-gray-100 text-gray-700',       label:'Tidak diketahui' },
  };
  function statusView(statusRaw){
    const key = String(statusRaw || '').toLowerCase();
    return STATUS_MAP[key] || STATUS_MAP.default;
  }
  function formatIDR(n){
    const num = typeof n === 'string' ? parseFloat(n) : (n || 0);
    if (isNaN(num)) return n ?? '-';
    return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 }).format(num);
  }
  function formatDateISO(iso){
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleString('id-ID', {
      year:'numeric', month:'short', day:'2-digit',
      hour:'2-digit', minute:'2-digit', hour12:false
    });
  }
  function buildUrl(page){
    const u = new URL(SOURCE_URL, window.location.origin);
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);
    u.search = params.toString();
    return u.pathname + u.search;
  }
  function detailUrl(id){ return (DETAIL_URL_TEMPLATE || '').replace(':id', id); }
  function cancelUrl(id){ return (CANCEL_URL_TEMPLATE || '').replace(':id', id); }
  function showGlobal(msg, ok=false){
    if (!$feedbackGlobal?.length) return;
    $feedbackGlobal.removeClass('hidden')
      .toggleClass('bg-red-50 text-red-700 border-red-200', !ok)
      .toggleClass('bg-green-50 text-green-700 border-green-200', ok)
      .html(msg);
    setTimeout(()=> $feedbackGlobal.addClass('hidden'), 4200);
  }
  function skeleton(count=3){
    const item = `
      <div class="animate-pulse border-b border-gray-200 py-4 md:py-5">
        <div class="mb-3 h-4 w-32 rounded bg-gray-200"></div>
        <div class="grid grid-cols-2 gap-4">
          <div class="h-4 rounded bg-gray-200"></div>
          <div class="h-4 rounded bg-gray-200"></div>
          <div class="h-4 rounded bg-gray-200"></div>
          <div class="h-4 rounded bg-gray-200"></div>
        </div>
      </div>`;
    return new Array(count).fill(item).join('');
  }
  function renderEmpty(){
    return `<div class="rounded-lg border border-gray-200 bg-white p-6 text-center text-gray-500">Belum ada pesanan.</div>`;
  }

  // ====== RENDER ITEM LIST ======
  function renderOrderItem(o, isLast=false){
    const s = statusView(o.status);
    const rowClass = isLast ? 'pt-4 md:pt-5' : 'border-b border-gray-200 md:py-5 py-4 pb-4';
    const canCancel = String(o.status).toUpperCase() === 'PENDING';

    // Cache object untuk detail modal
    orderCache.set(o.id, o);

    return `
      <div class="flex flex-wrap items-center gap-y-4 ${rowClass}">
        <dl class="w-1/2 sm:w-60">
          <dt class="text-base font-medium text-gray-500">No. Pesanan:</dt>
          <dd class="mt-1.5 text-base font-semibold text-gray-900">
            <a href="#" class="hover:underline" data-action="order-detail" data-order-id="${o.id}">${o.order_no || ('#'+o.id)}</a>
          </dd>
        </dl>

        <dl class="w-1/2 sm:w-1/4 md:flex-1 lg:w-auto">
          <dt class="text-base font-medium text-gray-500">Tanggal:</dt>
          <dd class="mt-1.5 text-base font-semibold text-gray-900">${formatDateISO(o.placed_at || o.created_at)}</dd>
        </dl>

        <dl class="w-1/2 sm:w-1/5 md:flex-1 lg:w-auto">
          <dt class="text-base font-medium text-gray-500">Total:</dt>
          <dd class="mt-1.5 text-base font-semibold text-gray-900">${formatIDR(o.grand_total)}</dd>
        </dl>

        <dl class="w-1/2 sm:w-1/4 sm:flex-1 lg:w-auto">
          <dt class="text-base font-medium text-gray-500">Status:</dt>
          <dd class="me-2 mt-1.5 inline-flex shrink-0 items-center rounded px-2.5 py-0.5 text-xs font-medium ${s.badge}">
            ${s.label}
          </dd>
        </dl>

        <div class="w-full sm:flex sm:w-48 sm:items-center sm:justify-end sm:gap-2">
          <a href="${detailUrl(o.id)}" data-action="order-detail" data-order-id="${o.id}"
             class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 md:w-auto">
            Detail
          </a>
          ${canCancel ? `
          <a href="${cancelUrl(o.id)}" data-action="cancel-order" data-order-id="${o.id}" data-order-label="${o.order_no || ('#'+o.id)}"
             class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 md:w-auto">
            Batalkan
          </a>` : ``}
        </div>
      </div>
    `;
  }

  // ====== RENDER PAGINATION (Prev/Next only) ======
  function renderPagination(pg){
    const current = pg?.current_page || 1;
    const last    = pg?.last_page    || 1;
    if (last <= 1) { $pagination.html(''); return; }

    const prevPage = Math.max(1, current - 1);
    const nextPage = Math.min(last, current + 1);
    const prevDisabled = current <= 1 ? 'pointer-events-none opacity-50' : '';
    const nextDisabled = current >= last ? 'pointer-events-none opacity-50' : '';

    const html = `
      <div class="flex items-center justify-between border-t border-gray-200 px-2 pt-4" role="navigation" aria-label="Pagination">
        <a href="${buildUrl(prevPage)}" data-page="${prevPage}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 ${prevDisabled}">
          ‹ Sebelumnya
        </a>
        <a href="${buildUrl(nextPage)}" data-page="${nextPage}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 ${nextDisabled}">
          Selanjutnya ›
        </a>
      </div>`;
    $pagination.html(html);
  }

  // ====== FLOWBITE SAFE INIT ======
  function showModal($el, cacheRef){
    if (window.Modal) {
      if (!cacheRef.ref) cacheRef.ref = new Modal($el[0]);
      cacheRef.ref.show();
    } else {
      $el.removeClass('hidden').addClass('flex');
    }
  }
  function hideModal($el, cacheRef){
    if (cacheRef.ref) cacheRef.ref.hide();
    else $el.addClass('hidden').removeClass('flex');
  }

  // ====== FETCH LIST (ADAPTED TO YOUR RESPONSE) ======
  function fetchOrders(pageOrUrl){
    let url;
    if (typeof pageOrUrl === 'number') url = buildUrl(pageOrUrl);
    else if (!pageOrUrl) {
      const qp = new URLSearchParams(window.location.search);
      url = buildUrl(parseInt(qp.get('page') || '1', 10));
    } else url = pageOrUrl;

    $list.html(skeleton(3));
    $pagination.html('');

    $.ajax({
      url,
      type: 'GET',
      dataType: 'json',
      headers: { 'Accept':'application/json' },
      success: function(resp){
        if (!resp || resp.success !== true) {
          $list.html(renderEmpty());
          renderPagination(resp?.pagination);
          showGlobal('Gagal memuat data (format tidak valid).');
          return;
        }

        const orders = resp.orders || [];
        const pg     = resp.pagination || { current_page:1, last_page:1 };

        currentPage = pg.current_page || 1;
        orderCache.clear();

        if (!orders.length) {
          $list.html(renderEmpty());
          renderPagination(pg);
          return;
        }

        let html = '';
        orders.forEach((o, i) => { html += renderOrderItem(o, i === orders.length - 1); });
        $list.html(html);
        renderPagination(pg);

        // update URL (tanpa reload)
        const newUrl = buildUrl(currentPage);
        if (newUrl !== (window.location.pathname + window.location.search)) {
          window.history.pushState({ page: currentPage }, '', newUrl);
        }
      },
      error: function(xhr){
        $list.html(renderEmpty());
        showGlobal(`Gagal memuat data (HTTP ${xhr.status||0}).`);
      }
    });
  }

  // ====== DETAIL MODAL ======
  const $detailModal = $('#orderDetailModal');
  const $detailTitle = $('#orderDetailTitle');
  const $detailBody  = $('#orderDetailBody');
  const $detailClose = $('#btnCloseDetailModal');

  function renderOrderDetail(order){
    const s = statusView(order.status);
    const items = order.items || [];
    const addr  = order.shipping_address || {};
    const itemsHtml = items.map(it => `
      <tr>
        <td class="px-0 py-1 text-sm text-gray-900">${it.name || '-'}</td>
        <td class="px-0 py-1 text-sm text-gray-500">${it.sku || '-'}</td>
        <td class="px-0 py-1 text-sm text-gray-900" align="center">${it.qty ?? 0}</td>
        <td class="px-0 py-1 text-sm text-gray-900" align="right">${formatIDR(it.unit_price)}</td>
        <td class="px-0 py-1 text-sm text-gray-900" align="right">${formatIDR(it.row_total)}</td>
      </tr>
    `).join('') || `<tr><td colspan="5" class="py-2 text-center text-sm text-gray-500">Tidak ada item.</td></tr>`;

    // Try parse notes JSON (optional)
    let paymentInfo = '';
    try {
      if (order.notes) {
        const n = JSON.parse(order.notes);
        paymentInfo = `
          <div class="mt-2 text-xs text-gray-500">
            Metode: <span class="font-medium text-gray-800">${n.payment_method || n.gateway || '-'}</span>
            ${n.redirect_url ? ` &middot; <a class="text-blue-600 underline" href="${n.redirect_url}" target="_blank" rel="noopener">Bayar</a>` : ``}
          </div>`;
      }
    } catch(_) {}

    return `
      <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div class="text-sm text-gray-600">
          Tanggal: <span class="font-medium text-gray-900">${formatDateISO(order.placed_at || order.created_at)}</span>
          ${paymentInfo}
        </div>
        <span class="inline-flex items-center rounded px-2.5 py-0.5 text-xs font-medium ${s.badge}">
          ${s.label}
        </span>
      </div>

      <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded border p-3">
          <div class="mb-1 text-sm font-semibold text-gray-900">Alamat Pengiriman</div>
          <div class="text-sm text-gray-700">
            <div>${addr.recipient_name || '-'}</div>
            <div>${addr.phone || '-'}</div>
            <div>${[addr.line1, addr.line2].filter(Boolean).join(', ') || '-'}</div>
            <div>${[addr.city, addr.province, addr.postal_code].filter(Boolean).join(', ') || '-'}</div>
          </div>
        </div>
        <div class="rounded border p-3">
          <div class="mb-1 text-sm font-semibold text-gray-900">Ringkasan</div>
          <div class="grid grid-cols-2 text-sm">
            <div class="text-gray-500">Subtotal</div><div class="text-right text-gray-900">${formatIDR(order.subtotal_amount)}</div>
            ${parseFloat(order.discount_amount)>0 ? `<div class="text-gray-500">Diskon</div><div class="text-right text-gray-900">-${formatIDR(order.discount_amount)}</div>`:''}
            ${parseFloat(order.tax_amount)>0 ? `<div class="text-gray-500">Pajak</div><div class="text-right text-gray-900">${formatIDR(order.tax_amount)}</div>`:''}
            ${parseFloat(order.shipping_amount)>0 ? `<div class="text-gray-500">Ongkir</div><div class="text-right text-gray-900">${formatIDR(order.shipping_amount)}</div>`:''}
            <div class="col-span-2 mt-1 border-t pt-2 text-right text-base font-bold text-gray-900">Total: ${formatIDR(order.grand_total)}</div>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full border-separate border-spacing-x-0 border-spacing-y-0">
          <thead>
            <tr class="text-left text-xs font-semibold text-gray-500">
              <th class="px-0 py-1">Item</th>
              <th class="px-0 py-1">SKU</th>
              <th class="px-0 py-1" align="center">Qty</th>
              <th class="px-0 py-1" align="right">Harga</th>
              <th class="px-0 py-1" align="right">Total</th>
            </tr>
          </thead>
          <tbody>${itemsHtml}</tbody>
        </table>
      </div>
    `;
  }

  $list.on('click', 'a[data-action="order-detail"]', function(e){
    e.preventDefault();
    const id = Number($(this).data('order-id'));
    const order = orderCache.get(id);

    $detailTitle.text(`Detail Pesanan ${order?.order_no || ('#'+id)}`);
    $detailBody.html('<div class="py-8 text-center text-sm text-gray-500">Memuat detail...</div>');
    showModal($('#orderDetailModal'), { get ref(){ return flowbiteDetailModal }, set ref(v){ flowbiteDetailModal = v; } });

    // Karena respons list sudah include detail (items, alamat), langsung render dari cache.
    if (order) {
      $detailBody.html(renderOrderDetail(order));
      return;
    }

    // Fallback: kalau belum ada di cache, fetch detail endpoint
    $.ajax({
      url: detailUrl(id),
      type: 'GET',
      dataType: 'json',
      headers: { 'Accept':'application/json' },
      success: function(resp){
        const data = resp?.data || resp || {};
        orderCache.set(id, data);
        $detailBody.html(renderOrderDetail(data));
      },
      error: function(xhr){
        const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || `Gagal memuat detail (HTTP ${xhr.status||0}).`;
        $detailBody.html(`<div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">${msg}</div>`);
      }
    });
  });

  $('#btnCloseDetailModal').on('click', function(){
    hideModal($('#orderDetailModal'), { get ref(){ return flowbiteDetailModal }, set ref(v){ flowbiteDetailModal = v; } });
  });

  // ====== CANCEL (DELETE) ======
  const $cancelModal   = $('#deleteOrderModal');
  const $cancelLabel   = $('#cancelOrderLabel');
  const $cancelAlert   = $('#cancelOrderAlert');
  const $cancelClose   = $('#btnCloseCancelModal');
  const $cancelConfirm = $('#btnConfirmCancel');
  const $cancelSpinner = $('#cancelSpinner');

  function setCancelDialog({label, url}) {
    $cancelLabel.text(label || '#—');
    $cancelConfirm.data('cancel-url', url || '');
    $cancelAlert.addClass('hidden').removeClass('bg-red-50 text-red-700 border-red-200 bg-green-50 text-green-700 border-green-200').text('');
  }
  function setCancelLoading(b) {
    $cancelConfirm.prop('disabled', b);
    $cancelSpinner.toggleClass('hidden', !b);
  }

  $list.on('click', 'a[data-action="cancel-order"]', function(e){
    e.preventDefault();
    const id    = $(this).data('order-id');
    const label = $(this).data('order-label') || ('#'+id);
    setCancelDialog({ label, url: cancelUrl(id) });
    showModal($cancelModal, { get ref(){ return flowbiteCancelModal }, set ref(v){ flowbiteCancelModal = v; } });
  });

  $cancelClose.on('click', function(){
    hideModal($cancelModal, { get ref(){ return flowbiteCancelModal }, set ref(v){ flowbiteCancelModal = v; } });
  });

  $cancelConfirm.on('click', function(){
    const url = $(this).data('cancel-url');
    if (!url) return;

    setCancelLoading(true);
    $.ajax({
      url,
      type: 'DELETE',
      dataType: 'json',
      headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF },
      success: function(resp){
        setCancelLoading(false);
        hideModal($cancelModal, { get ref(){ return flowbiteCancelModal }, set ref(v){ flowbiteCancelModal = v; } });
        const msg = (resp && (resp.message || resp.msg)) || 'Pesanan berhasil dibatalkan.';
        showGlobal(msg, true);
        fetchOrders(currentPage);
      },
      error: function(xhr){
        setCancelLoading(false);
        const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || `Gagal membatalkan (HTTP ${xhr.status||0}).`;
        $cancelAlert.removeClass('hidden').addClass('rounded border border-red-200 bg-red-50 p-2 text-red-700').text(msg);
      }
    });
  });

  // ====== PAGINATION CLICK ======
  $pagination.on('click', 'a[data-page]', function(e){
    e.preventDefault();
    const page = parseInt($(this).data('page'), 10);
    if (!isNaN(page)) fetchOrders(page);
  });

  // ====== POPSTATE ======
  window.addEventListener('popstate', function(){ fetchOrders(); });

  // ====== INIT GUARDS ======
  if (!$list.length)  { console.warn('#ordersList tidak ditemukan'); return; }
  if (!$pagination.length) { console.warn('#ordersPagination tidak ditemukan'); return; }

  fetchOrders();
});
