<?php

namespace App\Filament\Resources\ProductReviews;

use App\Filament\Resources\ProductReviews\Pages\ManageProductReviews;
use App\Models\ProductReview;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;
    protected static string | UnitEnum | null $navigationGroup = 'Produk';
    protected static ?string $recordTitleAttribute = 'ProductReview';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name') // asumsi field 'name' ada di tabel users
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih pengguna yang memberikan ulasan.'),

                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name') // asumsi field 'name' ada di tabel products
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih produk yang diulas.'),

                TextInput::make('rating')
                    ->label('Rating')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->helperText('Nilai rating antara 1 (terendah) sampai 5 (tertinggi).'),

                TextInput::make('title')
                    ->label('Judul Ulasan')
                    ->maxLength(255)
                    ->helperText('Judul singkat untuk ulasan produk.'),

                Textarea::make('comment')
                    ->label('Komentar')
                    ->columnSpanFull()
                    ->helperText('Tulis ulasan detail tentang produk.'),

                Toggle::make('is_approved')
                    ->label('Disetujui')
                    ->required()
                    ->helperText('Tentukan apakah ulasan ini sudah disetujui admin atau belum.'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('product_id')
                    ->numeric(),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('title'),
                IconEntry::make('is_approved')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ProductReview')
            ->columns([
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                IconColumn::make('is_approved')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => ManageProductReviews::route('/'),
        ];
    }
}
