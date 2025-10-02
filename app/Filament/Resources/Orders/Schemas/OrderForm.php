<?php

namespace App\Filament\Resources\Orders\Schemas;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $status = ['PENDING','PAID','PROCESSING','SHIPPED','COMPLETED','CANCELED','REFUNDED','PARTIAL_REFUND'];
        return $schema
            ->components([
                Section::make('Ringkasan Order')
                    ->description('Data utama order dari checkout hingga selesai/refund.')
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('order_no')
                            ->label('Order No')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(3)
                            ->helperText('Nomor order unik yang ditampilkan ke pengguna.'),

                        Select::make('user_id')
                            ->label('User (opsional)')
                            ->relationship('user', 'name')
                            ->searchable()->preload()
                            ->columnSpan(3)
                            ->helperText('Pemilik order. Biarkan kosong jika guest checkout.'),

                        TextInput::make('currency')
                            ->maxLength(3)
                            ->default('IDR')
                            ->columnSpan(2)
                            ->helperText('Kode mata uang (mis. IDR).'),

                        Select::make('status')
                            ->options(array_combine($status, $status))
                            ->default('PENDING')
                            ->columnSpan(4)
                            ->helperText('Status order terkini.'),

                        TextInput::make('subtotal_amount')->numeric()->default(0)->minValue(0)
                            ->label('Subtotal')->columnSpan(3)
                            ->helperText('Subtotal item sebelum diskon/ongkir/pajak.'),

                        TextInput::make('discount_amount')->numeric()->default(0)->minValue(0)
                            ->label('Diskon')->columnSpan(3)
                            ->helperText('Total diskon yang diterapkan pada order.'),

                        TextInput::make('shipping_amount')->numeric()->default(0)->minValue(0)
                            ->label('Ongkir')->columnSpan(3)
                            ->helperText('Biaya pengiriman.'),

                        TextInput::make('tax_amount')->numeric()->default(0)->minValue(0)
                            ->label('Pajak')->columnSpan(3)
                            ->helperText('Total pajak.'),

                        TextInput::make('grand_total')->numeric()->default(0)->minValue(0)
                            ->label('Grand Total')->columnSpan(3)
                            ->helperText('Total akhir yang harus dibayar.'),

                        Select::make('shipping_address_id')
                            ->label('Alamat Pengiriman')
                            ->relationship('shippingAddress','label') // ganti 'label' sesuai kolom alamat Anda
                            ->searchable()->preload()
                            ->columnSpan(4)
                            ->helperText('Alamat pengiriman yang digunakan.'),

                        Select::make('billing_address_id')
                            ->label('Alamat Penagihan')
                            ->relationship('billingAddress','bank_code')
                            ->searchable()->preload()
                            ->columnSpan(4)
                            ->helperText('Alamat penagihan yang digunakan.'),

                        Textarea::make('notes')
                            ->label('Catatan Pengguna/CS')
                            ->rows(3)
                            ->columnSpan(12)
                            ->helperText('Catatan tambahan dari pengguna/CS.'),

                        Textarea::make('applied_promos')
                            ->label('Applied Promos (JSON)')
                            ->rows(4)
                            ->columnSpan(12)
                            ->helperText('Daftar promo/voucher yang diterapkan (JSON). Pastikan format valid.'),

                        DateTimePicker::make('placed_at')
                            ->label('Waktu Order Dibuat')
                            ->seconds(false)
                            ->default(now())
                            ->columnSpan(4)
                            ->helperText('Waktu checkout selesai.'),
                    ]),
            ]);
    }
}
