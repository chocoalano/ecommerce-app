<?php
namespace App\Filament\Resources\Promotions\Components;

use Filament\Forms\Components\TextInput;

class BankInstallmentFormComponents
{
    public static function form():array
    {
        return[
            TextInput::make('bank_code')
                ->required()->maxLength(50)
                ->columnSpan(4)
                ->helperText('Kode bank, mis. BCA/BNI/Mandiri.'),
            TextInput::make('tenor_months')
                ->numeric()->minValue(1)->required()
                ->columnSpan(2)
                ->helperText('Tenor dalam bulan (3/6/12, dll).'),
            TextInput::make('interest_rate_pa')
                ->numeric()->step('0.0001')->minValue(0)
                ->columnSpan(3)
                ->helperText('Suku bunga efektif per tahun (opsional).'),
            TextInput::make('admin_fee')
                ->numeric()->step('0.01')->minValue(0)->default(0)
                ->columnSpan(3)
                ->helperText('Biaya admin tambahan, jika ada.'),
            TextInput::make('min_spend')
                ->numeric()->step('0.01')->minValue(0)->default(0)
                ->columnSpan(3)
                ->helperText('Transaksi minimal agar cicilan eligible.'),
        ];
    }
}
