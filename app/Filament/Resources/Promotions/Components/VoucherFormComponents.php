<?php
namespace App\Filament\Resources\Promotions\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class VoucherFormComponents
{
    public static function form():array
    {
        return [
            TextInput::make('code')
                ->required()->maxLength(100)
                ->unique(ignoreRecord: true, table: 'vouchers', column: 'code')
                ->columnSpan(4)
                ->helperText('Kode voucher unik. Jangan sama dengan promo code.'),
            Toggle::make('is_stackable')
                ->inline(false)->default(false)
                ->columnSpan(2)
                ->helperText('Jika aktif, voucher dapat ditumpuk dengan promo lain.'),
            DateTimePicker::make('start_at')
                ->seconds(false)->columnSpan(3)
                ->helperText('Mulai berlaku (UTC di DB). Kosongkan untuk mengikuti periode promo.'),
            DateTimePicker::make('end_at')
                ->seconds(false)->columnSpan(3)
                ->after('start_at')
                ->helperText('Berakhir. Kosongkan untuk mengikuti periode promo.'),
            TextInput::make('max_redemption')
                ->numeric()->minValue(0)->columnSpan(3)
                ->helperText('Kuota semua user. Kosongkan bila tanpa batas.'),
            TextInput::make('per_user_limit')
                ->numeric()->minValue(0)->columnSpan(3)
                ->helperText('Batas per user. Kosongkan bila tanpa batas.'),
            Textarea::make('conditions_json')
                ->rows(6)->columnSpanFull()
                ->helperText('JSON syarat granular (mis. min_spend khusus voucher).'),
        ];
    }
}
