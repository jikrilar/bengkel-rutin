<?php

namespace App\Filament\Pages;

use App\Services\Reporting\ReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan Operasional';

    protected string $view = 'filament.pages.reports';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = now(config('app.timezone'))->startOfMonth()->toDateString();
        $this->endDate = now(config('app.timezone'))->toDateString();
    }

    public function applyFilters(): void
    {
        $this->validate([
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
        ], [
            'endDate.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
        ]);
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'report' => app(ReportService::class)->forPeriod($this->startDate, $this->endDate),
        ];
    }
}
