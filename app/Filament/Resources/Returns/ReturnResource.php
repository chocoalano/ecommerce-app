<?php

namespace App\Filament\Resources\Returns;

use App\Filament\Resources\Returns\Pages\CreateReturn;
use App\Filament\Resources\Returns\Pages\EditReturn;
use App\Filament\Resources\Returns\Pages\ListReturns;
use App\Filament\Resources\Returns\Pages\ViewReturn;
use App\Filament\Resources\Returns\Schemas\ReturnForm;
use App\Filament\Resources\Returns\Schemas\ReturnInfolist;
use App\Filament\Resources\Returns\Tables\ReturnsTable;
use App\Models\ReturnModel;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnResource extends Resource
{
    protected static ?string $model = ReturnModel::class;

    protected static string|UnitEnum|null $navigationGroup = 'Pesanan';

    protected static ?string $recordTitleAttribute = 'Return';

    public static function form(Schema $schema): Schema
    {
        return ReturnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturns::route('/'),
            'create' => CreateReturn::route('/create'),
            'view' => ViewReturn::route('/{record}'),
            'edit' => EditReturn::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
