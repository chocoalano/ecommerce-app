@extends('mail.layouts.base')

@section('content')
@php
  // Fallback aman dari layout (kalau belum terdefinisi)
  $fontStack        = $fontStack        ?? "-apple-system, Segoe UI, Roboto, Arial, sans-serif";
  $textHex          = $textHex          ?? '#050505';
  $buttonBgHex      = $buttonBgHex      ?? '#000000';
  $buttonColorHex   = $buttonColorHex   ?? '#FFFFFF';

  // Mapping data yang kamu kirim
  $mainTitle        = $mainTitle        ?? ($main_title ?? 'New Basic Collection Launch!');
  $mainDescription  = $mainDescription  ?? ($main_description ?? 'Kami meluncurkan koleksi terbaru dengan diskon spesial 30%!');
  $shopNowUrl       = $shopNowUrl       ?? ($shop_now_url ?? url('/'));

  // Featured product
  $fp               = $featuredProduct  ?? ($featured_product ?? null);
  $fpName           = $fp['name']                ?? 'Nama Produk Unggulan';
  $fpOldPrice       = $fp['old_price']           ?? null;
  $fpNewPrice       = $fp['new_price']           ?? ($fp['price'] ?? null);
  $fpDesc           = $fp['description']         ?? 'Deskripsi singkat yang menonjolkan fitur terbaik.';
  $fpLink           = $fp['link']                ?? '#';
  $fpImgSmall       = $fp['image_url_small']     ?? ($fp['image_url'] ?? '');
  $imgFullWidth     = $bottomBannerUrl           // kalau layout sudah punya bottomBannerUrl, pakai itu
                        ?? ($fp['image_url_full_width'] ?? null); // jika tidak, pakai full-width dari featured product bila ada
@endphp

{{-- Headline --}}
<tr>
  <td class="bg-white mobile-padding" style="padding:20px 32px 8px; text-align:center;">
    <h1 class="heading2"
        style="margin:0; font-family:{{ $fontStack }}; font-weight:700; font-size:28px; line-height:36px; color:{{ $textHex }};">
      {{ $mainTitle }}
    </h1>
  </td>
</tr>

{{-- Deskripsi --}}
<tr>
  <td class="bg-white mobile-padding" style="padding:0 80px; text-align:center;">
    <p class="paragraph"
       style="margin:0; font-family:{{ $fontStack }}; font-size:16px; line-height:24px; color:{{ $textHex }};">
      {{ $mainDescription }}
    </p>
  </td>
</tr>

{{-- CTA utama (bulletproof + VML) --}}
<tr>
  <td class="bg-white" align="center" style="padding:24px 16px;">
    <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $shopNowUrl }}"
        style="height:44px;v-text-anchor:middle;width:240px;" arcsize="12%" stroke="f" fillcolor="{{ $buttonBgHex }}">
        <w:anchorlock/>
        <center style="color:{{ $buttonColorHex }};font-family:Arial, sans-serif;font-size:16px;font-weight:bold;">
          BELANJA SEKARANG
        </center>
      </v:roundrect>
    <![endif]-->
    <!--[if !mso]><!-- -->
    <a href="{{ $shopNowUrl }}"
       style="background:{{ $buttonBgHex }}; color:{{ $buttonColorHex }}; display:inline-block; padding:12px 32px; border-radius:10px; text-decoration:none; font-family:{{ $fontStack }}; font-weight:700; font-size:16px; line-height:20px;">
      BELANJA SEKARANG
    </a>
    <!--<![endif]-->
  </td>
</tr>

{{-- Featured Product (opsional) --}}
@if($fp)
<tr>
  <td class="bg-white mobile-padding" style="padding:8px 32px 16px;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
      <tr>
        <td width="33%" valign="top" class="single-column" style="padding-right:16px;">
          <a href="{{ $fpLink }}" style="text-decoration:none;">
            <img src="{{ $fpImgSmall }}"
                 alt="{{ $fpName }}" width="160"
                 style="display:block; max-width:100%; height:auto; border:0; border-radius:8px;">
          </a>
        </td>
        <td width="67%" valign="top" class="single-column">
          <p style="margin:0 0 4px; font-family:{{ $fontStack }}; font-size:12px; line-height:16px; color:#6B7280; text-transform:uppercase;">
            {{-- kategori opsional, tidak ada di data kiriman --}} Kategori
          </p>
          <h3 style="margin:0 0 4px; font-family:{{ $fontStack }}; font-size:18px; line-height:24px; font-weight:700; color:{{ $textHex }};">
            {{ $fpName }}
          </h3>
          <p style="margin:0 0 8px; font-family:{{ $fontStack }}; font-size:16px; line-height:22px; font-weight:700;">
            @if($fpOldPrice)
              <span style="color:#6B7280; text-decoration:line-through; margin-right:8px;">{{ $fpOldPrice }}</span>
            @endif
            <span style="color:#DC2626;">{{ $fpNewPrice }}</span>
          </p>
          <p style="margin:0 0 12px; font-family:{{ $fontStack }}; font-size:14px; line-height:20px; color:#374151;">
            {{ $fpDesc }}
          </p>
          <a href="{{ $fpLink }}"
             style="background:#18181B; color:#FFFFFF; padding:8px 16px; border-radius:6px; text-decoration:none; display:inline-block; font-family:{{ $fontStack }}; font-weight:600; font-size:14px; line-height:18px;">
            Lihat Produk
          </a>
        </td>
      </tr>
    </table>
  </td>
</tr>
@endif

{{-- Bottom banner (gunakan bottomBannerUrl dari layout jika ada, kalau tidak pakai full-width dari featured product) --}}
@if(!empty($imgFullWidth))
<tr>
  <td class="bg-white" style="padding:0;">
    <a href="{{ $shopNowUrl }}" style="text-decoration:none;">
      <img src="{{ $imgFullWidth }}" alt="More Picks" width="600"
           style="display:block; width:100%; height:auto; border:0;">
    </a>
  </td>
</tr>
@endif
@endsection
