<?php

namespace App\Filament\Resources\EmployeeBlocks\Pages;

use App\Filament\Resources\EmployeeBlocks\EmployeeBlockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeBlock extends EditRecord
{
    protected static string $resource = EmployeeBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
