<?php

namespace App\Filament\Resources\EmployeeBlocks\Pages;

use App\Filament\Resources\EmployeeBlocks\EmployeeBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeBlocks extends ListRecords
{
    protected static string $resource = EmployeeBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
