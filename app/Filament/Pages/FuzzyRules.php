<?php

namespace App\Filament\Pages;

use App\Models\FuzzyRule;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FuzzyRules extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Rule Fuzzy';

    protected static ?string $title = '18 Rule Fuzzy Tsukamoto';

    protected string $view = 'filament.pages.fuzzy-rules';

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'rules' => FuzzyRule::query()->orderBy('code')->get(),
        ];
    }
}
