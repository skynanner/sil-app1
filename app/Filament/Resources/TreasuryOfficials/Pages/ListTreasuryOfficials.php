<?php

namespace App\Filament\Resources\TreasuryOfficials\Pages;

use App\Filament\Resources\TreasuryOfficials\TreasuryOfficialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreasuryOfficials extends ListRecords
{
    protected static string $resource = TreasuryOfficialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
