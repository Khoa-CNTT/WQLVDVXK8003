<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['trip.route', 'user', 'payment']);

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by trip
        if ($request->has('trip_id') && !empty($request->trip_id)) {
            $query->where('trip_id', $request->trip_id);
        }

        // Filter by user
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        $trips = Trip::orderBy('departure_date', 'desc')->take(50)->get();
        $users = User::orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'trips', 'users'));
    }

    /**
     * Display the specified ticket.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['trip.route', 'trip.vehicle', 'trip.driver', 'user', 'seat', 'payment'])
            ->findOrFail($id);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $trips = Trip::where('status', 'scheduled')
            ->where('departure_date', '>=', now()->format('Y-m-d'))
            ->with('route')
            ->orderBy('departure_date')
            ->get();

        $users = User::orderBy('name')->get();

        return view('admin.tickets.create', compact('trips', 'users'));
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'user_id' => 'required|exists:users,id',
            'seat_id' => 'required|exists:seats,id',
            'passenger_name' => 'required|string|max:255',
            'passenger_phone' => 'required|string|max:20',
            'passenger_email' => 'nullable|email|max:255',
            'status' => 'required|in:pending,confirmed,cancelled',
            'payment_method' => 'required|in:cod,vnpay,momo',
            'payment_status' => 'required|in:pending,completed,failed,refunded',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if seat is available
        $trip = Trip::findOrFail($request->trip_id);

        $existingTicket = Ticket::where('trip_id', $trip->id)
            ->where('seat_id', $request->seat_id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingTicket) {
            return redirect()->back()->withErrors(['seat_id' => 'Ghế này đã được đặt'])->withInput();
        }

        // Create ticket
        $ticket = new Ticket();
        $ticket->generateTicketNumber();
        $ticket->trip_id = $trip->id;
        $ticket->user_id = $request->user_id;
        $ticket->seat_id = $request->seat_id;
        $ticket->price = $trip->price;
        $ticket->passenger_name = $request->passenger_name;
        $ticket->passenger_phone = $request->passenger_phone;
        $ticket->passenger_email = $request->passenger_email;
        $ticket->status = $request->status;
        $ticket->save();

        // Create payment
        $ticket->payment()->create([
            'payment_method' => $request->payment_method,
            'amount' => $ticket->price,
            'status' => $request->payment_status,
            'paid_at' => $request->payment_status == 'completed' ? now() : null,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.tickets.index')->with('success', 'Vé đã được tạo thành công');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'payment_status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $ticket->status = $request->status;
        $ticket->save();

        // Update payment if it exists
        if ($ticket->payment) {
            $ticket->payment->status = $request->payment_status;
            if ($request->payment_status == 'completed' && !$ticket->payment->paid_at) {
                $ticket->payment->paid_at = now();
            }
            $ticket->payment->save();
        }

        return redirect()->back()->with('success', 'Trạng thái vé đã được cập nhật');
    }

    /**
     * Export tickets to CSV.
     */
    public function export(Request $request)
    {
        $query = Ticket::with(['trip.route', 'user', 'payment', 'seat']);

        // Apply the same filters as in index
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('trip_id') && !empty($request->trip_id)) {
            $query->where('trip_id', $request->trip_id);
        }

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $filename = 'tickets_export_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'Mã vé',
            'Ngày tạo',
            'Tuyến đường',
            'Ngày khởi hành',
            'Giờ khởi hành',
            'Khách hàng',
            'Tên hành khách',
            'SĐT hành khách',
            'Email hành khách',
            'Số ghế',
            'Giá vé',
            'Trạng thái vé',
            'Phương thức thanh toán',
            'Trạng thái thanh toán'
        ];

        $callback = function() use ($tickets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tickets as $ticket) {
                $row = [
                    $ticket->ticket_number,
                    $ticket->created_at->format('d/m/Y H:i'),
                    $ticket->trip->route->departure_location . ' - ' . $ticket->trip->route->arrival_location,
                    $ticket->trip->departure_date,
                    $ticket->trip->departure_time,
                    $ticket->user->name,
                    $ticket->passenger_name,
                    $ticket->passenger_phone,
                    $ticket->passenger_email,
                    $ticket->seat->seat_number,
                    number_format($ticket->price) . ' VND',
                    $ticket->status,
                    $ticket->payment ? $ticket->payment->payment_method : 'N/A',
                    $ticket->payment ? $ticket->payment->status : 'N/A'
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
