// orders-vanilla-alt.js  —  Vanilla JS (no jQuery), isolated namespace
(function () {
  'use strict';

  // ====== ELEMENTS ======
  const $wrap   = document.querySelector('#ordersContainer');
  const $list   = document.querySelector('#ordersList');
  const $pager  = document.querySelector('#ordersPagination');
  const $fb     = document.querySelector('#ordersFeedback');

  // Filters (optional presence)
  const $form     = document.querySelector('#ordersFilters');
  const $search   = document.querySelector('#search');
  const $status   = document.querySelector('#order-type-dropdown');
  const $period   = document.querySelector('#duration-dropdown');
  const $perPage  = document.querySelector('#per-page-dropdown'); // kalau ada
  const $btnReset = document.querySelector('#btnFilterReset');

  // Modals
  const $detailModal = document.querySelector('#orderDetailModal');
  const $detailTitle = document.querySelector('#orderDetailTitle');
  const $detailBody  = document.querySelector('#orderDetailBody');
  const $detailClose = document.querySelector('#btnCloseDetailModal');

  const $cancelModal   = document.querySelector('#deleteOrderModal');
  const $cancelLabel   = document.querySelector('#cancelOrderLabel');
  const $cancelAlert   = document.querySelector('#cancelOrderAlert');
  const $cancelClose   = document.querySelector('#btnCloseCancelModal');
  const $cancelConfirm = document.querySelector('#btnConfirmCancel');
  const $cancelSpinner = document.querySelector('#cancelSpinner');

  if (!$wrap || !$list || !$pager) {
    console.warn('[orders-vanilla-alt] Container tidak lengkap, abort.');
    return;
  }

  // ====== CONFIG (ambil dari data-attr) ======
  const SOURCE_URL  = $wrap.dataset.sourceUrl || window.location.pathname;
  const DETAIL_TPL  = $wrap.dataset.detailUrlTemplate || '/orders/:id';
  const CANCEL_TPL  = $wrap.dataset.cancelUrlTemplate || '/orders/:id/cancel';
  const CSRF        = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  // ====== STATE ======
  let currentPage = 1;
  const cache = new Map(); // id -> order
  let flowbiteDetail = null;
  let flowbiteCancel = null;

  // ====== HELPERS ======
  const STATUS = {
    pending   : { badge:'bg-gray-100 text-gray-700',       label:'Menunggu' },
    processing: { badge:'bg-amber-100 text-amber-700',     label:'Diproses' },
    shipped   : { badge:'bg-green-100 text-green-700',     label:'Dikirim' },
    completed : { badge:'bg-emerald-100 text-emerald-700', label:'Selesai' },
    cancelled : { badge:'bg-red-100 text-red-700',         label:'Dibatalkan' },
    default   : { badge:'bg-gray-100 text-gray-700',       label:'Tidak diketahui' },
  };
  const statusView = (raw) => STATUS[String(raw||'').toLowerCase()] || STATUS.default;

  const fmtIDR = (n) => {
    const num = typeof n === 'string' ? parseFloat(n) : (n||0);
    if (isNaN(num)) return n ?? '-';
    return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 }).format(num);
  };
  const fmtDate = (iso) => {
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d)) return iso;
    return d.toLocaleString('id-ID', { year:'numeric', month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false });
  };
  const detailUrl = (id) => DETAIL_TPL.replace(':id', id);
  const cancelUrl = (id) => CANCEL_TPL.replace(':id', id);

  const banner = (msg, ok=false) => {
    if (!$fb) return;
    $fb.classList.remove('hidden');
    $fb.className = 'mb-4 rounded border p-3 text-sm ' + (ok ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200');
    $fb.innerHTML = msg;
    setTimeout(()=> $fb.classList.add('hidden'), 4200);
  };

  const skeleton = (n=3) => {
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
    return new Array(n).fill(item).join('');
  };
  const emptyView = () => `<div class="rounded-lg border border-gray-200 bg-white p-6 text-center text-gray-500">Belum ada pesanan.</div>`;

  // Flowbite-safe modal toggler
  function showModal(el, refHolder){
    if (!el) return;
    if (window.Modal) {
      if (!refHolder.ref) refHolder.ref = new Modal(el);
      refHolder.ref.show();
    } else {
      el.classList.remove('hidden');
      el.classList.add('flex');
    }
  }
  function hideModal(el, refHolder){
    if (!el) return;
    if (refHolder.ref) refHolder.ref.hide();
    else {
      el.classList.add('hidden');
      el.classList.remove('flex');
    }
  }

  // ====== URL BUILD ======
  function currentQuery(){
    // Ambil dari URL agar back/forward terjaga
    const sp = new URLSearchParams(window.location.search);
    return {
      search   : sp.get('search') || '',
      status   : sp.get('status') || '',
      date_from: sp.get('date_from') || '',
      date_to  : sp.get('date_to') || '',
      per_page : sp.get('per_page') || '',
      page     : parseInt(sp.get('page') || '1', 10)
    };
  }

  function pushQueryToUrl(q){
    const url = new URL(window.location.href);
    ['search','status','date_from','date_to','per_page','page'].forEach(k => url.searchParams.delete(k));
    if (q.search)    url.searchParams.set('search', q.search);
    if (q.status)    url.searchParams.set('status', q.status);
    if (q.date_from) url.searchParams.set('date_from', q.date_from);
    if (q.date_to)   url.searchParams.set('date_to', q.date_to);
    if (q.per_page)  url.searchParams.set('per_page', q.per_page);
    if (q.page && q.page > 1) url.searchParams.set('page', String(q.page));
    window.history.pushState({ page: q.page || 1 }, '', url.toString());
  }

  function buildListUrl(page){
    const base = new URL(SOURCE_URL, window.location.origin);
    const sp   = new URLSearchParams(window.location.search);
    sp.set('page', page);
    base.search = sp.toString();
    return base.pathname + base.search;
  }

  // ====== RENDER ======
  function renderItem(o, isLast=false){
    cache.set(o.id, o);
    const s = statusView(o.status);
    const canCancel = String(o.status).toUpperCase() === 'PENDING';
    const rowClass = isLast ? 'pt-4 md:pt-5' : 'border-b border-gray-200 md:py-5 py-4 pb-4';

    return `
      <div class="flex flex-wrap items-center gap-y-4 ${rowClass}">
        <dl class="w-1/2 sm:w-60">
          <dt class="text-base font-medium text-gray-500">No. Pesanan:</dt>
          <dd class="mt-1.5 text-base font-semibold text-gray-900">
            <a href="${detailUrl(o.id)}" data-action="detail" data-id="${o.id}" class="hover:underline">${o.order_no || ('#'+o.id)}</a>
          </dd>
        </dl>

        <dl class="w-1/2 sm:w-1/4 md:flex-1 lg:w-auto">
          <dt class="text-base font-medium text-gray-500">Tanggal:</dt>
          <dd class="mt-1.5 text-base font-semibold text-gray-900">${fmtDate(o.placed_at || o.created_at)}</dd>
        </dl>

        <dl class="w-1/2 sm:w-1/5 md:flex-1 lg:w-auto">
          <dt class="text-base font-medium text-gray-500">Total:</dt>
          <dd class="mt-1.5 text-base font-semibold text-gray-900">${fmtIDR(o.grand_total)}</dd>
        </dl>

        <dl class="w-1/2 sm:w-1/4 sm:flex-1 lg:w-auto">
          <dt class="text-base font-medium text-gray-500">Status:</dt>
          <dd class="me-2 mt-1.5 inline-flex shrink-0 items-center rounded px-2.5 py-0.5 text-xs font-medium ${s.badge}">
            ${s.label}
          </dd>
        </dl>

        <div class="w-full sm:flex sm:w-48 sm:items-center sm:justify-end sm:gap-2">
          <a href="${detailUrl(o.id)}" data-action="detail" data-id="${o.id}"
             class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 md:w-auto">
            Detail
          </a>
          ${canCancel ? `
          <a href="${cancelUrl(o.id)}" data-action="cancel" data-id="${o.id}" data-label="${o.order_no || ('#'+o.id)}"
             class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 md:w-auto">
            Batalkan
          </a>` : ``}
        </div>
      </div>
    `;
  }

  function renderPager(pg){
    const current = pg?.current_page || 1;
    const last    = pg?.last_page    || 1;
    if (last <= 1) { $pager.innerHTML = ''; return; }

    const prevPage = Math.max(1, current - 1);
    const nextPage = Math.min(last, current + 1);
    const prevDis  = current <= 1 ? 'pointer-events-none opacity-50' : '';
    const nextDis  = current >= last ? 'pointer-events-none opacity-50' : '';

    $pager.innerHTML = `
      <div class="flex items-center justify-between border-t border-gray-200 px-2 pt-4" role="navigation" aria-label="Pagination">
        <a href="${buildListUrl(prevPage)}" data-page="${prevPage}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 ${prevDis}">
          ‹ Sebelumnya
        </a>
        <a href="${buildListUrl(nextPage)}" data-page="${nextPage}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 ${nextDis}">
          Selanjutnya ›
        </a>
      </div>`;
  }

  // ====== FETCH ======
  async function fetchOrders(pageOrUrl){
    let url;
    if (typeof pageOrUrl === 'number') url = buildListUrl(pageOrUrl);
    else if (!pageOrUrl) {
      const q = currentQuery();
      url = buildListUrl(q.page || 1);
    } else url = pageOrUrl;

    $list.innerHTML  = skeleton(3);
    $pager.innerHTML = '';

    try {
      const resp = await fetch(url, { headers: { 'Accept':'application/json' } });
      const json = await resp.json();

      if (!json || json.success !== true) {
        $list.innerHTML = emptyView();
        renderPager(json?.pagination);
        banner('Gagal memuat data (format tidak valid).');
        return;
      }

      const orders = json.orders || [];
      const pg     = json.pagination || { current_page:1, last_page:1 };

      currentPage = pg.current_page || 1;
      cache.clear();

      if (!orders.length) {
        $list.innerHTML = emptyView();
        renderPager(pg);
        return;
      }

      $list.innerHTML = orders.map((o, i) => renderItem(o, i===orders.length-1)).join('');
      renderPager(pg);

      // Sync URL (tanpa reload)
      const newUrl = buildListUrl(currentPage);
      const curr   = window.location.pathname + window.location.search;
      if (newUrl !== curr) window.history.pushState({ page: currentPage }, '', newUrl);
    } catch (e) {
      $list.innerHTML = emptyView();
      banner('Gagal memuat data (jaringan/error server).');
    }
  }

  // ====== DETAIL ======
  function renderDetail(order){
    const s = statusView(order.status);
    const items = order.items || [];
    const addr = order.shipping_address || {};
    const rows = items.length
      ? items.map(it => `
        <tr>
          <td class="px-0 py-1 text-sm text-gray-900">${it.name || '-'}</td>
          <td class="px-0 py-1 text-sm text-gray-500">${it.sku || '-'}</td>
          <td class="px-0 py-1 text-sm text-gray-900" align="center">${it.qty ?? 0}</td>
          <td class="px-0 py-1 text-sm text-gray-900" align="right">${fmtIDR(it.unit_price)}</td>
          <td class="px-0 py-1 text-sm text-gray-900" align="right">${fmtIDR(it.row_total)}</td>
        </tr>`).join('')
      : `<tr><td colspan="5" class="py-2 text-center text-sm text-gray-500">Tidak ada item.</td></tr>`;

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
          Tanggal: <span class="font-medium text-gray-900">${fmtDate(order.placed_at || order.created_at)}</span>
          ${paymentInfo}
        </div>
        <span class="inline-flex items-center rounded px-2.5 py-0.5 text-xs font-medium ${s.badge}">${s.label}</span>
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
            <div class="text-gray-500">Subtotal</div><div class="text-right text-gray-900">${fmtIDR(order.subtotal_amount)}</div>
            ${parseFloat(order.discount_amount)>0 ? `<div class="text-gray-500">Diskon</div><div class="text-right text-gray-900">-${fmtIDR(order.discount_amount)}</div>`:''}
            ${parseFloat(order.tax_amount)>0 ? `<div class="text-gray-500">Pajak</div><div class="text-right text-gray-900">${fmtIDR(order.tax_amount)}</div>`:''}
            ${parseFloat(order.shipping_amount)>0 ? `<div class="text-gray-500">Ongkir</div><div class="text-right text-gray-900">${fmtIDR(order.shipping_amount)}</div>`:''}
            <div class="col-span-2 mt-1 border-t pt-2 text-right text-base font-bold text-gray-900">Total: ${fmtIDR(order.grand_total)}</div>
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
          <tbody>${rows}</tbody>
        </table>
      </div>
    `;
  }

  // ====== EVENTS (delegation) ======
  // Item actions
  $list.addEventListener('click', function (e) {
    const a = e.target.closest('a[data-action]');
    if (!a) return;

    const action = a.dataset.action;
    const id     = Number(a.dataset.id);

    if (action === 'detail') {
      e.preventDefault();
      const order = cache.get(id);
      if ($detailTitle) $detailTitle.textContent = `Detail Pesanan ${order?.order_no || ('#'+id)}`;
      if ($detailBody)  $detailBody.innerHTML    = '<div class="py-8 text-center text-sm text-gray-500">Memuat detail...</div>';
      showModal($detailModal, { get ref(){ return flowbiteDetail }, set ref(v){ flowbiteDetail = v; } });

      if (order) {
        $detailBody.innerHTML = renderDetail(order);
      } else {
        // fallback fetch
        fetch(detailUrl(id), { headers: { 'Accept':'application/json' } })
          .then(r=>r.json())
          .then(d=>{
            const data = d?.data || d || {};
            cache.set(id, data);
            $detailBody.innerHTML = renderDetail(data);
          })
          .catch(()=> $detailBody.innerHTML = `<div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">Gagal memuat detail.</div>`);
      }
      return;
    }

    if (action === 'cancel') {
      e.preventDefault();
      const label = a.dataset.label || ('#'+id);
      if ($cancelLabel) $cancelLabel.textContent = label;
      if ($cancelAlert) {
        $cancelAlert.className = 'hidden mt-3';
        $cancelAlert.textContent = '';
      }
      $cancelConfirm.dataset.url = cancelUrl(id);
      showModal($cancelModal, { get ref(){ return flowbiteCancel }, set ref(v){ flowbiteCancel = v; } });
      return;
    }
  });

  if ($detailClose) {
    $detailClose.addEventListener('click', ()=> hideModal($detailModal, { get ref(){ return flowbiteDetail }, set ref(v){ flowbiteDetail = v; } }));
  }
  if ($cancelClose) {
    $cancelClose.addEventListener('click', ()=> hideModal($cancelModal, { get ref(){ return flowbiteCancel }, set ref(v){ flowbiteCancel = v; } }));
  }
  if ($cancelConfirm) {
    $cancelConfirm.addEventListener('click', async function () {
      const url = this.dataset.url;
      if (!url) return;
      // loading
      this.disabled = true;
      if ($cancelSpinner) $cancelSpinner.classList.remove('hidden');

      try {
        const resp = await fetch(url, {
          method: 'DELETE',
          headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const json = await resp.json();
        this.disabled = false;
        if ($cancelSpinner) $cancelSpinner.classList.add('hidden');

        hideModal($cancelModal, { get ref(){ return flowbiteCancel }, set ref(v){ flowbiteCancel = v; } });
        banner((json && (json.message || json.msg)) || 'Pesanan berhasil dibatalkan.', true);
        fetchOrders(currentPage);
      } catch (e) {
        this.disabled = false;
        if ($cancelSpinner) $cancelSpinner.classList.add('hidden');
        if ($cancelAlert) {
          $cancelAlert.className = 'mt-3 rounded border border-red-200 bg-red-50 p-2 text-red-700';
          $cancelAlert.textContent = 'Gagal membatalkan pesanan.';
        }
      }
    });
  }

  // Pagination click
  $pager.addEventListener('click', function (e) {
    const a = e.target.closest('a[data-page]');
    if (!a) return;
    e.preventDefault();
    const page = parseInt(a.dataset.page || '1', 10);
    if (!isNaN(page)) fetchOrders(page);
  });

  // ====== FILTERS (AJAX, no reload) ======
  function periodToRange(val){
    if (!val) return {};
    const now = new Date();
    let from  = new Date();
    if (val==='7days')  from = new Date(now.getTime() -  7*24*60*60*1000);
    if (val==='30days') from = new Date(now.getTime() - 30*24*60*60*1000);
    if (val==='90days') from = new Date(now.getTime() - 90*24*60*60*1000);
    if (val==='1year')  from = new Date(now.getTime() -365*24*60*60*1000);
    const fmt = d => d.toISOString().split('T')[0];
    return { date_from: fmt(from), date_to: fmt(now) };
  }

  function applyFilters(){
    const qNow = currentQuery();
    const q = {
      search   : ($search?.value || '').trim(),
      status   : $status?.value || '',
      per_page : $perPage?.value || qNow.per_page || '',
      page     : 1, // reset ke awal
      ...periodToRange($period?.value || '')
    };
    pushQueryToUrl(q);
    fetchOrders(1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  if ($form) {
    $form.addEventListener('submit', function(e){ e.preventDefault(); applyFilters(); });
  }
  if ($status)  $status.addEventListener('change', applyFilters);
  if ($period)  $period.addEventListener('change', applyFilters);
  if ($perPage) $perPage.addEventListener('change', applyFilters);
  if ($btnReset) {
    $btnReset.addEventListener('click', function(){
      const url = new URL(window.location.href);
      ['search','status','date_from','date_to','page','per_page'].forEach(k => url.searchParams.delete(k));
      window.history.pushState({ page:1 }, '', url.toString());
      if ($form) $form.reset();
      fetchOrders(1);
    });
  }

  // Hydrate filter dari URL saat load
  (function hydrate(){
    const sp = new URLSearchParams(window.location.search);
    if ($search)  $search.value = sp.get('search') || '';
    if ($status)  $status.value = sp.get('status') || '';
    if ($perPage && sp.get('per_page')) $perPage.value = sp.get('per_page');
  })();

  // Back/forward
  window.addEventListener('popstate', function(){ fetchOrders(); });

  // ====== INIT ======
  fetchOrders();
})();
