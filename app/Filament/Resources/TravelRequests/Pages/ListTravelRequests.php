<?php

namespace App\Filament\Resources\TravelRequests\Pages;

use App\Filament\Resources\TravelRequests\TravelRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelRequests extends ListRecords
{
    protected static string $resource = TravelRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
