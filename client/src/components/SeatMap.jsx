import React, { useState } from 'react';
import './SeatMap.css';

export default function SeatMap({ seats, seatsPerRow = 4, onSelect }) {
  const [selected, setSelected] = useState([]);

  // Chia ghế thành các hàng
  const rows = [];
  for (let i = 0; i < seats.length; i += seatsPerRow) {
    rows.push(seats.slice(i, i + seatsPerRow));
  }

  const handleSelect = (seat) => {
    if (seat.status === 'sold') return;
    let newSelected;
    if (selected.includes(seat.id)) {
      newSelected = selected.filter(id => id !== seat.id);
    } else {
      newSelected = [...selected, seat.id];
    }
    setSelected(newSelected);
    if (onSelect) onSelect(newSelected);
  };

  return (
    <div className="seatmap-container">
      {rows.map((row, idx) => (
        <div className="seat-row" key={idx}>
          {row.map(seat => (
            <button
              key={seat.code}
              className={`seat-btn 
                ${seat.status === 'sold' ? 'sold' : ''}
                ${selected.includes(seat.id) ? 'selected' : ''}
                ${seat.status === 'available' ? 'available' : ''}
              `}
              disabled={seat.status === 'sold'}
              onClick={() => handleSelect(seat)}
            >
              {seat.code}
            </button>
          ))}
        </div>
      ))}
      <div className="seat-legend">
        <span className="seat-legend-btn sold">Đã bán</span>
        <span className="seat-legend-btn available">Còn trống</span>
        <span className="seat-legend-btn selected">Đang chọn</span>
      </div>
      <div>Ghế đã chọn: {selected.join(', ') || 'Chưa chọn'}</div>
    </div>
  );
} 