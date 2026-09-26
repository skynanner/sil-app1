<?php

namespace App\Filament\Resources\EmployeeBlocks\Pages;

use App\Filament\Resources\EmployeeBlocks\EmployeeBlockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeBlock extends CreateRecord
{
    protected static string $resource = EmployeeBlockResource::class;
}
