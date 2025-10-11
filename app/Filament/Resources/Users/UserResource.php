<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Auth\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'User';

    protected static ?string $navigationLabel = 'Data Pengguna';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'full_name', 'email', 'phone'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Detail dasar identitas pengguna dan informasi kontak. Pastikan data seperti Username dan Email bersifat unik.') // 👈 Penambahan Description
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Username')
                            ->required()
                            ->maxLength(50)
                            ->unique(table: 'users', column: 'name', ignoreRecord: true)
                            ->helperText('Username harus unik dan digunakan untuk login. Maksimal 50 karakter.'), // 👈 Penambahan HelperText

                        TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nama lengkap pengguna/administrator.'), // 👈 Penambahan HelperText

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->helperText('Alamat email aktif. Harus unik dan digunakan untuk notifikasi.'), // 👈 Penambahan HelperText

                        TextInput::make('phone')
                            ->label('No. HP')
                            ->tel()
                            ->maxLength(50)
                            ->helperText('Opsional. Contoh: +62812xxxxxx.'), // 👈 HelperText Ditingkatkan

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menangguhkan akses pengguna ini.'), // 👈 Penambahan HelperText
                    ])
                    ->columnSpanFull(),

                Section::make('Kredensial')
                    ->description('Pengaturan kata sandi untuk keamanan akun. Password bersifat sensitif dan dienkripsi.') // 👈 Penambahan Description
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->rule('confirmed') // butuh field password_confirmation
                            ->required(fn (string $context) => $context === 'create')
                            // hanya ter-dehydrate (tersimpan) jika diisi
                            ->dehydrated(fn ($state) => filled($state))
                            // hash otomatis
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->helperText('Minimal 8 karakter. Harus sama dengan Konfirmasi Password. **Kosongkan** saat edit jika tidak ingin mengubah.'), // 👈 HelperText Ditingkatkan

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->helperText('Ketik ulang password di atas untuk verifikasi.'), // 👈 Penambahan HelperText
                    ])->columnSpanFull(),

                Section::make('Akses & Verifikasi')
                    ->description('Pengaturan peran pengguna untuk otorisasi dan status verifikasi email.') // 👈 Penambahan Description
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Diverifikasi')
                            ->native(false)
                            ->seconds(false)
                            ->helperText('Tanggal dan waktu email ini dikonfirmasi. Atur secara manual jika diperlukan.'), // 👈 Penambahan HelperText

                        Select::make('roles')
                            ->label('Peran (Roles)')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Pilih satu atau lebih peran untuk mengatur hak akses pengguna ini pada sistem.'), // 👈 HelperText Ditingkatkan
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime(),
                TextEntry::make('phone'),
                TextEntry::make('full_name'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('roles.name')
                    ->label('Peran (Roles)')
                    ->separator(', '),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('User')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
