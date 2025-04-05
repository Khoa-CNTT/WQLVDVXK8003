<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Bus;
use App\Models\Route;

class TicketController extends Controller
{
    // Lấy danh sách vé
    public function index()
    {
        return response()->json(Ticket::with(['bus', 'route'])->get());
    }

    // Đặt vé
    public function store(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'route_id' => 'required|exists:routes,id',
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'seat_number' => 'required|integer|min:1',
        ]);

        // Kiểm tra ghế đã đặt chưa
        $existingTicket = Ticket::where('bus_id', $request->bus_id)
            ->where('seat_number', $request->seat_number)
            ->where('status', '!=', 'canceled')
            ->exists();

        if ($existingTicket) {
            return response()->json(['message' => 'Ghế đã được đặt!'], 400);
        }

        $ticket = Ticket::create($request->all());
        return response()->json($ticket, 201);
    }

    // Cập nhật trạng thái thanh toán
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate(['status' => 'required|in:pending,paid,canceled']);
        $ticket->update(['status' => $request->status]);
        return response()->json($ticket);
    }

    // Hủy vé
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Vé đã được hủy']);
    }
}
