/**
 * Helper: Mengkonversi angka menjadi format Rupiah.
 */
function formatRupiah(number) {
    if (isNaN(number)) number = 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

// ----------------------------------------------------------------------
// 2. FUNGSI UTILITY UPDATE UI
// ----------------------------------------------------------------------

/**
 * Memperbarui total harga di baris item yang diubah.
 */
function updateItemRowTotal(itemId, newTotal) {
    $(`[data-item-row-total="${itemId}"]`).text(formatRupiah(newTotal));
}

/**
 * Memperbarui elemen-elemen di div Ringkasan Pesanan.
 */
function updateOrderSummary(totals) {
    if (!totals) {
        console.error('Data totals tidak ditemukan.');
        return;
    }

    // Pastikan nilai adalah numerik (untuk berjaga-jaga jika respons JSON adalah string)
    const subtotal = parseFloat(totals.subtotal);
    const discount = parseFloat(totals.discount);
    const shipping = parseFloat(totals.shipping);
    const tax = parseFloat(totals.tax);
    const grand = parseFloat(totals.grand);
    const item_count = parseInt(totals.item_count);

    // 1. Update Item Count
    $('[data-cart-item-count]').text(item_count);

    // 2. Update Subtotal
    $('[data-cart-subtotal]').text(formatRupiah(subtotal));

    // 3. Update Discount
    const $discountRow = $('[data-cart-discount]').closest('.flex');
    if (discount > 0) {
        $discountRow.show();
        $('[data-cart-discount]').text('-' + formatRupiah(discount));
    } else {
        $discountRow.hide();
    }

    // 4. Update Tax
    const $taxRow = $('[data-cart-tax]').closest('.flex');
    if (tax > 0) {
        $taxRow.show();
        $('[data-cart-tax]').text(formatRupiah(tax));
    } else {
        $taxRow.hide();
    }

    // 5. Update Shipping Fee
    $('[data-cart-shipping]').text(formatRupiah(shipping));

    // 6. Update Grand Total
    $('[data-cart-grand]').text(formatRupiah(grand));

    // PENTING: Perbarui variabel global ongkir agar perhitungan selanjutnya benar
    window.currentSubtotal = subtotal;
    window.currentDiscount = discount;
    window.currentTax = tax;
}


// ----------------------------------------------------------------------
// 3. FUNGSI AJAX CORE (Update Qty & Delete)
// ----------------------------------------------------------------------

/**
 * Menangani pengiriman form AJAX menggunakan jQuery.ajax().
 */
function submitCartForm($form, method, isDelete = false) {
    const $itemElement = $form.closest('[data-cart-item]');

    // Tampilkan loading visual pada item yang diproses
    $itemElement.addClass('opacity-50 pointer-events-none');

    $.ajax({
        url: $form.attr('action'),
        type: method,
        data: $form.serialize(), // Mengambil data input, termasuk _token dan _method
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success && data.totals) {
                if (isDelete) {
                    // Hapus elemen item dan cek keranjang kosong
                    $itemElement.remove();
                    if ($('.lg\\:col-span-2.space-y-6').children('[data-cart-item]').length === 0) {
                         window.location.reload();
                         return;
                    }
                } else if (data.item) {
                    // Update Total Baris Item
                    updateItemRowTotal(data.item.id, data.item.row_total);
                }

                // Update Ringkasan dan panggil perhitungan ongkir ulang
                updateOrderSummary(data.totals);
                fetchShippingCosts(); // Hitung ulang ongkir (untuk memastikan total Grand benar)
            } else {
                alert(data.message || 'Gagal memproses permintaan.');
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.responseJSON || xhr.responseText);
            const errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan server.';
            alert(errorMessage);
        },
        complete: function() {
            // Sembunyikan loading
            $itemElement.removeClass('opacity-50 pointer-events-none');
        }
    });
}

// ----------------------------------------------------------------------
// 4. LOGIKA ONGKOS KIRIM (RAJAONGKIR)
// ----------------------------------------------------------------------

/**
 * Mengambil daftar kota/kabupaten berdasarkan ID Provinsi yang dipilih.
 */
function fetchCities() {
    const provinceId = $('#shipping-address-province').val();
    const $citySelect = $('#shipping-address-city');

    $citySelect.html('<option value="" selected>Memuat Kota...</option>').prop('disabled', true);
    $('#shipping-service').hide();
    updateShippingFee(0);

    if (!provinceId) {
        $citySelect.html('<option value="" selected>Pilih Kota Tujuan</option>').prop('disabled', false);
        return;
    }

    // Ganti URL ini dengan endpoint API Anda untuk mengambil kota
    // Asumsi: /api/rajaongkir/cities/PROVINCE_ID
    $.get(`/api/rajaongkir/cities/${provinceId}`)
        .done(function(data) {
            $citySelect.html('<option value="" selected>Pilih Kota Tujuan</option>');
            if (data.success && data.cities) {
                data.cities.forEach(city => {
                    $citySelect.append(`<option value="${city.city_id}">${city.type} ${city.city_name}</option>`);
                });
            } else {
                 $citySelect.html('<option value="" selected>Kota tidak ditemukan</option>');
            }
        })
        .fail(function() {
            $citySelect.html('<option value="" selected>Gagal memuat kota</option>');
        })
        .always(function() {
            $citySelect.prop('disabled', false);
        });
}

/**
 * Mengambil biaya pengiriman (costs) dari RajaOngkir.
 */
function fetchShippingCosts() {
    const destinationCityId = $('#shipping-address-city').val();
    const courierCode = $('#shipping-courier').val();
    const $serviceSelect = $('#shipping-service');

    // Pastikan kota dan kurir sudah dipilih, dan berat cart tersedia
    if (!destinationCityId || !courierCode || window.cartWeight <= 0) {
         $serviceSelect.hide().html('<option value="" selected>Pilih Layanan</option>');
         updateShippingFee(0);
         return;
    }

    $serviceSelect.show().html('<option value="" selected>Memuat Layanan...</option>').prop('disabled', true);
    updateShippingFee(0);

    // Ganti URL ini dengan endpoint API Anda untuk menghitung ongkir
    // Asumsi: POST /api/rajaongkir/cost
    $.ajax({
        url: '/api/rajaongkir/cost',
        type: 'POST',
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: JSON.stringify({
            // Asumsi origin_city_id disuntikkan dari PHP
            origin: window.originCityId,
            destination: destinationCityId,
            weight: window.cartWeight,
            courier: courierCode
        }),
        contentType: 'application/json', // Penting untuk kirim JSON

        success: function(response) {
            $serviceSelect.html('<option value="" selected>Pilih Layanan</option>');
            if (response.success && response.costs && response.costs.length > 0) {
                response.costs.forEach(costGroup => {
                    costGroup.costs.forEach(service => {
                        const price = service.cost[0].value;
                        const formattedPrice = formatRupiah(price);

                        $serviceSelect.append(
                            `<option value="${price}">
                                ${service.service} (${service.description}) - ${formattedPrice} (${service.cost[0].etd} hari)
                            </option>`
                        );
                    });
                });
            } else {
                $serviceSelect.html('<option value="" selected>Tidak ada layanan tersedia.</option>');
            }
        },
        error: function(xhr) {
            console.error('Gagal mengambil biaya pengiriman:', xhr.responseText);
            $serviceSelect.html('<option value="" selected>Gagal memuat layanan</option>');
        },
        complete: function() {
            $serviceSelect.prop('disabled', false);
        }
    });
}

/**
 * Memperbarui Biaya Pengiriman dan Total Pembayaran.
 */
function updateShippingFee(cost) {
    window.selectedShippingCost = parseFloat(cost) || 0;

    const newGrandTotal = (window.currentSubtotal - window.currentDiscount) + window.selectedShippingCost + window.currentTax;

    // Panggil fungsi global untuk memperbarui UI
    updateOrderSummary({
        subtotal: window.currentSubtotal,
        discount: window.currentDiscount,
        shipping: window.selectedShippingCost,
        tax: window.currentTax,
        grand: newGrandTotal,
        item_count: $('[data-cart-item]').length
    });
}

// ----------------------------------------------------------------------
// 5. INISIALISASI DAN EVENT HANDLER UTAMA
// ----------------------------------------------------------------------

$(document).ready(function() {
    // -----------------------------------------------------
    // A. Item Cart Handlers (Update Qty & Delete)
    // -----------------------------------------------------

    // Event Delegation: Tombol Plus/Minus Qty
    $('.lg\\:col-span-2').on('click', 'button', function(e) {
        // Cek apakah tombol ini adalah tombol Qty (+/-)
        const $button = $(this);
        const step = $button.text().trim() === '+' ? 1 : ($button.text().trim() === '−' ? -1 : 0);

        if (step !== 0) {
            e.preventDefault();
            const $form = $button.closest('form');
            const $input = $form.find('input[name="qty"]');

            let currentValue = parseInt($input.val(), 10) || 0;
            let newValue = currentValue + step;

            const min = parseInt($input.attr('min'), 10) || 1;
            const max = parseInt($input.attr('max'), 10) || Infinity;

            newValue = Math.max(min, Math.min(max, newValue));

            if (newValue !== currentValue) {
                $input.val(newValue);
                // Langsung submit form AJAX
                submitCartForm($form, 'PATCH', false);
            }
        }
    });

    // Event Delegation: Input Qty berubah (diketik)
    $('.lg\\:col-span-2').on('change', 'input[name="qty"]', function() {
        const $input = $(this);
        const $form = $input.closest('form');

        // Sanitasi nilai input
        let currentValue = parseInt($input.val(), 10) || 1;
        const min = parseInt($input.attr('min'), 10) || 1;
        const max = parseInt($input.attr('max'), 10) || Infinity;

        currentValue = Math.max(min, Math.min(max, currentValue));
        $input.val(currentValue); // Setel nilai yang sudah divalidasi

        submitCartForm($form, 'PATCH', false);
    });

    // Event Delegation: Hapus Item (Form submit)
    $('.lg\\:col-span-2').on('submit', 'form[method="POST"]', function(e) {
        const $form = $(this);
        // Cek apakah form ini adalah form DELETE (mengandung input _method=DELETE)
        if ($form.find('input[name="_method"][value="DELETE"]').length > 0) {
             e.preventDefault();
             if (confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
                 submitCartForm($form, 'DELETE', true);
             }
        }
        // Biarkan form update qty berjalan normal (tapi sudah di-handle via change/click di atas)
    });

    // -----------------------------------------------------
    // B. Ongkos Kirim Handlers
    // -----------------------------------------------------

    // 1. Provinsi berubah -> Muat Kota
    $('#shipping-address-province').on('change', function() {
        fetchCities();
    });

    // 2. Kota berubah -> Cek dan Muat Biaya Kurir (hanya jika kurir sudah dipilih)
    $('#shipping-address-city').on('change', function() {
        if ($('#shipping-courier').val()) {
            fetchShippingCosts();
        } else {
             $('#shipping-service').hide();
             updateShippingFee(0);
        }
    });

    // 3. Kurir berubah -> Muat Biaya Layanan
    $('#shipping-courier').on('change', function() {
        fetchShippingCosts();
    });

    // 4. Layanan berubah -> Update Biaya Pengiriman & Total
    $('#shipping-service').on('change', function() {
        updateShippingFee($(this).val());
    });
});
