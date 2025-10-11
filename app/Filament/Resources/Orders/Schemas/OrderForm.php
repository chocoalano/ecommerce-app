<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Auth\CustomerAddress;
use App\Models\Product\Product;
use App\Rules\JsonKeyPattern;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Order')
                    ->description('Pilih customer (opsional untuk guest), waktu order, dan nilai order.')
                    ->schema([
                        Grid::make(12)->schema([
                            TextInput::make('order_no')
                                ->label('Nomor Order')->maxLength(50)
                                ->helperText('Nomor unik (bisa auto-generate di backend).')
                                ->columnSpan(4),

                            DateTimePicker::make('placed_at')
                                ->label('Waktu Order Dibuat')->seconds(false)->native(false)
                                ->default(now())->required()->columnSpan(4),

                            Select::make('customer_id')
                                ->label('Customer')
                                ->relationship('customer', 'name')
                                ->searchable()->preload()
                                ->placeholder('Guest (tanpa akun)')
                                ->helperText('Mem-scope daftar alamat & memudahkan auto-isi.')
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('shipping_address_id', null);
                                    $set('billing_address_id', null);
                                })
                                ->columnSpan(4),

                            TextInput::make('subtotal_amount')
                                ->label('Subtotal')->numeric()->default(0)->minValue(0)->required()
                                ->helperText('Otomatis dijumlah dari item di bawah.')
                                ->readOnly()->columnSpan(3),

                            TextInput::make('discount_amount')
                                ->label('Diskon')->numeric()->default(0)->minValue(0)
                                ->helperText('Diskon tingkat order (di luar diskon per item).')
                                ->live(debounce: 400)->afterStateUpdated(self::recalcTotal())
                                ->columnSpan(3),

                            TextInput::make('shipping_amount')
                                ->label('Ongkir')->numeric()->default(0)->minValue(0)
                                ->live(debounce: 400)->afterStateUpdated(self::recalcTotal())
                                ->columnSpan(3),

                            TextInput::make('tax_amount')
                                ->label('Pajak')->numeric()->default(0)->minValue(0)
                                ->live(debounce: 400)->afterStateUpdated(self::recalcTotal())
                                ->columnSpan(3),

                            TextInput::make('grand_total')
                                ->label('Grand Total')->numeric()->default(0)->minValue(0)->required()
                                ->helperText('Subtotal (item) − Diskon + Ongkir + Pajak.')
                                ->readOnly()->columnSpan(3),
                        ]),
                    ]),

                Section::make('Item Produk')
                    ->description('Tambahkan item; nama & SKU tersalin dari produk (snapshot).')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->defaultItems(1)->minItems(1)
                            ->collapsible()
                            ->columns(4)
                            ->schema([
                                Select::make('product_id')
                                        ->label('Produk')
                                        ->relationship('product', 'name')
                                        ->searchable()->preload()
                                        ->required()
                                        ->helperText('Pilih produk untuk menyalin nama/SKU & (opsional) harga default.')
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, ?int $state) {
                                            if (! $state) {
                                                $set('name', null);
                                                $set('sku', null);
                                                return;
                                            }
                                            $snap = self::productSnapshot($state);
                                            if ($snap) {
                                                $set('name', $snap['name'] ?? null);
                                                $set('sku',  $snap['sku']  ?? null);
                                                $set('unit_price', fn ($get) => $get('unit_price') ?: ($snap['price'] ?? null));
                                            }
                                        })
                                        ->columnSpan(2),

                                    TextInput::make('name')
                                        ->label('Nama (snapshot)')->maxLength(255)->required()
                                        ->helperText('Nama produk saat checkout untuk jejak transaksi.'),

                                    TextInput::make('sku')
                                        ->label('SKU (snapshot)')->maxLength(120),

                                    TextInput::make('qty')
                                        ->label('Qty')->numeric()->minValue(1)->default(1)->required()
                                        ->live(debounce: 300)->afterStateUpdated(self::recalcRowTotal()),

                                    TextInput::make('unit_price')
                                        ->label('Harga/Unit')->numeric()->minValue(0)->required()
                                        ->live(debounce: 300)->afterStateUpdated(self::recalcRowTotal()),

                                    TextInput::make('discount_amount')
                                        ->label('Diskon Item')->numeric()->minValue(0)->default(0)
                                        ->helperText('Diskon khusus baris ini.')
                                        ->live(debounce: 300)->afterStateUpdated(self::recalcRowTotal()),

                                    TextInput::make('row_total')
                                        ->label('Subtotal Baris')->numeric()->minValue(0)->required()
                                        ->readOnly()->dehydrated(true)
                                        ->helperText('(qty × harga) − diskon item; tidak boleh negatif.'),
                            ])
                            ->live()
                            ->afterStateUpdated(self::recalcFromItems()),
                    ]),

                Section::make('Alamat & Catatan')
                    ->description('Alamat terfilter & auto-pilih setelah memilih customer.')
                    ->schema([
                        Grid::make(12)->schema([
                            Select::make('shipping_address_id')
                                ->label('Alamat Pengiriman')
                                ->relationship(
                                    name: 'shippingAddress',
                                    titleAttribute: 'line1',
                                    modifyQueryUsing: function ($query, Get $get) {
                                        if ($cid = $get('customer_id')) $query->where('customer_id', $cid);
                                        else $query->whereRaw('1=0');
                                    }
                                )
                                ->searchable()->preload()->live()
                                ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                    if (! $state && $cid = $get('customer_id')) {
                                        if ($first = self::firstAddressIdFor($cid)) $set('shipping_address_id', $first);
                                    }
                                })
                                ->placeholder('Pilih customer terlebih dulu')
                                ->helperText('Snapshot alamat pengiriman yang digunakan.')
                                ->columnSpan(6),

                            Select::make('billing_address_id')
                                ->label('Alamat Penagihan')
                                ->relationship(
                                    name: 'billingAddress',
                                    titleAttribute: 'line1',
                                    modifyQueryUsing: function ($query, Get $get) {
                                        if ($cid = $get('customer_id')) $query->where('customer_id', $cid);
                                        else $query->whereRaw('1=0');
                                    }
                                )
                                ->searchable()->preload()->live()
                                ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                    if (! $state && $cid = $get('customer_id')) {
                                        if ($first = self::firstAddressIdFor($cid)) $set('billing_address_id', $first);
                                    }
                                })
                                ->placeholder('Pilih customer terlebih dulu')
                                ->helperText('Snapshot alamat penagihan yang digunakan.')
                                ->columnSpan(6),
                        ]),

                        KeyValue::make('applied_promos')
                            ->label('Promo Diterapkan (JSON)')
                            ->addButtonLabel('Tambah entri')
                            ->keyLabel('Kunci')->valueLabel('Nilai')
                            ->keyPlaceholder('snake_case atau dot.notation')
                            ->valuePlaceholder('contoh: GALAXYWEEK / 25000')
                            ->reorderable()
                            ->helperText('Standar kunci: code, amount, provider, type, source, campaign, voucher_code, note, meta.*')
                            ->rules(['nullable','array', new JsonKeyPattern()])
                            ->default([
                                'code'         => null,
                                'amount'       => null,
                                'provider'     => null,
                                'type'         => null,
                                'source'       => null,
                                'campaign'     => null,
                                'voucher_code' => null,
                                // 'meta.gateway' => null,
                            ])
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Catatan')->rows(3)
                            ->helperText('Catatan tambahan dari pengguna/CS.')
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }

    /** ================= Helpers ================= */

    protected static function recalcTotal(): Closure
    {
        return function (Set $set, Get $get) {
            $subtotal = (float) ($get('subtotal_amount') ?: 0);
            $discount = (float) ($get('discount_amount') ?: 0);
            $shipping = (float) ($get('shipping_amount') ?: 0);
            $tax      = (float) ($get('tax_amount') ?: 0);
            $set('grand_total', max(0, $subtotal - $discount + $shipping + $tax));
        };
    }

    protected static function recalcRowTotal(): Closure
    {
        return function (Set $set, Get $get) {
            $qty   = (float) ($get('qty') ?: 0);
            $price = (float) ($get('unit_price') ?: 0);
            $disc  = (float) ($get('discount_amount') ?: 0);
            $set('row_total', max(0, ($qty * $price) - $disc));

            self::sumItemsToSubtotal()($set, $get);
        };
    }

    protected static function recalcFromItems(): Closure
    {
        return function (Set $set, Get $get) {
            self::sumItemsToSubtotal()($set, $get);
        };
    }

    protected static function sumItemsToSubtotal(): Closure
    {
        return function (Set $set, Get $get) {
            $items = $get('items') ?: [];
            $subtotal = 0;
            foreach ($items as $i) {
                $qty   = (float) ($i['qty'] ?? 0);
                $price = (float) ($i['unit_price'] ?? 0);
                $disc  = (float) ($i['discount_amount'] ?? 0);
                $subtotal += max(0, ($qty * $price) - $disc);
            }
            $set('subtotal_amount', $subtotal);

            $discount = (float) ($get('discount_amount') ?: 0);
            $shipping = (float) ($get('shipping_amount') ?: 0);
            $tax      = (float) ($get('tax_amount') ?: 0);
            $set('grand_total', max(0, $subtotal - $discount + $shipping + $tax));
        };
    }

    protected static function productSnapshot(int $productId): ?array
    {
        $p = \App\Models\Product::query()->select(['id','name','sku','price'])->find($productId);
        return $p ? ['name'=>$p->name, 'sku'=>$p->sku, 'price'=>$p->price] : null;
    }

    protected static function firstAddressIdFor(int|string $customerId): ?int
    {
        return \App\Models\CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->value('id');
    }
}
