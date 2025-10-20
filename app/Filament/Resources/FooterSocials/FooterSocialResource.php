<?php

namespace App\Filament\Resources\FooterSocials;

use App\Filament\Resources\FooterSocials\Pages\ManageFooterSocials;
use App\Models\FooterSocial;
use Filament\Forms\Components\FileUpload;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FooterSocialResource extends Resource
{
    protected static ?string $model = FooterSocial::class;

    protected static string | UnitEnum | null $navigationGroup = 'Pengaturan Website';

    protected static ?string $recordTitleAttribute = 'FooterSocial';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('platform')
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required(),
                FileUpload::make('icon')
                    ->label('Icon (SVG)')
                    ->acceptedFileTypes(['image/svg+xml'])
                    ->maxSize(1024) // ukuran maksimum dalam KB, sesuaikan jika perlu
                    ->disk('public')
                    ->directory('images/icons')
                    ->required(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('platform'),
                TextEntry::make('url'),
                TextEntry::make('icon')
                    ->placeholder('-'),
                TextEntry::make('order')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('FooterSocial')
            ->columns([
                TextColumn::make('platform')
                    ->searchable(),
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('icon')
                    ->searchable(),
                TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
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
            'index' => ManageFooterSocials::route('/'),
        ];
    }
}
