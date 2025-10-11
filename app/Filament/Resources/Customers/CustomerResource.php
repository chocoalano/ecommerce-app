<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\ManageCustomers;
use App\Models\Auth\Customer;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'full_name', 'email', 'phone'];
    }
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Username')
                            ->required()
                            ->maxLength(50)
                            ->unique(table: 'customers', column: 'name', ignoreRecord: true)
                            ->helperText('Gunakan nama singkat tanpa spasi. Maksimal 50 karakter. Harus unik.'),

                        TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama lengkap pengguna/pelanggan, sesuai KTP. Maksimal 255 karakter.'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(table: 'customers', column: 'email', ignoreRecord: true)
                            ->helperText('Alamat email aktif. Harus unik dan berformat email yang valid.'),

                        TextInput::make('phone')
                            ->label('No. HP')
                            ->tel()
                            ->maxLength(50)
                            ->helperText('Nomor telepon/HP yang dapat dihubungi.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menangguhkan akun pelanggan ini.'),
                    ])
                    ->columnSpanFull(),

                Section::make('Kredensial')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->rule('confirmed')
                            ->required(fn (string $context) => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->helperText('Minimal 8 karakter. Harus sama dengan Konfirmasi Password. Kosongkan saat edit jika tidak ingin mengubah.'),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->helperText('Ketik ulang password di atas untuk konfirmasi.'),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Diverifikasi')
                            ->native(false)
                            ->seconds(false)
                            ->helperText('Tanggal dan waktu email pelanggan diverifikasi. Kosongkan jika belum diverifikasi.'),
                    ])
                    ->columnSpanFull(),

                Section::make('Customer Addresses')
                    ->description('Kelola alamat pelanggan langsung dari form ini. Tambahkan minimal satu alamat.')
                    ->schema([
                        Repeater::make('addresses')
                            ->relationship('addresses')
                            ->label('Daftar Alamat')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['label'] ?? ($state['recipient_name'] ?? 'Alamat'))
                            ->addActionLabel('Tambah Alamat Baru') // 👈 Sedikit perubahan untuk kejelasan
                            ->columns(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label Alamat')
                                    ->maxLength(100)
                                    ->required()
                                    ->helperText('Contoh: "Rumah", "Kantor", atau "Alamat Utama".'),

                                TextInput::make('recipient_name')
                                    ->label('Nama Penerima')
                                    ->maxLength(150)
                                    ->required()
                                    ->helperText('Nama lengkap orang yang akan menerima paket.'),

                                TextInput::make('phone')
                                    ->label('No. HP')
                                    ->tel()
                                    ->maxLength(50)
                                    ->helperText('Nomor HP penerima alamat ini.'),

                                Textarea::make('line1')
                                    ->label('Alamat 1')
                                    ->maxLength(255)
                                    ->required()
                                    ->columnSpanFull()
                                    ->helperText('Nama jalan, nomor rumah, RT/RW, dan detail lokasi utama.'),

                                Textarea::make('line2')
                                    ->label('Alamat 2')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->helperText('Tambahan detail, seperti blok atau patokan. Opsional.'),

                                TextInput::make('city')
                                    ->label('Kota/Kab')
                                    ->maxLength(100)
                                    ->required()
                                    ->helperText('Nama kota atau kabupaten.'),

                                TextInput::make('province')
                                    ->label('Provinsi')
                                    ->maxLength(100)
                                    ->helperText('Nama provinsi.'),

                                TextInput::make('postal_code')
                                    ->label('Kode Pos')
                                    ->maxLength(20)
                                    ->helperText('Kode pos area alamat ini.'),

                                TextInput::make('country')
                                    ->label('Negara')
                                    ->maxLength(100)
                                    ->default('Indonesia')
                                    ->helperText('Nama negara.'),

                                Toggle::make('is_default')
                                    ->label('Alamat Default?')
                                    ->inline(false)
                                    ->helperText('Tandai *salah satu* alamat sebagai alamat utama/default pelanggan.'), // 👈 HelperText Ditingkatkan
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('addresses_count')
                    ->counts('addresses')
                    ->label('Alamat')
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('Verifikasi')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->placeholder('Semua')
                    ->queries(
                        true: fn (Builder $q) => $q->where('is_active', true),
                        false: fn (Builder $q) => $q->where('is_active', false),
                        blank: fn (Builder $q) => $q,
                    ),

                TernaryFilter::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum')
                    ->placeholder('Semua')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('email_verified_at'),
                        false: fn (Builder $q) => $q->whereNull('email_verified_at'),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),

                    BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomers::route('/'),
        ];
    }
}
