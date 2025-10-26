<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => Article::query()->with('content'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('seo_title')
                    ->label('SEO Judul')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('seo_description')
                    ->label('SEO Deskripsi')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                BadgeColumn::make('published_at')
                    ->label('Diterbitkan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->colors([
                        'danger' => fn($state) => is_null($state),
                        'success' => fn($state) => !is_null($state),
                    ])
                    ->formatStateUsing(fn($state) => $state ? $state->format('d M Y H:i') : 'Belum dipublikasikan'),

                TextColumn::make('content.tags')
                    ->label('Tag')
                    ->formatStateUsing(fn($state) => $state ? implode(', ', json_decode($state, true) ?? []) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->trueLabel('Dipublikasikan')
                    ->falseLabel('Belum dipublikasikan')
                    ->nullable(),

                Filter::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('published_at', '<=', $date));
                    }),

                TrashedFilter::make(),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->searchPlaceholder('Cari berdasarkan judul, slug, deskripsi SEO, atau tag...')
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Tambahkan artikel baru untuk memulai.');
    }
}
