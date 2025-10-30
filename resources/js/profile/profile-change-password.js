$(function () {
  const $form = $('#changePasswordForm');
  const $submitButton = $('#submitPasswordButton');
  const $feedbackMessage = $('#pwd-feedback-message');
  const $feedbackContent = $('#pwd-feedback-content');

  // --- Translator key Laravel -> ID (opsional)
  const I18N = {
    'validation.current_password': 'Kata sandi saat ini tidak sesuai.',
    'validation.required': 'Kolom ini wajib diisi.',
    'validation.confirmed': 'Konfirmasi kata sandi tidak cocok.',
    'validation.min.string': 'Minimal 8 karakter.',
    'validation.password.letters': 'Kata sandi baru harus mengandung huruf.',
    'validation.password.mixed': 'Kata sandi baru harus mengandung huruf besar & kecil.',
    'validation.password.numbers': 'Kata sandi baru harus mengandung angka.',
    'validation.password.symbols': 'Kata sandi baru harus mengandung simbol.',
    'validation.password.uncompromised': 'Kata sandi ini pernah terlibat kebocoran data. Gunakan yang lain.',
  };
  const t = (key) => I18N[key] || key;

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
    $feedbackMessage.stop(true, true).slideDown().delay(5000).slideUp();
  }

  function clearErrors() {
    $('.error-message').text('');
    $form.find('input').removeClass('input-error');
  }

  function setLoading(isLoading) {
    if (isLoading) {
      if (!$submitButton.data('default-html')) {
        $submitButton.data('default-html', $submitButton.html());
      }
      $submitButton.prop('disabled', true).html(`
        <svg class="animate-spin -ms-0.5 me-1.5 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Memproses...
      `);
    } else {
      $submitButton.prop('disabled', false);
    }
  }

  // Validasi front-end singkat
  function validateForm() {
    clearErrors();
    let valid = true;

    const $current = $('#current_password_input');
    const $new = $('#password_input');
    const $confirm = $('#password_confirmation_input');

    const currentVal = ($current.val() || '').trim();
    const newVal = ($new.val() || '').trim();
    const confirmVal = ($confirm.val() || '').trim();

    const map = {
      current_password: { $el: $current, err: '#error-current_password' },
      password: { $el: $new, err: '#error-password' },
      password_confirmation: { $el: $confirm, err: '#error-password_confirmation' },
    };
    const setErr = ($input, errorId, msg) => { valid = false; $input.addClass('input-error'); $(errorId).text(msg); };

    if (!currentVal) setErr(map.current_password.$el, map.current_password.err, t('validation.required'));
    if (!newVal) setErr(map.password.$el, map.password.err, t('validation.required'));
    if (!confirmVal) setErr(map.password_confirmation.$el, map.password_confirmation.err, t('validation.required'));
    if (newVal && newVal.length < 8 && !$(map.password.err).text()) setErr(map.password.$el, map.password.err, t('validation.min.string'));
    if (newVal && confirmVal && newVal !== confirmVal && !$(map.password_confirmation.err).text()) setErr(map.password_confirmation.$el, map.password_confirmation.err, t('validation.confirmed'));

    return valid;
  }

  // Bersihkan error saat mengetik
  $form.on('input', 'input', function () {
    $(this).removeClass('input-error');
    const name = $(this).attr('name'); // ex: "password_confirmation"
    if (name) $('#error-' + name).text('');
  });

  function renderServerErrors(xhr) {
    const errs = xhr.responseJSON && xhr.responseJSON.errors;
    if (!errs) return;

    const map = {
      current_password: { input: '#current_password_input', err: '#error-current_password' },
      password: { input: '#password_input', err: '#error-password' },
      password_confirmation: { input: '#password_confirmation_input', err: '#error-password_confirmation' },
    };

    Object.keys(errs).forEach((field) => {
      const conf = map[field];
      if (!conf) return;
      const arr = Array.isArray(errs[field]) ? errs[field] : [errs[field]];
      const rendered = arr.map((item) => typeof item === 'string' ? t(item) : String(item));
      $(conf.input).addClass('input-error');
      $(conf.err).html(rendered.join('<br>'));
    });
  }

  $form.on('submit', function (e) {
    e.preventDefault();

    if (!validateForm()) {
      showFeedback('Gagal! Periksa kembali kolom yang ditandai.', false);
      return;
    }

    $.ajax({
      url: $form.attr('action'),
      type: $form.attr('method') || 'POST',
      data: $form.serialize(),
      dataType: 'json',
      timeout: 20000, // jaga-jaga agar tidak loading selamanya
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept-Language': 'id',
      },
      beforeSend: function () {
        setLoading(true);
      },
      success: function () {
        showFeedback('Kata sandi berhasil diperbarui!', true);
        $form.trigger('reset');
      },
      error: function (xhr) {
        clearErrors();

        let msg = 'Terjadi kesalahan. Silakan coba lagi. (Code: ' + xhr.status + ')';
        if (xhr.status === 422) {
          renderServerErrors(xhr);
          msg = 'Server menolak data. Mohon periksa kembali input Anda.';
        } else if (xhr.status === 419) {
          msg = 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        } else if (xhr.status === 429) {
          msg = 'Terlalu banyak percobaan. Coba beberapa saat lagi.';
        }
        showFeedback(msg, false);
      },
      complete: function () {
        // SELALU dipanggil (sukses/gagal/timeout)
        setLoading(false);
      }
    });
  });
});
