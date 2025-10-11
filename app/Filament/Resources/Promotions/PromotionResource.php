<?php

namespace App\Filament\Resources\Promotions;

use App\Filament\Resources\Promotions\Pages\ManagePromotions;
use App\Models\Promo\Promotion;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use UnitEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string | UnitEnum | null $navigationGroup = 'Iklan & Promosi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                ->description('Detail promosi untuk tampilan dan identifikasi.')
                ->schema([
                    Grid::make(12)->schema([
                        TextInput::make('code')
                            ->label('Kode Promo')
                            ->helperText('Kode unik untuk klaim / identifikasi promo. Contoh: GALAXYWEEK')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Closure $set, $state) {
                                // Auto-set landing_slug default jika kosong
                                $set('landing_slug', fn ($get) => $get('landing_slug') ?: Str::slug($state));
                            })
                            ->maxLength(100)
                            ->columnSpan(4),

                        TextInput::make('name')
                            ->label('Nama Promo')
                            ->helperText('Nama yang tampil ke pengguna.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(8),

                        Select::make('type')
                            ->label('Jenis Promo')
                            ->helperText('Jenis promosi menentukan parameter & logika perhitungan.')
                            ->options(Promotion::TYPE)
                            ->native(false)
                            ->required()
                            ->live()
                            ->columnSpan(4),

                        Select::make('show_on')
                            ->label('Tampilkan di')
                            ->helperText('Posisi tampilan (Hero/Banner) pada landing.')
                            ->options(Promotion::SHOW_ON)
                            ->native(false)
                            ->default('HERO')
                            ->columnSpan(4),

                        Select::make('page')
                            ->label('Halaman')
                            ->helperText('Halaman spesifik tempat promo ditampilkan.')
                            ->options(Promotion::PAGE)
                            ->native(false)
                            ->default('beranda')
                            ->columnSpan(4),

                        TextInput::make('landing_slug')
                            ->label('Landing Slug (Opsional)')
                            ->helperText('Slug halaman khusus untuk promo ini (opsional). Contoh: galaxy-week-sale')
                            ->maxLength(255)
                            ->columnSpan(6),

                        TextInput::make('priority')
                            ->label('Prioritas')
                            ->helperText('Angka kecil = lebih prioritas ketika banyak promo aktif bersamaan.')
                            ->numeric()
                            ->default(100)
                            ->minValue(0)
                            ->columnSpan(3),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Nonaktifkan untuk menyembunyikan promo tanpa menghapus.')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(3),
                    ]),
                ])
                ->columnSpanFull(),

            Section::make('Periode')
                ->description('Waktu mulai dan akhir promo.')
                ->schema([
                    Grid::make(12)->schema([
                        DateTimePicker::make('start_at')
                            ->label('Mulai')
                            ->helperText('Waktu mulai promo (mengikuti timezone server/DB).')
                            ->seconds(false)
                            ->required()
                            ->native(false)
                            ->columnSpan(6),

                        DateTimePicker::make('end_at')
                            ->label('Berakhir')
                            ->helperText('Wajib setelah waktu mulai.')
                            ->seconds(false)
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_at')
                            ->rule('after:start_at')
                            ->columnSpan(6),
                    ]),
                ])
                ->columnSpanFull(),

            Section::make('Konten & Ketentuan')
                ->description('Deskripsi, HTML kustom, dan ketentuan granular.')
                ->schema([
                    RichEditor::make('description')
                        ->label('Deskripsi / Ketentuan Promo')
                        ->helperText('Gunakan untuk syarat & ketentuan, ketentuan klaim, pengecualian, dsb.')
                        ->toolbarButtons([
                            'bold','italic','underline','strike','link','orderedList','bulletList','blockquote','codeBlock','h2','h3','undo','redo',
                        ])
                        ->columnSpanFull(),

                    KeyValue::make('conditions_json')
                        ->label('Syarat & Filter (JSON)')
                        ->helperText('Contoh kunci: min_spend, channel, bank, whitelist_user_ids, payment_methods.')
                        ->addButtonLabel('Tambah Syarat')
                        ->reorderable()
                        ->columnSpanFull(),

                    Textarea::make('custom_html')
                        ->label('Custom HTML (Opsional)')
                        ->helperText('HTML kustom untuk tampilan promo pada landing. Pastikan aman & tervalidasi.')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('Batasan Penggunaan')
                ->description('Batasi jumlah penebusan agar stok & margin tetap aman.')
                ->schema([
                    Grid::make(12)->schema([
                        TextInput::make('max_redemption')
                            ->label('Kuota Global')
                            ->helperText('Maksimum total penggunaan promo (biarkan kosong untuk tanpa batas).')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->columnSpan(6),

                        TextInput::make('per_user_limit')
                            ->label('Batas per User')
                            ->helperText('Maksimum penggunaan promo per pengguna (biarkan kosong untuk tanpa batas).')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->columnSpan(6),
                    ]),
                ])
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => ['PERCENT_DISCOUNT','FIXED_DISCOUNT','CASHBACK','BUNDLE_PRICE'],
                        'info'    => ['BANK_INSTALLMENT','PAYMENT_METHOD_DISCOUNT'],
                        'warning' => ['FLASH_SALE','TRADE_IN','GIFT_WITH_PURCHASE'],
                    ])
                    ->formatStateUsing(fn ($state) => Promotion::TYPE[$state] ?? $state)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('start_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('end_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('max_redemption')
                    ->label('Kuota')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('per_user_limit')
                    ->label('Limit/User')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(Promotion::TYPE)
                    ->attribute('type'),

                TernaryFilter::make('is_active')
                    ->label('Aktif'),

                Filter::make('periode')
                    ->label('Dalam Rentang Tanggal')
                    ->form([
                        DateTimePicker::make('from')->label('Dari'),
                        DateTimePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $from) => $q->where('end_at','>=',$from))
                            ->when($data['until'] ?? null, fn ($q, $until) => $q->where('start_at','<=',$until));
                    }),

                SelectFilter::make('show_on')
                    ->label('Posisi')
                    ->options(Promotion::SHOW_ON),

                SelectFilter::make('page')
                    ->label('Halaman')
                    ->options(Promotion::PAGE),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('toggleActive')
                        ->label('Toggle Aktif')
                        ->icon('heroicon-o-power')
                        ->requiresConfirmation()
                        ->action(fn (Promotion $record) => $record->update(['is_active' => ! $record->is_active])),
                ])->size('sm'),
            ])
            ->bulkActions([
                BulkAction::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn ($records) => $records->each->update(['is_active' => true])),

                BulkAction::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->action(fn ($records) => $records->each->update(['is_active' => false])),
            ])
            ->defaultSort('start_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePromotions::route('/'),
        ];
    }
}
