<?php

namespace App\Filament\Resources\StandardCosts\Pages;

use App\Filament\Resources\StandardCosts\StandardCostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStandardCosts extends ListRecords
{
    protected static string $resource = StandardCostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
