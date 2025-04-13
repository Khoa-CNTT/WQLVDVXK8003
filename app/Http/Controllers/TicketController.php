<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Ticket;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Create a new ticket.
     */
    public function create(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seat_id' => 'required|exists:seats,id',
            'passenger_name' => 'required|string|max:255',
            'passenger_phone' => 'required|string|max:20',
            'passenger_email' => 'nullable|email|max:255',
            'payment_method' => 'required|in:cod,vnpay,momo',
        ]);

        // Check if the seat is available
        $trip = Trip::findOrFail($request->trip_id);
        $seat = Seat::findOrFail($request->seat_id);

        $existingTicket = Ticket::where('trip_id', $trip->id)
            ->where('seat_id', $seat->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingTicket) {
            return back()->withErrors(['message' => 'Seat is already booked']);
        }

        // Create ticket
        $ticket = new Ticket();
        $ticket->generateTicketNumber();
        $ticket->trip_id = $trip->id;
        $ticket->user_id = Auth::id();
        $ticket->seat_id = $seat->id;
        $ticket->price = $trip->price;
        $ticket->passenger_name = $request->passenger_name;
        $ticket->passenger_phone = $request->passenger_phone;
        $ticket->passenger_email = $request->passenger_email;
        $ticket->status = 'pending';
        $ticket->save();

        // Redirect to payment
        if ($request->payment_method == 'cod') {
            $ticket->status = 'confirmed';
            $ticket->save();

            // Create payment record
            $ticket->payment()->create([
                'payment_method' => 'cod',
                'amount' => $ticket->price,
                'status' => 'completed',
                'paid_at' => now(),
                'notes' => 'Thanh toán khi lên xe',
            ]);

            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', 'Ticket booked successfully');
        }

        return redirect()->route('payments.process', [
            'ticket_id' => $ticket->id,
            'payment_method' => $request->payment_method,
        ]);
    }

    /**
     * Display the specified ticket.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['trip.route', 'trip.vehicle', 'seat', 'payment'])
            ->findOrFail($id);

        // Check if user is authorized to view this ticket
        if (Auth::id() != $ticket->user_id && !Auth::user()->isAdmin) {
            return abort(403, 'Unauthorized action');
        }

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Display a list of user's tickets.
     */
    public function myTickets()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->with(['trip.route', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tickets.my-tickets', compact('tickets'));
    }

    /**
     * Cancel a ticket.
     */
    public function cancel($id)
    {
        $ticket = Ticket::findOrFail($id);

        // Check if user is authorized to cancel this ticket
        if (Auth::id() != $ticket->user_id && !Auth::user()->isAdmin) {
            return abort(403, 'Unauthorized action');
        }

        // Check if ticket can be cancelled
        $departureDateTime = $ticket->trip->departure_date . ' ' . $ticket->trip->departure_time;
        $hoursUntilDeparture = now()->diffInHours(now()->createFromFormat('Y-m-d H:i:s', $departureDateTime), false);

        if ($hoursUntilDeparture < 6 && !Auth::user()->isAdmin) {
            return back()->withErrors(['message' => 'Tickets can only be cancelled at least 6 hours before departure']);
        }

        $ticket->status = 'cancelled';
        $ticket->save();

        // If payment exists, mark as refunded
        if ($ticket->payment) {
            $ticket->payment->status = 'refunded';
            $ticket->payment->save();
        }

        return back()->with('success', 'Ticket cancelled successfully');
    }
}
