<?php

namespace App\Filament\Resources\TravelRequests\Pages;

use App\Filament\Resources\TravelRequests\TravelRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelRequest extends ViewRecord
{
    protected static string $resource = TravelRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
