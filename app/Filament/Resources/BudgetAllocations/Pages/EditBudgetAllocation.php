<?php

namespace App\Filament\Resources\BudgetAllocations\Pages;

use App\Filament\Resources\BudgetAllocations\BudgetAllocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBudgetAllocation extends EditRecord
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
