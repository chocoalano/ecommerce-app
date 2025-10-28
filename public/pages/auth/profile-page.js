/**
 * profile_ajax.js
 * Script jQuery AJAX untuk mengelola formulir di halaman profil (Update Akun, Update Password, Cancel Order).
 * Membutuhkan jQuery, Flowbite (untuk modal), dan token CSRF.
 * URL dinamis diambil dari variabel global window.APP_CONFIG yang didefinisikan di Blade.
 */

// Pastikan jQuery sudah dimuat
if (typeof jQuery === 'undefined') {
    console.error('jQuery is required for profile_ajax.js to run.');
}

// Pastikan konfigurasi aplikasi tersedia
const APP_CONFIG = window.APP_CONFIG || {};

$(document).ready(function() {

    const CSRF_TOKEN = APP_CONFIG.csrfToken || $('meta[name="csrf-token"]').attr('content');

    // Helper: Mendapatkan instance modal Flowbite
    function getFlowbiteModalInstance(elementId) {
        const modalElement = document.getElementById(elementId);
        if (modalElement && typeof Flowbite !== 'undefined' && Flowbite.Modal) {
            return Flowbite.Modal.getInstance(modalElement) || new Flowbite.Modal(modalElement);
        }
        return null;
    }

    // Helper: Menampilkan pesan notifikasi dinamis (sukses atau error)
    function displayFlashMessage(type, message) {
        // Hapus pesan sebelumnya yang dibuat oleh JS (class flash-message-container)
        $('.flash-message-container').remove();

        // Ganti elemen notifikasi bawaan Blade jika ada
        const existingFlash = $('[role="alert"][aria-live="polite"]');
        if (existingFlash.length) {
            existingFlash.remove();
        }

        const isSuccess = (type === 'success');
        const iconPath = isSuccess
            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.731 0 2.813-1.874 1.948-3.374L13.949 3.376a2.25 2.25 0 0 0-3.898 0L2.697 16.502z"/>';

        const borderColor = isSuccess ? 'border-green-200' : 'border-red-200';
        const bgColor = isSuccess ? 'bg-green-50' : 'bg-red-50';
        const textColor = isSuccess ? 'text-green-800' : 'text-red-800';
        const iconColor = isSuccess ? 'text-green-500' : 'text-red-500';

        const flashHtml = `
            <div x-data="{ show: true }" x-show="show" x-transition
                role="alert" aria-live="polite" aria-atomic="true"
                class="flash-message-container mb-4 rounded-lg border ${borderColor} ${bgColor} p-4">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 ${iconColor}" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        ${iconPath}
                    </svg>
                    <p class="text-sm font-medium ${textColor}">
                        ${message}
                    </p>
                    <button type="button" onclick="$(this).closest('.flash-message-container').remove()" aria-label="Tutup"
                            class="ms-auto rounded p-1 text-gray-700/70 hover:bg-gray-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>`;

        // Sisipkan pesan tepat setelah breadcrumbs di kolom utama
        $('.md\\:col-span-9').find('nav[aria-label="Breadcrumb"]').after(flashHtml);

        // Atur agar pesan hilang setelah 8 detik
        setTimeout(() => {
            $('.flash-message-container').slideUp(300, function() {
                $(this).remove();
            });
        }, 8000);
    }

    // Helper: Reset status dan error pada formulir
    function resetFormState(form, errors = {}) {
        // Hapus semua pesan error sebelumnya
        form.find('.error-message').remove();
        form.find('.border-red-500').removeClass('border-red-500');

        // Tambahkan kembali error baru jika ada
        if (Object.keys(errors).length > 0) {
            $.each(errors, function(key, value) {
                // Konversi nama field nested array Laravel (misal: address.line1) menjadi selector
                const safeKey = key.replace('.', '\\[').replace(/(\[\d+\])/g, '');
                let input = form.find(`[name="${safeKey}"]`);

                if (input.length) {
                    input.addClass('border-red-500');
                    input.after(`<p class="error-message mt-2 text-sm text-red-600">${value[0]}</p>`);
                } else {
                    console.error('Field not found for error:', key);
                }
            });
        }
    }

    // ==========================================================
    // 1. AJAX Form Submission: Update Akun
    // Form ID: updateAccountForm
    // Action URL diambil dari atribut 'action' di tag <form>
    // ==========================================================
    $('#accountInformationModal2').on('submit', '#updateAccountForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitButton = form.find('button[type="submit"]');

        submitButton.prop('disabled', true).text('Menyimpan...');
        resetFormState(form);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            success: function(response) {
                displayFlashMessage('success', response.message || 'Informasi akun berhasil diperbarui.');

                // Tutup modal Flowbite
                const modal = getFlowbiteModalInstance('accountInformationModal2');
                if (modal) { modal.hide(); }

                // Muat ulang halaman untuk update data di view
                setTimeout(() => {
                     window.location.reload();
                }, 500);
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                let errors = {};

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                         errors = xhr.responseJSON.errors;
                         errorMessage = xhr.responseJSON.message || 'Validasi gagal. Periksa kembali input Anda.';
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else {
                    errorMessage = 'Kesalahan server tidak diketahui. Status: ' + xhr.status;
                }

                displayFlashMessage('error', errorMessage);
                resetFormState(form, errors);
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });

    // ==========================================================
    // 2. AJAX Form Submission: Update Password
    // Form ID: updatePasswordForm (Asumsi)
    // Action URL diambil dari window.APP_CONFIG.updatePasswordUrl
    // ==========================================================
    // Catatan: Pastikan modal #accountPasswordChangeModal sudah ada di Blade
    $('#accountPasswordChangeModal').on('submit', '#updatePasswordForm', function(e) {
        e.preventDefault();

        if (!APP_CONFIG.updatePasswordUrl) {
            return console.error('Error: updatePasswordUrl is not defined in window.APP_CONFIG.');
        }

        const form = $(this);
        const submitButton = form.find('button[type="submit"]');

        submitButton.prop('disabled', true).text('Mengubah...');
        resetFormState(form);

        $.ajax({
            url: APP_CONFIG.updatePasswordUrl,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            success: function(response) {
                displayFlashMessage('success', response.message || 'Password berhasil diperbarui.');
                form[0].reset(); // Kosongkan form

                // Tutup modal Flowbite
                const modal = getFlowbiteModalInstance('accountPasswordChangeModal');
                if (modal) { modal.hide(); }
            },
            error: function(xhr) {
                let errorMessage = 'Gagal mengubah password.';
                let errors = {};

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                         errors = xhr.responseJSON.errors;
                         errorMessage = xhr.responseJSON.message || 'Validasi gagal. Periksa password lama dan konfirmasi.';
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else {
                    errorMessage = 'Kesalahan server tidak diketahui. Status: ' + xhr.status;
                }

                displayFlashMessage('error', errorMessage);
                resetFormState(form, errors);
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Ubah Password');
            }
        });
    });

    // ==========================================================
    // 3. AJAX Logic: Order Cancellation (Batalkan Pesanan)
    // ==========================================================

    // Langkah 1: Saat tombol "Batalkan pesanan" di dropdown diklik, isikan Order ID & URL ke modal
    $(document).on('click', '.cancel-order-btn', function() {
        const orderId = $(this).data('order-id');
        // Gunakan URL dasar dan tambahkan ID pesanan untuk membuat URL lengkap
        const cancelUrl = (APP_CONFIG.cancelOrderBaseUrl || '/auth/orders') + '/' + orderId + '/cancel';

        // Isi form di modal dengan data yang diperlukan
        $('#deleteOrderModal').find('#cancel_order_id').val(orderId);
        $('#deleteOrderModal').find('#cancelOrderForm').data('action-url', cancelUrl);
        // Hapus pesan error/sukses lama jika ada
        resetFormState($('#cancelOrderForm'));
    });

    // Langkah 2: Tangani submission form pembatalan
    // Form ID: cancelOrderForm (Asumsi)
    $('#deleteOrderModal').on('submit', '#cancelOrderForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const orderId = form.find('#cancel_order_id').val();
        const submitButton = form.find('button[type="submit"]');
        const url = form.data('action-url');

        if (!url || !orderId) {
             return console.error('Error: Order ID or Cancel URL is missing.');
        }

        submitButton.prop('disabled', true).text('Membatalkan...');

        $.ajax({
            url: url,
            type: 'POST',
            // Hanya kirim token dan ID pesanan
            data: {
                _token: CSRF_TOKEN,
                order_id: orderId
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            success: function(response) {
                displayFlashMessage('success', response.message || `Pesanan #${orderId} berhasil dibatalkan.`);

                // Tutup modal Flowbite
                const modal = getFlowbiteModalInstance('deleteOrderModal');
                if (modal) { modal.hide(); }

                // Muat ulang halaman untuk update UI daftar pesanan
                setTimeout(() => {
                     window.location.reload();
                }, 500);
            },
            error: function(xhr) {
                let errorMessage = 'Gagal membatalkan pesanan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint pembatalan pesanan tidak ditemukan.';
                }

                displayFlashMessage('error', errorMessage);
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Ya, Batalkan');
            }
        });
    });

});
