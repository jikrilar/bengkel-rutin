<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ServiceRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ServiceHistoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', ServiceRecord::class);
        $records = ServiceRecord::query()
            ->whereHas('vehicle', fn ($query) => $query->where('user_id', request()->user()->id))
            ->with('vehicle')
            ->latest('service_date')
            ->paginate(12);

        return view('customer.service-history.index', compact('records'));
    }

    public function show(ServiceRecord $record): View
    {
        Gate::authorize('view', $record);
        $record->load(['vehicle', 'booking']);

        return view('customer.service-history.show', compact('record'));
    }
}
