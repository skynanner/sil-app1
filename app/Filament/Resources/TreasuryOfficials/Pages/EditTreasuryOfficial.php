<?php

namespace App\Filament\Resources\TreasuryOfficials\Pages;

use App\Filament\Resources\TreasuryOfficials\TreasuryOfficialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTreasuryOfficial extends EditRecord
{
    protected static string $resource = TreasuryOfficialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
