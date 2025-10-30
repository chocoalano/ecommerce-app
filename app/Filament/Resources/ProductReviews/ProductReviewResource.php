<?php

namespace App\Filament\Resources\ProductReviews;

use App\Filament\Resources\ProductReviews\Pages\ManageProductReviews;
use App\Models\OrderProduct\OrderItem;
use App\Models\Product\ProductReview;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship(name: 'customer', titleAttribute: 'email')
                    ->required()
                    ->helperText('Pilih pelanggan yang memberikan ulasan.')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('product_id')
                    ->label('Product')
                    ->relationship(name: 'product', titleAttribute: 'sku')
                    ->required()
                    ->helperText('Pilih produk yang sedang di-review. SKU akan ditampilkan sebagai judul.')
                    ->searchable()
                    ->preload(),

                // --- order_item_id ---
                Select::make('order_item_id')
                    ->label('Order Item ID')
                    ->relationship(name: 'orderItem')
                    ->getOptionLabelFromRecordUsing(fn (OrderItem $item) => $item->order->order_no . ' (Item SKU Produk: ' . $item->product->sku . ')')
                    ->preload()
                    ->required()
                    ->helperText('Pilih item pesanan yang terkait dengan review ini. ID item pesanan digunakan untuk melacak riwayat.')
                    ->searchable(true),

                // --- rating ---
                Rating::make('rating')
                    ->stars(5)
                    ->required()
                    ->helperText('Berikan rating bintang (1 hingga 5) untuk produk ini.'),

                // --- title ---
                TextInput::make('title')
                    ->label('Review Title')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Masukkan judul ulasan Anda.'),

                // --- comment ---
                Textarea::make('comment')
                    ->label('Comment / Review Detail')
                    ->rows(4)
                    ->required()
                    ->helperText('Tulis komentar atau ulasan detail Anda tentang produk ini.'),

                // --- is_approved ---
                Select::make('is_approved')
                    ->label('Approval Status')
                    ->options([
                        1 => 'Approved',
                        0 => 'Pending',
                    ])
                    ->required()
                    ->helperText('Tentukan status persetujuan ulasan (Approved: ditampilkan, Pending: belum ditampilkan).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.sku')
                    ->label('Product SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan secara default

                TextColumn::make('title')
                     ->label('Review Title')
                     ->limit(50)
                     ->sortable()
                     ->wrap(),

                RatingColumn::make('rating')
                    ->label('Rating')
                    ->color('warning') // Beri warna bintang
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Comment Preview')
                    ->limit(50) // Tampilkan hanya 50 karakter pertama
                    ->tooltip(fn ($record): string => $record->comment) // Tampilkan penuh saat hover
                    ->wrap(), // Memungkinkan teks membungkus

                BadgeColumn::make('is_approved')
                    ->label('Status')
                    ->getStateUsing(fn ($record): string => $record->is_approved ? 'Approved' : 'Pending')
                    ->colors([
                        'success' => 1, // Approved
                        'warning' => 0, // Pending
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Reviewed On')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter untuk Status Persetujuan
                SelectFilter::make('is_approved')
                    ->options([
                        1 => 'Approved',
                        0 => 'Pending',
                    ]),
                // Filter untuk Rating
                SelectFilter::make('rating')
                    ->options([
                        5 => '5 Stars',
                        4 => '4 Stars',
                        3 => '3 Stars',
                        2 => '2 Stars',
                        1 => '1 Star',
                    ])
            ])
            ->recordActions([
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
