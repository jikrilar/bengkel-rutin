<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Booking\CancelBookingAction;
use App\Exceptions\Booking\InvalidBookingTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Booking::class);
        $query = Booking::query()
            ->whereHas('vehicle', fn ($query) => $query->where('user_id', request()->user()->id))
            ->with('vehicle');
        $activeBookings = (clone $query)->active()->orderBy('scheduled_at')->get();
        $historyBookings = (clone $query)->history()->latest('scheduled_at')->paginate(10);

        return view('customer.bookings.index', compact('activeBookings', 'historyBookings'));
    }

    public function create(): View
    {
        Gate::authorize('create', Booking::class);

        return view('customer.bookings.create');
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);
        $booking->load(['vehicle', 'events.actor']);

        return view('customer.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking, CancelBookingAction $action): RedirectResponse
    {
        Gate::authorize('update', $booking);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $action->execute($request->user(), $booking, $validated['reason']);
        } catch (InvalidBookingTransitionException $exception) {
            return back()->withErrors(['reason' => $exception->getMessage()]);
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dibatalkan. Slot kini tersedia kembali.');
    }
}
