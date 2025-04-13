@props(['bus', 'takenSeats', 'selectedSeats' => []])

<div class="grid grid-cols-{{ $bus->seat_layout == '2-2' ? '4' : '5' }} gap-2">
    @for($i = 1; $i <= $bus->total_seats; $i++)
        @php
            $isDriverSeat = in_array($i, [1, 2]);
            $isTaken = in_array($i, $takenSeats);
            $isSelected = in_array($i, $selectedSeats);
        @endphp

        <div
            class="seat {{ $isDriverSeat ? 'driver-seat' : '' }} {{ $isTaken ? 'taken' : 'available' }} {{ $isSelected ? 'selected' : '' }}"
            data-seat="{{ $i }}"
            @if(!$isDriverSeat && !$isTaken) onclick="toggleSeat({{ $i }})" @endif
        >
            @if($isDriverSeat)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6z" />
                </svg>
            @else
                {{ $i }}
            @endif
        </div>
    @endfor
</div>

<style>
.seat {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ccc;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.seat.available {
    background-color: #e5e7eb;
}
.seat.selected {
    background-color: #f97316;
    color: white;
    border-color: #ea580c;
}
.seat.taken {
    background-color: #d1d5db;
    border-color: #9ca3af;
    cursor: not-allowed;
}
.seat.driver-seat {
    background-color: #cbd5e1;
    border-color: #94a3b8;
    cursor: not-allowed;
}
</style>
