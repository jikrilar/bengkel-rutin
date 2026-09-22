<?php

namespace App\Filament\Resources\ServiceProfiles\Pages;

use App\Actions\Service\UpdateServiceProfileAction;
use App\Filament\Resources\ServiceProfiles\ServiceProfileResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditServiceProfile extends EditRecord
{
    protected static string $resource = ServiceProfileResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateServiceProfileAction::class)->execute(auth()->user(), $record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
