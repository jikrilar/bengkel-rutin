<?php

namespace App\Filament\Resources\FuzzyConfigs\Pages;

use App\Actions\Fuzzy\ActivateFuzzyConfigAction;
use App\Filament\Resources\FuzzyConfigs\FuzzyConfigResource;
use App\Models\FuzzyConfig;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListFuzzyConfigs extends ListRecords
{
    protected static string $resource = FuzzyConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newVersion')
                ->label('Buat versi baru')
                ->icon(Heroicon::OutlinedPlus)
                ->fillForm(function (): array {
                    $active = FuzzyConfig::query()->active()->first();

                    return $active?->only(array_keys(ActivateFuzzyConfigAction::DEFAULTS))
                        ?? ActivateFuzzyConfigAction::DEFAULTS;
                })
                ->schema($this->parameterFields())
                ->action(function (array $data): void {
                    app(ActivateFuzzyConfigAction::class)->execute(auth()->user(), $data);
                    Notification::make()->success()->title('Versi fuzzy baru diaktifkan.')->send();
                }),
            Action::make('resetDefaults')
                ->label('Reset ke default')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Versi aktif tidak akan ditimpa. Sistem akan membuat versi baru dengan parameter default.')
                ->action(function (): void {
                    app(ActivateFuzzyConfigAction::class)->reset(auth()->user());
                    Notification::make()->success()->title('Versi default baru diaktifkan.')->send();
                }),
        ];
    }

    /** @return list<TextInput> */
    private function parameterFields(): array
    {
        return [
            TextInput::make('progress_safe_end')->label('Akhir progress aman')->numeric()->minValue(0)->suffix('%')->required(),
            TextInput::make('progress_approaching_peak')->label('Puncak progress mendekati')->numeric()->minValue(0)->suffix('%')->required(),
            TextInput::make('progress_critical_full')->label('Progress kritis penuh')->numeric()->minValue(0)->suffix('%')->required(),
            TextInput::make('usage_normal_full_until')->label('Penggunaan normal penuh sampai')->numeric()->minValue(0)->suffix('%')->required(),
            TextInput::make('usage_intensive_full_from')->label('Penggunaan intensif penuh mulai')->numeric()->minValue(0)->suffix('%')->required(),
        ];
    }
}
