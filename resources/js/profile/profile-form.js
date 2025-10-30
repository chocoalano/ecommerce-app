$(function () {
  const $form = $('#changePasswordForm');
  const $submitButton = $('#submitPasswordButton');
  const $feedbackMessage = $('#pwd-feedback-message');
  const $feedbackContent = $('#pwd-feedback-content');

  // --- Helper function to show/hide feedback messages ---
  function showFeedback(message, isSuccess = true) {
    $feedbackContent.html(message);
    if (isSuccess) {
      $feedbackContent
        .removeClass('bg-red-100 text-red-700 border border-red-200')
        .addClass('bg-green-100 text-green-700 border border-green-200');
    } else {
      $feedbackContent
        .removeClass('bg-green-100 text-green-700 border border-green-200')
        .addClass('bg-red-100 text-red-700 border border-red-200');
    }
    // Menggunakan .stop(true, true) untuk menghindari antrian animasi yang berlebihan
    $feedbackMessage.stop(true, true).slideDown().delay(5000).slideUp();
  }

  // --- Helper function for clearing all validation errors and highlights ---
  function clearErrors() {
    $('.error-message').text('');
    $form.find('input').removeClass('input-error');
  }

  // --- Client-Side Validation Function (Opsional, tapi disarankan) ---
  function validateForm() {
    clearErrors();
    let valid = true;

    const $current = $('#current_password_input');
    const $new = $('#password_input');
    const $confirm = $('#password_confirmation_input');

    const currentVal = ($current.val() || '').trim();
    const newVal = ($new.val() || '').trim();
    const confirmVal = ($confirm.val() || '').trim();

    // Helper untuk menampilkan error
    function setErr($input, errorId, msg) {
      valid = false;
      $input.addClass('input-error');
      $(errorId).text(msg);
    }

    if (!currentVal) setErr($current, '#error-current_password', 'Kolom ini wajib diisi.');
    if (!newVal) setErr($new, '#error-password', 'Kolom ini wajib diisi.');
    if (!confirmVal) setErr($confirm, '#error-password_confirmation', 'Kolom ini wajib diisi.');

    // Cek panjang minimal
    if (newVal && newVal.length < 8) {
        // Hanya set error jika belum ada error "wajib diisi"
        if ($(map['password'].err).text() === '') {
             setErr($new, '#error-password', 'Kata sandi minimal 8 karakter.');
        }
    }

    // Konfirmasi sama
    if (newVal && confirmVal && newVal !== confirmVal) {
      setErr($confirm, '#error-password_confirmation', 'Konfirmasi tidak cocok dengan kata sandi baru.');
    }

    return valid;
  }

  // Hapus error saat user mengetik
  $form.on('input', 'input', function () {
    $(this).removeClass('input-error');
    // Asumsi ID input berakhir dengan '_input' dan ID error-nya adalah '#error-' + nama_field
    const name = $(this).attr('name');
    if (name) {
        $('#error-' + name).text('');
    }
  });

  // --- AJAX Submission Handler ---
  $form.on('submit', function (e) {
    e.preventDefault();

    // Jalankan validasi client-side
    if (!validateForm()) {
      showFeedback('Gagal! Periksa kembali kolom yang ditandai.', false);
      return;
    }

    // Disable button + loading state
    const defaultBtnHtml = $submitButton.html();
    $submitButton.prop('disabled', true).html(`
      <svg class="animate-spin -ms-0.5 me-1.5 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Memproses...
    `);

    const data = $form.serialize();

    $.ajax({
      url: $form.attr('action'),
      type: $form.attr('method') || 'POST',
      data: data,
      dataType: 'json',
      // Penting: Memastikan server merespons dengan JSON, termasuk error 422
      headers: { 'Accept': 'application/json' },

      success: function (resp) {
        showFeedback('Kata sandi berhasil diperbarui!', true);
        $form.trigger('reset'); // Kosongkan form setelah sukses
      },

      error: function (xhr) {
        // Bersihkan error sebelum menampilkan yang baru
        clearErrors();

        let msg = 'Terjadi kesalahan. Silakan coba lagi. (Code: ' + xhr.status + ')';

        // --- Penanganan Validasi Laravel 422 ---
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          const errs = xhr.responseJSON.errors;

          // Mapping nama field input (sesuai respons Laravel) ke ID input dan ID error di HTML
          const map = {
            'current_password': { id: '#current_password_input', err: '#error-current_password' },
            'password': { id: '#password_input', err: '#error-password' },
            'password_confirmation': { id: '#password_confirmation_input', err: '#error-password_confirmation' },
          };

          Object.keys(errs).forEach(function (name) {
            // Pastikan field ada dalam mapping
            if (map[name]) {
              const errorText = Array.isArray(errs[name]) ? errs[name][0] : errs[name];

              // 1. Tambahkan kelas error ke input
              $(map[name].id).addClass('input-error');

              // 2. Tampilkan pesan error pertama (sesuai format respons Laravel)
              $(map[name].err).text(errorText);
            }
          });
          msg = 'Server menolak data. Mohon periksa kembali input Anda.';
        }
        // --- End Penanganan Validasi Laravel 422 ---

        // Penanganan error umum Laravel lainnya
        if (xhr.status === 419) {
          msg = 'Sesi kedaluwarsa (CSRF token hilang). Muat ulang halaman lalu coba lagi.';
        }
        if (xhr.status === 429) {
          msg = 'Terlalu banyak percobaan. Coba beberapa saat lagi.';
        }

        showFeedback(msg, false);
      },

      complete: function () {
        // Kembalikan tombol ke kondisi semula
        $submitButton.prop('disabled', false).html(defaultBtnHtml);
      }
    });
  });
});
