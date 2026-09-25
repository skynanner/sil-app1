<?php

namespace App\Filament\Resources\StandardCosts\Pages;

use App\Filament\Resources\StandardCosts\StandardCostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStandardCost extends EditRecord
{
    protected static string $resource = StandardCostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
