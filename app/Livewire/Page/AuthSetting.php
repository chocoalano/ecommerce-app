<?php

namespace App\Livewire\Page;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AuthSetting extends Component implements HasSchemas
{
    use InteractsWithForms;
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Account Settings')
                ->tabs([
                    Tab::make('Profil')
                        ->schema([
                            TextInput::make('name')->label('Nama Lengkap')->required(),
                            TextInput::make('email')->label('Email')->email()->required(),
                            TextInput::make('phone')->label('No. HP')->tel(),
                        ]),
                    Tab::make('Password')
                        ->schema([
                            TextInput::make('current_password')->label('Password Lama')->password()->required(),
                            TextInput::make('new_password')->label('Password Baru')->password()->required(),
                            TextInput::make('confirm_password')->label('Konfirmasi Password')->password()->required(),
                        ]),
                    Tab::make('Notifikasi')
                        ->schema([
                            Toggle::make('newsletter')->label('Newsletter'),
                            Toggle::make('promo_sms')->label('Promo via SMS'),
                        ]),
                    Tab::make('Preferensi')
                        ->schema([
                            TextInput::make('language')->label('Bahasa')->default('id'),
                            TextInput::make('currency')->label('Mata Uang')->default('IDR'),
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        dd($this->form->getState());
    }

    public function render()
    {
        return view('livewire.page.auth-setting');
    }
}
