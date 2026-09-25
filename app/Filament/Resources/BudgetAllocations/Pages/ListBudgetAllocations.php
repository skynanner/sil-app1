<?php

namespace App\Filament\Resources\BudgetAllocations\Pages;

use App\Filament\Resources\BudgetAllocations\BudgetAllocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBudgetAllocations extends ListRecords
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
