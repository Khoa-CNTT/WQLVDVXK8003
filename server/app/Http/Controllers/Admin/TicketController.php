<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Hiển thị danh sách vé
     */
    public function index()
    {
        $tickets = Ticket::with(['trip.line', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Hiển thị form tạo vé mới
     */
    public function create()
    {
        $trips = Trip::with('line')
            ->where('status', 'scheduled')
            ->get();

        $users = User::where('role', 'customer')->get();

        return view('admin.tickets.create', compact('trips', 'users'));
    }

    /**
     * Lưu vé mới vào database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'user_id' => 'required|exists:users,id',
            'seat_number' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_method' => 'required|in:cash,vnpay,momo,bank_transfer',
            'payment_status' => 'required|in:pending,paid,refunded',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tạo mã vé ngẫu nhiên
        $ticketCode = 'PTX-' . strtoupper(Str::random(8));

        $data = $request->all();
        $data['ticket_code'] = $ticketCode;

        Ticket::create($data);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Vé đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết vé
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['trip.line', 'trip.driver', 'trip.vehicle', 'user']);
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Hiển thị form chỉnh sửa vé
     */
    public function edit(Ticket $ticket)
    {
        $trips = Trip::with('line')
            ->where('status', 'scheduled')
            ->orWhere('id', $ticket->trip_id)
            ->get();

        $users = User::where('role', 'customer')
            ->orWhere('id', $ticket->user_id)
            ->get();

        return view('admin.tickets.edit', compact('ticket', 'trips', 'users'));
    }

    /**
     * Cập nhật thông tin vé
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'user_id' => 'required|exists:users,id',
            'seat_number' => 'required|string',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_method' => 'in:cash,vnpay,momo,bank_transfer',
            'payment_status' => 'in:pending,paid,refunded',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $ticket->update($request->all());

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Thông tin vé đã được cập nhật thành công.');
    }

    /**
     * Xóa vé
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Vé đã được xóa thành công.');
    }

    /**
     * In vé
     */
    public function print(Ticket $ticket)
    {
        $ticket->load(['trip.line', 'trip.driver', 'trip.vehicle', 'user']);
        return view('admin.tickets.print', compact('ticket'));
    }

    /**
     * Hủy vé
     */
    public function cancel(Ticket $ticket)
    {
        $ticket->update([
            'status' => 'cancelled',
            'payment_status' => $ticket->payment_status == 'paid' ? 'refunded' : 'pending'
        ]);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Vé đã được hủy thành công.');
    }
}
