<?php

namespace App\Filament\Resources\WorkshopSettings\Pages;

use App\Filament\Resources\WorkshopSettings\WorkshopSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkshopSetting extends EditRecord
{
    protected static string $resource = WorkshopSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
