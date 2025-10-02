<?php
namespace App\Filament\Resources\Promotions\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;

class PromotionFormComponents
{
    public static function form():array
    {
        $typeOptions = [
            'PERCENT_DISCOUNT'        => 'Percent Discount',
            'FIXED_DISCOUNT'          => 'Fixed Discount',
            'CASHBACK'                => 'Cashback',
            'BUNDLE_PRICE'            => 'Bundle Price',
            'GIFT_WITH_PURCHASE'      => 'Gift With Purchase',
            'BANK_INSTALLMENT'        => 'Bank Installment',
            'PAYMENT_METHOD_DISCOUNT' => 'Payment Method Discount',
            'FLASH_SALE'              => 'Flash Sale',
            'TRADE_IN'                => 'Trade-in',
        ];
        return [
          Section::make('Detail Promo')
            ->description('Definisi promo, periode, dan aturan global.')
            ->columns(12)
            ->schema([
                TextInput::make('code')
                    ->label('Kode Promo')
                    ->columnSpan(3)
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(100)
                    ->helperText('Kode unik (contoh: GALAXYWEEK). Wajib unik, gunakan huruf besar/angka tanpa spasi.'),

                TextInput::make('name')
                    ->label('Nama Promo')
                    ->columnSpan(6)
                    ->required()
                    ->maxLength(255)
                    ->helperText('Nama untuk tampilan di CMS/landing.'),

                Select::make('type')
                    ->label('Jenis Promo')
                    ->options($typeOptions)
                    ->required()
                    ->searchable()
                    ->columnSpan(3)
                    ->helperText('Pilih mekanisme promo. Field di tab lain akan menyesuaikan.'),

                TextInput::make('landing_slug')
                    ->label('Landing Slug')
                    ->columnSpan(4)
                    ->maxLength(255)
                    ->helperText('Opsional: slug halaman campaign (mis. galaxy-week-2025).'),

                DateTimePicker::make('start_at')
                    ->label('Mulai')
                    ->required()
                    ->columnSpan(4)
                    ->seconds(false)
                    ->helperText('Waktu mulai (disimpan UTC di DB). Pastikan sinkron dengan zona waktu operasional.'),

                DateTimePicker::make('end_at')
                    ->label('Berakhir')
                    ->required()
                    ->columnSpan(4)
                    ->seconds(false)
                    ->after('start_at')
                    ->helperText('Wajib setelah “Mulai”. Sistem akan menonaktifkan promo setelah waktu ini.'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(2)
                    ->helperText('Nonaktifkan untuk menghentikan promo tanpa menghapus data.'),

                TextInput::make('priority')
                    ->numeric()
                    ->default(100)
                    ->minValue(0)
                    ->columnSpan(2)
                    ->helperText('Angka kecil = lebih prioritas saat konflik beberapa promo.'),

                TextInput::make('max_redemption')
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(4)
                    ->helperText('Kuota global pemakaian. Biarkan kosong bila tanpa batas.'),

                TextInput::make('per_user_limit')
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(4)
                    ->helperText('Maksimal pemakaian per user. Kosongkan untuk tanpa batas.'),

                RichEditor::make('description')
                    ->label('Deskripsi/Ketentuan')
                    ->columnSpanFull()
                    ->toolbarButtons(['bold','italic','underline','strike','bulletList','orderedList','link'])
                    ->helperText('Tampilkan syarat & ketentuan agar jelas bagi tim & pelanggan.'),

                Textarea::make('conditions_json')
                    ->label('Conditions (JSON)')
                    ->rows(6)
                    ->columnSpanFull()
                    ->helperText('Opsional: filter granular (min_spend, channel, bank, whitelist, dsb). Simpan JSON valid.'),
            ])->columnSpanFull(),

        Tabs::make('promoTabs')
            ->tabs([
                Tabs\Tab::make('Scope Produk/Variant')
                    ->schema([
                        Repeater::make('products')
                            ->relationship('promotionProducts')
                            ->defaultItems(0)
                            ->addActionLabel('Tambah scope')
                            ->columns(12)
                            ->helperText('Tetapkan produk/variant yang terkena promo beserta nilai diskonnya.')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk (opsional)')
                                    ->columnSpan(6)
                                    ->searchable()
                                    ->preload()
                                    ->relationship(name: 'product', titleAttribute: 'name')
                                    ->helperText('Isi jika level produk. Boleh kosong jika spesifik ke variant.'),

                                Select::make('variant_id')
                                    ->label('Variant (opsional)')
                                    ->columnSpan(6)
                                    ->searchable()
                                    ->preload()
                                    ->relationship(name: 'variant', titleAttribute: 'variant_sku')
                                    ->helperText('Isi jika berlaku di level variant. Kosongkan jika level produk.'),

                                TextInput::make('min_qty')
                                    ->numeric()->default(1)->minValue(1)
                                    ->columnSpan(3)
                                    ->helperText('Minimal qty agar item ini eligible promo.'),

                                TextInput::make('discount_percent')
                                    ->numeric()->step('0.01')->minValue(0)->maxValue(100)
                                    ->visible(fn (callable $get) => in_array($get('../../type'), ['PERCENT_DISCOUNT']))
                                    ->columnSpan(3)
                                    ->helperText('Isi untuk PERCENT_DISCOUNT (0–100).'),

                                TextInput::make('discount_value')
                                    ->numeric()->step('0.01')->minValue(0)
                                    ->visible(fn (callable $get) => in_array($get('../../type'), ['FIXED_DISCOUNT','PAYMENT_METHOD_DISCOUNT','CASHBACK']))
                                    ->columnSpan(3)
                                    ->helperText('Isi nominal untuk FIXED_DISCOUNT / PAYMENT_METHOD_DISCOUNT / CASHBACK.'),

                                TextInput::make('bundle_price')
                                    ->numeric()->step('0.01')->minValue(0)
                                    ->visible(fn (callable $get) => in_array($get('../../type'), ['BUNDLE_PRICE']))
                                    ->columnSpan(3)
                                    ->helperText('Harga bundling final untuk item terkait (BUNDLE_PRICE).'),
                            ]),
                    ]),

                Tabs\Tab::make('Hadiah (GWP)')
                    ->visible(fn (callable $get) => $get('type') === 'GIFT_WITH_PURCHASE')
                    ->schema([
                        Repeater::make('gifts')
                            ->relationship('promotionGifts')
                            ->addActionLabel('Tambah hadiah')
                            ->columns(12)
                            ->helperText('Atur hadiah yang diberikan ketika syarat minimal terpenuhi.')
                            ->schema([
                                Select::make('gift_variant_id')
                                    ->label('Variant Hadiah')
                                    ->relationship('giftVariant', 'sku')
                                    ->searchable()->preload()
                                    ->required()
                                    ->columnSpan(6)
                                    ->helperText('Pilih variant produk yang akan diberikan sebagai hadiah.'),
                                TextInput::make('min_spend')
                                    ->numeric()->step('0.01')->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(3)
                                    ->helperText('Minimal belanja agar hadiah aktif.'),
                                TextInput::make('min_qty')
                                    ->numeric()->minValue(0)->default(0)
                                    ->columnSpan(3)
                                    ->helperText('Opsional: minimal qty item tertentu bila diperlukan.'),
                            ]),
                    ]),

                Tabs\Tab::make('Cicilan Bank')
                    ->visible(fn (callable $get) => $get('type') === 'BANK_INSTALLMENT')
                    ->schema([
                        Repeater::make('bankInstallments')
                            ->relationship('bankInstallments')
                            ->addActionLabel('Tambah skema cicilan')
                            ->columns(12)
                            ->helperText('Definisikan opsi cicilan per bank/tenor.')
                            ->schema(BankInstallmentFormComponents::form()),
                    ]),

                Tabs\Tab::make('Trade-in')
                    ->visible(fn (callable $get) => $get('type') === 'TRADE_IN')
                    ->schema([
                        Repeater::make('tradeInPrograms')
                            ->relationship('tradeInPrograms')
                            ->addActionLabel('Tambah program trade-in')
                            ->columns(12)
                            ->helperText('Atur syarat & partner untuk program tukar tambah.')
                            ->schema([
                                TextInput::make('partner_name')
                                    ->maxLength(255)
                                    ->columnSpan(6)
                                    ->helperText('Nama partner pihak ketiga (opsional).'),
                                Textarea::make('terms_json')
                                    ->rows(8)->columnSpanFull()
                                    ->helperText('JSON: cakupan brand/model yang diterima, kondisi, dsb. Pastikan format valid.'),
                            ]),
                    ]),

                Tabs\Tab::make('Voucher')
                    ->schema([
                        Repeater::make('vouchers')
                            ->relationship('vouchers')
                            ->addActionLabel('Tambah voucher')
                            ->columns(12)
                            ->helperText('Voucher dapat di-redeem saat checkout. Dapat dikaitkan dengan promo ini.')
                            ->schema(VoucherFormComponents::form()),
                    ]),
            ])->columnSpanFull()
        ];
    }
}
