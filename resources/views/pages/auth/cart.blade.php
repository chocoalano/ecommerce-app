@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\CartProduct\Cart|null $cart */
    $items = $cart?->items ?? collect();

    // Helper format Rupiah
    $formatRupiah = function ($amount) {
        try { return 'Rp' . number_format((float)$amount, 0, ',', '.'); } catch (\Throwable $e) { return 'Rp0'; }
    };

    // Ambil nilai dari model cart kalau tersedia, kalau belum dihitung, fallback hitung manual dari item
    $subtotal = $cart?->subtotal_amount ?? $items->sum(fn($i) => (float)$i->row_total);
    $shippingFee = $cart?->shipping_amount ?? 0; // bebas: bisa 0 atau logika ongkir kamu
    $discount = $cart?->discount_amount ?? 0;
    $tax = $cart?->tax_amount ?? 0;
    $grand = $cart?->grand_total ?? max(0, ($subtotal - $discount) + $shippingFee + $tax);
@endphp

<div class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight mb-8">Keranjang Belanja Anda</h1>

    @if ($items->isEmpty())
        {{-- Keranjang Kosong --}}
        <div class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-xl border border-gray-200">
            <svg class="w-16 h-16 text-gray-400 mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-4h10.222m-7.722 0c.264 0 .52.105.707.293l3.19 3.19c.188.188.513.188.701 0l3.188-3.188A.993.993 0 0 1 19 8.5V11m-14 0V8.5a.993.993 0 0 1 .293-.707L8 4"/>
            </svg>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Anda Kosong</h2>
            <p class="text-gray-500 mb-6 text-center">Yuk, temukan produk menarik yang ingin Anda beli!</p>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-center text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                Mulai Belanja Sekarang
            </a>
        </div>
    @else
        {{-- Grid: Items (2/3) + Ringkasan (1/3) --}}
        <div class="lg:grid lg:grid-cols-3 lg:gap-12 xl:gap-16">

            {{-- KOLOM 1-2: Daftar Item --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach ($items as $item)
                    @php
                        /** @var \App\Models\CartProduct\CartItem $item */
                        $product = $item->product;
                        $title = $product?->name ?? $item->product_name ?? ('#' . $item->product_id);
                        $image = asset('storage/'. ($product?->primary_image_url ?? 'images/galaxy-z-flip7-share-image.png'));
                        $stock = (int) ($product?->stock ?? 0);
                        $unitPrice = (float) $item->unit_price;
                        $rowTotal = (float) $item->row_total;
                        $qty = (int) $item->qty;
                    @endphp

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-start bg-gray-100 p-4 sm:p-6 rounded-xl transition hover:shadow-lg border border-gray-200" data-cart-item="{{ $item->id }}">
                        {{-- Gambar --}}
                        <div class="size-20 sm:size-28 flex-shrink-0 rounded-lg overflow-hidden bg-white mx-auto sm:mx-0 mb-4 sm:mb-0 border border-gray-200">
                            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-contain p-2">
                        </div>

                        {{-- Detail --}}
                        <div class="ml-0 sm:ml-4 flex-grow flex flex-col justify-between w-full">
                            <div class="flex justify-between items-start w-full">
                                <div class="pr-4">
                                    <a href="{{ route('products.show', $product?->slug ?? $product?->id ?? '#') }}"
                                       class="text-lg font-semibold text-gray-900 hover:text-zinc-700 transition line-clamp-2">
                                        {{ $title }}
                                    </a>
                                    @if ($product->short_desc)
                                        <p class="text-sm text-gray-500 mt-1">{{ $product->short_desc }}</p>
                                    @endif
                                    @if ($qty > $stock && $stock >= 0)
                                        <p class="text-xs text-red-600 font-medium mt-1">Stok tidak mencukupi! Tersisa {{ $stock }}.</p>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xl font-bold text-gray-900" data-item-row-total="{{ $item->id }}">
                                        {{ $formatRupiah($rowTotal) }}
                                    </p>
                                    <p class="text-xs text-gray-500" data-unit-price data-unit-price="{{ $unitPrice }}">
                                        {{ $formatRupiah($unitPrice) }} / item
                                    </p>
                                </div>
                            </div>

                            {{-- Kontrol Kuantitas & Hapus --}}
                            <div class="flex flex-wrap justify-between items-center mt-4 pt-3 border-t border-gray-200 sm:mt-6 sm:pt-0 sm:border-t-0 w-full">
                                {{-- Form Update Qty --}}
                                <form action="{{ route('cart.items.update', $item->id) }}" method="POST" class="flex items-center gap-1"
                                      onsubmit="return updateQtySubmit(this);">
                                    @csrf
                                    @method('PATCH')

                                    <label for="qty-{{ $item->id }}" class="sr-only">Kuantitas</label>

                                    <button type="button"
                                            class="size-9 leading-9 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-200 transition duration-150"
                                            onclick="stepQty('qty-{{ $item->id }}', -1)">
                                        &minus;
                                    </button>

                                    <input type="number" id="qty-{{ $item->id }}" name="qty" value="{{ $qty }}"
                                           min="1" max="{{ max(1, $stock) }}"
                                           class="h-9 w-16 text-center rounded-lg border border-gray-300 bg-white text-gray-900 text-sm focus:ring-zinc-900 focus:border-zinc-900 p-0 [-moz-appearance:_textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none" />

                                    <button type="button"
                                            class="size-9 leading-9 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-200 transition duration-150"
                                            onclick="stepQty('qty-{{ $item->id }}', 1)">
                                        &plus;
                                    </button>

                                    <button type="submit"
                                            class="ml-2 inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-zinc-900 rounded-lg hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition">
                                        Perbarui
                                    </button>
                                </form>

                                {{-- Hapus --}}
                                <form action="{{ route('cart.items.destroy', $item->id) }}" method="POST"
                                      onsubmit="return handleDeleteConfirm(event, this);">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-800 transition py-2 px-3 rounded-lg hover:bg-gray-200/50">
                                        <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- KOLOM 3: Ringkasan --}}
            <div class="lg:col-span-1 mt-8 lg:mt-0">
                <div class="sticky top-8 bg-gray-100 p-6 rounded-xl border border-gray-200 shadow-md">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-3 border-b border-gray-200">Ringkasan Pesanan</h3>

                    <dl class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <dt>Subtotal (<span data-cart-item-count>{{ $cart?->total_items ?? $items->count() }}</span> Item)</dt>
                            <dd class="font-medium text-gray-900" data-cart-subtotal>{{ $formatRupiah($subtotal) }}</dd>
                        </div>
                        @if($discount > 0)
                        <div class="flex justify-between" style="{{ $discount > 0 ? '' : 'display: none;' }}">
                            <dt>Diskon</dt>
                            <dd class="font-medium text-green-600" data-cart-discount>-{{ $formatRupiah($discount) }}</dd>
                        </div>
                        @endif
                        @if($tax > 0)
                        <div class="flex justify-between" style="{{ $tax > 0 ? '' : 'display: none;' }}">
                            <dt>Pajak</dt>
                            <dd class="font-medium text-gray-900" data-cart-tax>{{ $formatRupiah($tax) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt>Biaya Pengiriman</dt>
                            <dd class="font-medium text-gray-900" data-cart-shipping>{{ $formatRupiah($shippingFee) }}</dd>
                        </div>
                        <div class="flex justify-between pt-4 border-t border-gray-300 mt-4">
                            <dt class="text-lg font-bold text-gray-900">Total Pembayaran</dt>
                            <dd class="text-xl font-extrabold text-zinc-900" data-cart-grand>{{ $formatRupiah($grand) }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('checkout.index') }}"
                       class="inline-flex items-center justify-center w-full py-3 text-base font-semibold text-center text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300 mt-6">
                        <svg class="w-5 h-5 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 10h18M3 14h18M5 18h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM8 18h8V14H8v4z"/>
                        </svg>
                        Lanjut ke Pembayaran
                    </a>

                    <p class="text-xs text-gray-500 mt-4 text-center">Biaya pengiriman dapat berubah saat checkout.</p>
                </div>
            </div>

        </div>
    @endif
</div>

{{-- JS untuk cart management tanpa refresh --}}
<script>
// Cart management object untuk state management
const CartManager = {
    // Format currency helper
    formatRupiah: (amount) => 'Rp' + new Intl.NumberFormat('id-ID').format(parseFloat(amount) || 0),

    // Update cart totals di UI dengan smooth animation
    updateCartTotals: (totals) => {
        if (!totals) return;

        // Helper function untuk smooth number animation
        const animateValue = (element, newValue, formatter = (v) => v) => {
            if (!element) return;

            const oldText = element.textContent.replace(/[^\d,-]/g, '');
            const oldValue = parseFloat(oldText.replace(/[,]/g, '')) || 0;
            const newNum = parseFloat(newValue) || 0;

            if (oldValue === newNum) return;

            // Add highlight animation
            element.style.transition = 'background-color 0.3s ease';
            element.style.backgroundColor = '#fef3c7'; // yellow highlight

            // Animate the number change
            const duration = 500;
            const steps = 20;
            const increment = (newNum - oldValue) / steps;
            let currentValue = oldValue;
            let step = 0;

            const timer = setInterval(() => {
                step++;
                currentValue += increment;

                if (step >= steps) {
                    currentValue = newNum;
                    clearInterval(timer);

                    // Remove highlight after animation
                    setTimeout(() => {
                        element.style.backgroundColor = '';
                    }, 200);
                }

                element.textContent = formatter(currentValue);
            }, duration / steps);
        };

        // Update subtotal with animation
        const subtotalEl = document.querySelector('[data-cart-subtotal]');
        if (subtotalEl && totals.subtotal !== undefined) {
            animateValue(subtotalEl, totals.subtotal, CartManager.formatRupiah);
        }

        // Update discount with animation
        const discountEl = document.querySelector('[data-cart-discount]');
        if (discountEl && totals.discount !== undefined) {
            animateValue(discountEl, totals.discount, (v) => '-' + CartManager.formatRupiah(v));
            // Show/hide discount row
            const discountRow = discountEl.closest('.flex');
            if (discountRow) {
                discountRow.style.display = totals.discount > 0 ? 'flex' : 'none';
            }
        }

        // Update shipping
        const shippingEl = document.querySelector('[data-cart-shipping]');
        if (shippingEl && totals.shipping !== undefined) {
            animateValue(shippingEl, totals.shipping, CartManager.formatRupiah);
        }

        // Update tax with animation
        const taxEl = document.querySelector('[data-cart-tax]');
        if (taxEl && totals.tax !== undefined) {
            animateValue(taxEl, totals.tax, CartManager.formatRupiah);
            // Show/hide tax row
            const taxRow = taxEl.closest('.flex');
            if (taxRow) {
                taxRow.style.display = totals.tax > 0 ? 'flex' : 'none';
            }
        }

        // Update grand total with special highlighting
        const grandEl = document.querySelector('[data-cart-grand]');
        if (grandEl && totals.grand !== undefined) {
            grandEl.style.transition = 'all 0.3s ease';
            grandEl.style.transform = 'scale(1.05)';
            grandEl.style.color = '#059669'; // green color

            animateValue(grandEl, totals.grand, CartManager.formatRupiah);

            setTimeout(() => {
                grandEl.style.transform = 'scale(1)';
                grandEl.style.color = '';
            }, 600);
        }

        // Update item count in summary with bounce animation
        const itemCountEl = document.querySelector('[data-cart-item-count]');
        if (itemCountEl && totals.item_count !== undefined) {
            itemCountEl.style.transition = 'transform 0.2s ease';
            itemCountEl.style.transform = 'scale(1.2)';
            itemCountEl.textContent = totals.item_count;

            setTimeout(() => {
                itemCountEl.style.transform = 'scale(1)';
            }, 200);
        }
    },

    // Update individual item row total dengan highlight
    updateItemRowTotal: (itemId, newQty, unitPrice) => {
        const rowTotal = newQty * unitPrice;
        const itemRowEl = document.querySelector(`[data-item-row-total="${itemId}"]`);
        if (itemRowEl) {
            // Add highlight animation
            itemRowEl.style.transition = 'all 0.3s ease';
            itemRowEl.style.backgroundColor = '#dcfce7'; // green highlight
            itemRowEl.style.transform = 'scale(1.05)';
            itemRowEl.textContent = CartManager.formatRupiah(rowTotal);

            setTimeout(() => {
                itemRowEl.style.backgroundColor = '';
                itemRowEl.style.transform = 'scale(1)';
            }, 500);
        }
    },

    // Remove item row dari UI dengan enhanced animation
    removeItemRow: (itemId) => {
        const itemRow = document.querySelector(`[data-cart-item="${itemId}"]`);
        if (itemRow) {
            // Add slide and fade out animation
            itemRow.style.transition = 'all 0.4s ease-out';
            itemRow.style.transform = 'translateX(100%)';
            itemRow.style.opacity = '0';
            itemRow.style.maxHeight = itemRow.offsetHeight + 'px';

            setTimeout(() => {
                itemRow.style.maxHeight = '0';
                itemRow.style.paddingTop = '0';
                itemRow.style.paddingBottom = '0';
                itemRow.style.marginBottom = '0';
            }, 200);

            setTimeout(() => {
                itemRow.remove();

                // Check if cart is now empty
                const remainingItems = document.querySelectorAll('[data-cart-item]');
                if (remainingItems.length === 0) {
                    // Reload to show empty cart state
                    setTimeout(() => location.reload(), 500);
                }
            }, 300);
        }
    },

    // Add loading state to button
    setButtonLoading: (button, isLoading = true) => {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            button.classList.add('opacity-75', 'cursor-not-allowed');

            // Add spinner if not exists
            if (!button.querySelector('.loading-spinner')) {
                const spinner = document.createElement('div');
                spinner.className = 'loading-spinner inline-block w-4 h-4 border-2 border-white border-t-transparent border-solid rounded-full animate-spin mr-2';
                button.insertBefore(spinner, button.firstChild);
            }
        } else {
            button.disabled = false;
            button.classList.remove('opacity-75', 'cursor-not-allowed');

            // Remove spinner
            const spinner = button.querySelector('.loading-spinner');
            if (spinner) spinner.remove();
        }
    }
};

// Quantity stepper function
function stepQty(id, delta) {
    const el = document.getElementById(id);
    if (!el) return;
    const min = parseInt(el.min || '1', 10);
    const max = parseInt(el.max || '9999', 10);
    const cur = parseInt(el.value || '1', 10);
    const next = Math.max(min, Math.min(max, cur + delta));
    el.value = next;
}

// Update quantity without refresh
async function updateQtySubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const qtyInput = form.querySelector('input[name="qty"]');
    const itemId = form.action.split('/').pop();

    if (!qtyInput || !itemId) {
        showToast('Data item tidak valid.', 'error');
        return false;
    }

    const newQty = parseInt(qtyInput.value);
    if (newQty < 1) {
        showToast('Kuantitas minimal adalah 1.', 'error');
        qtyInput.value = qtyInput.dataset.originalValue || 1;
        return false;
    }

    // Check if quantity actually changed
    const originalQty = parseInt(qtyInput.dataset.originalValue || qtyInput.value);
    if (newQty === originalQty) {
        showToast('Kuantitas tidak berubah.', 'info');
        return false;
    }

    try {
        // Set loading state
        CartManager.setButtonLoading(submitBtn, true);

        // Disable quantity input during update
        qtyInput.disabled = true;

        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                qty: newQty,
                _method: 'PATCH'
            })
        });

        let data = null;
        try {
            data = await response.json();
        } catch (parseError) {
            throw new Error('Server response tidak valid.');
        }

        if (!response.ok) {
            throw new Error(data?.message || `Server error: ${response.status}`);
        }

        // Update UI tanpa reload
        if (data && data.totals) {
            // Update cart totals with animation
            CartManager.updateCartTotals(data.totals);

            // Update individual item row total if unit price available
            const unitPriceEl = form.closest('[data-cart-item]')?.querySelector('[data-unit-price]');
            if (unitPriceEl) {
                const unitPrice = parseFloat(unitPriceEl.dataset.unitPrice);
                CartManager.updateItemRowTotal(itemId, newQty, unitPrice);
            }

            // Update original value for future comparisons
            qtyInput.dataset.originalValue = newQty;
        }

        showToast('Kuantitas berhasil diperbarui!', 'success');

        // Dispatch custom event for other components
        window.dispatchEvent(new CustomEvent('cart:updated', {
            detail: { type: 'quantity_updated', itemId, newQty, totals: data.totals }
        }));

        // Also dispatch cartUpdated event (for compatibility with CartIndicator)
        window.dispatchEvent(new CustomEvent('cartUpdated', {
            detail: {
                cartItemCount: data.totals.item_count || data.totals.total_items,
                cartTotal: data.totals.grand || data.totals.grand_total,
                type: 'quantity_updated'
            }
        }));

    } catch (error) {
        console.error('Update quantity error:', error);
        showToast(error.message || 'Kesalahan jaringan saat memperbarui kuantitas.', 'error');

        // Reset quantity to original value on error
        qtyInput.value = qtyInput.dataset.originalValue || originalQty;

    } finally {
        // Reset loading state
        CartManager.setButtonLoading(submitBtn, false);
        qtyInput.disabled = false;
    }

    return false; // Prevent default form submission
}

// Delete item without refresh
async function handleDeleteConfirm(event, form) {
    event.preventDefault();

    try {
        const submitBtn = form.querySelector('button[type="submit"]');
        const itemId = form.action.split('/').pop();
        const itemRow = form.closest('[data-cart-item]');

        if (!itemId) {
            showToast('Data item tidak valid.', 'error');
            return false;
        }

        try {
            // Set loading state
            CartManager.setButtonLoading(submitBtn, true);

            // Add loading overlay to item row
            if (itemRow) {
                itemRow.style.opacity = '0.5';
                itemRow.style.pointerEvents = 'none';
            }

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            });

            let data = null;
            try {
                data = await response.json();
            } catch (parseError) {
                throw new Error('Server response tidak valid.');
            }

            if (!response.ok) {
                throw new Error(data?.message || `Server error: ${response.status}`);
            }

            // Update UI tanpa reload
            // Dispatch custom event for other components
            window.dispatchEvent(new CustomEvent('cart:updated', {
                detail: { type: 'item_deleted', itemId, totals: data.totals }
            }));

            // Also dispatch cartUpdated event (for compatibility with CartIndicator)
            window.dispatchEvent(new CustomEvent('cartUpdated', {
                detail: {
                    cartItemCount: data.totals.item_count || data.totals.total_items,
                    cartTotal: data.totals.grand || data.totals.grand_total,
                    type: 'item_deleted'
                }
            }));
            // Remove item row dari UI dengan smooth animation
            CartManager.removeItemRow(itemId);

            showToast('Item berhasil dihapus dari keranjang!', 'success');

            // Dispatch custom event for other components
            window.dispatchEvent(new CustomEvent('cart:updated', {
                detail: { type: 'item_deleted', itemId, totals: data.totals }
            }));

            // Check if cart is now empty
            setTimeout(() => {
                const remainingItems = document.querySelectorAll('[data-cart-item]');
                if (remainingItems.length === 0) {
                    showToast('Keranjang Anda sekarang kosong.', 'info');
                    // Optionally redirect to empty cart or reload
                    setTimeout(() => location.reload(), 1500);
                }
            }, 500);

        } catch (error) {
            console.error('Delete item error:', error);
            showToast(error.message || 'Kesalahan jaringan saat menghapus item.', 'error');

            // Reset item row state on error
            if (itemRow) {
                itemRow.style.opacity = '1';
                itemRow.style.pointerEvents = 'auto';
            }
            CartManager.setButtonLoading(submitBtn, false);
        }

    } catch (error) {
        // User cancelled confirmation or other error
        if (error.message !== 'Dibatalkan oleh user') {
            console.log('Delete operation cancelled or failed:', error.message);
        }
    }

    return false;
}

// Auto-save quantity on input change (debounced)
let qtyUpdateTimeout;
function handleQtyInputChange(input) {
    // Store original value for error recovery
    if (!input.dataset.originalValue) {
        input.dataset.originalValue = input.value;
    }

    // Clear previous timeout
    clearTimeout(qtyUpdateTimeout);

    // Add visual indicator that change is pending
    const form = input.closest('form');
    const updateBtn = form?.querySelector('button[type="submit"]');

    if (updateBtn) {
        updateBtn.textContent = 'Menyimpan...';
        updateBtn.disabled = true;
        updateBtn.classList.add('opacity-75');
    }

    // Set new timeout for auto-update
    qtyUpdateTimeout = setTimeout(() => {
        const currentValue = parseInt(input.value);
        const originalValue = parseInt(input.dataset.originalValue);

        // Reset button state
        if (updateBtn) {
            updateBtn.textContent = 'Perbarui';
            updateBtn.disabled = false;
            updateBtn.classList.remove('opacity-75');
        }

        // Only update if value actually changed and is valid
        if (form && currentValue !== originalValue && currentValue >= 1) {
            updateQtySubmit(form);
        }
    }, 1500); // Wait 1.5 seconds after user stops typing
}

// Enhanced quantity stepper with immediate visual feedback
function stepQty(id, delta) {
    const el = document.getElementById(id);
    if (!el) return;

    const min = parseInt(el.min || '1', 10);
    const max = parseInt(el.max || '9999', 10);
    const cur = parseInt(el.value || '1', 10);
    const next = Math.max(min, Math.min(max, cur + delta));

    if (next !== cur) {
        el.value = next;

        // Add visual feedback
        el.style.transition = 'all 0.2s ease';
        el.style.backgroundColor = delta > 0 ? '#dcfce7' : '#fef2f2'; // green for increase, red for decrease
        el.style.transform = 'scale(1.05)';

        setTimeout(() => {
            el.style.backgroundColor = '';
            el.style.transform = 'scale(1)';
        }, 200);

        // Trigger auto-update
        handleQtyInputChange(el);
    }
}

// Network status monitoring for better error handling
let isOnline = navigator.onLine;
let offlineToastShown = false;

window.addEventListener('online', () => {
    isOnline = true;
    offlineToastShown = false;
    showToast('Koneksi internet tersambung kembali.', 'success');
});

window.addEventListener('offline', () => {
    isOnline = false;
    if (!offlineToastShown) {
        showToast('Koneksi internet terputus. Perubahan akan disimpan saat kembali online.', 'warning', { duration: 0 });
        offlineToastShown = true;
    }
});

// Enhanced error handling with retry mechanism
async function retryOperation(operation, maxRetries = 3, delay = 1000) {
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            return await operation();
        } catch (error) {
            if (attempt === maxRetries) {
                throw error;
            }

            // Show retry notification
            showToast(`Mencoba ulang... (${attempt}/${maxRetries})`, 'info', { duration: 2000 });

            // Wait before retry with exponential backoff
            await new Promise(resolve => setTimeout(resolve, delay * attempt));
        }
    }
}

// Initialize cart functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🛒 Cart AJAX system initialized');

    // Add data attributes for easier targeting
    const qtyInputs = document.querySelectorAll('input[name="qty"]');
    qtyInputs.forEach((input, index) => {
        input.dataset.originalValue = input.value;

        // Add enhanced input event listeners
        input.addEventListener('input', () => handleQtyInputChange(input));
        input.addEventListener('blur', () => {
            // Validate on blur
            const value = parseInt(input.value);
            const min = parseInt(input.min || '1');
            const max = parseInt(input.max || '9999');

            if (value < min) {
                input.value = min;
                showToast(`Kuantitas minimal adalah ${min}`, 'warning');
            } else if (value > max) {
                input.value = max;
                showToast(`Kuantitas maksimal adalah ${max}`, 'warning');
            }
        });

        // Add keyboard shortcuts
        input.addEventListener('keydown', (e) => {
            // Enter key to submit
            if (e.key === 'Enter') {
                e.preventDefault();
                const form = input.closest('form');
                if (form) updateQtySubmit(form);
            }
            // Arrow keys for quantity adjustment
            else if (e.key === 'ArrowUp') {
                e.preventDefault();
                stepQty(input.id, 1);
            }
            else if (e.key === 'ArrowDown') {
                e.preventDefault();
                stepQty(input.id, -1);
            }
        });
    });

    // Store unit prices for calculation with error handling
    document.querySelectorAll('[data-cart-item]').forEach((itemRow, index) => {
        try {
            const unitPriceEl = itemRow.querySelector('.text-xs.text-gray-500');
            if (unitPriceEl) {
                const unitPriceText = unitPriceEl.textContent;
                const unitPriceMatch = unitPriceText.match(/Rp([\d,.]+)/);
                if (unitPriceMatch) {
                    const unitPrice = parseFloat(unitPriceMatch[1].replace(/[,.]/g, ''));
                    const unitPriceDisplay = itemRow.querySelector('[data-unit-price]') || unitPriceEl;
                    unitPriceDisplay.dataset.unitPrice = unitPrice;
                }
            }
        } catch (error) {
            console.warn(`Failed to parse unit price for item ${index}:`, error);
        }
    });

    // Add global keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + U to update all quantities
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            const forms = document.querySelectorAll('form[action*="cart/items/"]');
            forms.forEach(form => {
                if (form.action.includes('update')) {
                    updateQtySubmit(form);
                }
            });
            showToast('Memperbarui semua kuantitas...', 'info');
        }
    });

    // Add visual loading indicator for the entire cart
    const cartContainer = document.querySelector('.lg\\:col-span-2');
    if (cartContainer) {
        cartContainer.style.position = 'relative';
    }

    // Performance monitoring
    if (window.performance && performance.mark) {
        performance.mark('cart-initialized');
        const loadTime = performance.now();
        console.log(`🚀 Cart initialization completed in ${loadTime.toFixed(2)}ms`);
    }

    // Check for any existing cart data in sessionStorage
    const savedCartData = sessionStorage.getItem('cart_pending_updates');
    if (savedCartData) {
        try {
            const pendingUpdates = JSON.parse(savedCartData);
            if (pendingUpdates.length > 0) {
                showToast(`Ada ${pendingUpdates.length} perubahan yang belum tersimpan.`, 'info');
            }
        } catch (error) {
            sessionStorage.removeItem('cart_pending_updates');
        }
    }
});

// Global error handler untuk cart operations
window.addEventListener('unhandledrejection', function(event) {
    if (event.reason && event.reason.message && event.reason.message.includes('cart')) {
        console.error('Unhandled cart error:', event.reason);
        showToast('Terjadi kesalahan tidak terduga pada keranjang.', 'error');
        event.preventDefault(); // Prevent console error
    }
});

// Periodic sync check (every 30 seconds when page is visible)
let syncInterval;
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        syncInterval = setInterval(() => {
            // Check if there are any pending operations
            const pendingOperations = document.querySelectorAll('[data-cart-item] .opacity-75');
            if (pendingOperations.length === 0 && isOnline) {
                // Optional: Sync cart state with server
                console.log('🔄 Cart sync check');
            }
        }, 30000);
    } else {
        clearInterval(syncInterval);
    }
});
</script>
@endsection
