<?php

namespace App\Filament\Resources\ServiceProfiles\Pages;

use App\Filament\Resources\ServiceProfiles\ServiceProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceProfiles extends ListRecords
{
    protected static string $resource = ServiceProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
