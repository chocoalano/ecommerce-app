{{-- resources/views/mail/layouts/base.blade.php --}}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="id">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="x-apple-disable-message-reformatting"/>
  <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no"/>
  <meta name="color-scheme" content="light dark"/>
  <meta name="supported-color-schemes" content="light dark"/>
  <title>{{ $mainTitle ?? 'Penawaran Spesial' }}</title>

  <style type="text/css">
    /* Resets */
    body,table,td,a{ -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table,td{ mso-table-lspace:0pt; mso-table-rspace:0pt; }
    img{ -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; }
    table{ border-collapse:collapse !important; }
    body{ margin:0 !important; padding:0 !important; width:100% !important; }

    /* iOS blue links fix */
    a[x-apple-data-detectors]{ color:inherit !important; text-decoration:none !important; }

    /* Gmail dark mode quirk */
    u + #body a{ color:inherit; text-decoration:none; font-size:inherit; }

    /* Helpers */
    .paragraph{ font-size:16px; line-height:24px; }
    .heading2{ font-size:28px; line-height:36px; }

    @media only screen and (max-width:600px){
      .single-column{ width:100% !important; float:none !important; }
      .mobile-padding{ padding-left:20px !important; padding-right:20px !important; }
      .three-column td{ display:block !important; width:100% !important; }
      .three-column td img{ max-width:100% !important; height:auto !important; }
    }
  </style>

  <!--[if mso]>
  <style type="text/css"> * { font-family: Arial, sans-serif !important; } </style>
  <![endif]-->
</head>

<body id="body" style="margin:0; padding:0; background-color:#f7f7f7;">
  {{-- Preheader (disembunyikan) --}}
  <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#fff;">
    {{ $mainDescription ?? 'Koleksi baru & penawaran spesial untuk Anda. Stok terbatas!' }}
  </div>

  <center style="width:100%;">
    <div style="max-width:600px; width:100%; margin:0 auto;">
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
             style="max-width:600px; width:100%; background:#ffffff;">

        {{-- Header: Logo --}}
        <tr>
          <td class="bg-white" style="padding:24px 0 16px; text-align:center;">
            <a href="{{ url('/') }}" style="text-decoration:none;">
              <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" width="128"
                   style="display:inline-block; max-width:128px; height:auto; border:0;"/>
            </a>
          </td>
        </tr>

        {{-- Hero / Banner --}}
        <tr>
          <td class="bg-white" style="padding:0;">
            <a href="{{ $shopNowUrl }}" style="text-decoration:none;">
              <img src="{{ $bannerImageUrl }}" alt="{{ $mainTitle ?? 'New Collection' }}" width="600"
                   style="display:block; width:100%; height:auto; border:0;"/>
            </a>
          </td>
        </tr>

        {{-- Slot konten halaman (CTA, produk, dll) --}}
        @yield('content')

        {{-- Closing --}}
        <tr>
          <td class="bg-white mobile-padding" style="padding:16px 32px 0; text-align:center;">
            <p class="paragraph" style="margin:0; font-size:14px; line-height:20px; color:#6B7280; font-style:italic;">
              {{ $closingMessage ?? 'P.S. Jangan lewatkan penawaran spesial ini!' }}
            </p>
          </td>
        </tr>

        {{-- Social (sesuai format array dari Mailable->getSocialLinks) --}}
        @if(isset($socialLinks) && is_array($socialLinks) && count($socialLinks))
        <tr>
          <td class="bg-white" style="padding:12px 32px; text-align:center;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
              <tr>
                @foreach ($socialLinks as $link)
                  <td style="padding:0 6px;">
                    <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener" style="text-decoration:none;">
                      <img src="{{ $link['icon'] ?? '' }}" alt="{{ $link['platform'] ?? 'Social' }}" width="24" height="24"
                           style="display:inline-block; border:0;">
                    </a>
                  </td>
                @endforeach
              </tr>
            </table>
          </td>
        </tr>
        @endif

        {{-- Unsubscribe --}}
        <tr>
          <td class="bg-white" style="padding:8px 32px 24px; text-align:center;">
            <p style="margin:0; font-size:12px; line-height:18px; color:#6B7280;">
              Anda menerima email ini karena Anda berlangganan buletin kami.
              <br>
              <a href="{{ $unsubscribeUrl }}" style="color:#6B7280; text-decoration:underline;">Berhenti Berlangganan</a>
            </p>
          </td>
        </tr>

      </table>
    </div>
  </center>
</body>
</html>
