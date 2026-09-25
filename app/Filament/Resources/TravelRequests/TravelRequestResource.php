<?php

namespace App\Filament\Resources\TravelRequests;

use App\Filament\Resources\TravelRequests\Pages\CreateTravelRequest;
use App\Filament\Resources\TravelRequests\Pages\EditTravelRequest;
use App\Filament\Resources\TravelRequests\Pages\ListTravelRequests;
use App\Filament\Resources\TravelRequests\Pages\ViewTravelRequest;
use App\Models\TravelRequest;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TravelRequestResource extends Resource
{
    protected static ?string $model = TravelRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListTravelRequests::route('/'),
            'create' => CreateTravelRequest::route('/create'),
            'view' => ViewTravelRequest::route('/{record}'),
            'edit' => EditTravelRequest::route('/{record}/edit'),
        ];
    }
}
