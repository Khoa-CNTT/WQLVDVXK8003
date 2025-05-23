import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';

const Lines = () => {
  const api = useApi();

  const [lines, setLines] = useState([]);
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [editingLine, setEditingLine] = useState(null);

  const [formLine, setFormLine] = useState({
    departure: '',
    destination: '',
    distance: '',
    duration: '',
    base_price: '',
    description: '',
    status: 'active',
  });

  // Load lines and vehicles
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const linesData = await fetchSortedData(api, '/admin/lines');
        const vehiclesData = await fetchSortedData(api, '/admin/vehicles');
        setLines(linesData);
        setVehicles(vehiclesData);
      } catch (err) {
        setError(err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  // Hàm tính tổng số ghế của line dựa vào trips và vehicles
  const calculateTotalSeats = (trips) => {
    if (!trips || trips.length === 0) return 0;
    let totalSeats = 0;
    trips.forEach(trip => {
      const vehicle = vehicles.find(v => v.id === trip.vehicle_id);
      if (vehicle) {
        totalSeats += vehicle.capacity;
      }
    });
    return totalSeats;
  };

  // Lấy ngày đi đầu tiên trong trips của line
  const getFirstDepartureDate = (trips) => {
    if (!trips || trips.length === 0) return '-';
    // Lấy ngày departure_time nhỏ nhất
    const sortedTrips = trips.slice().sort((a, b) => new Date(a.departure_time) - new Date(b.departure_time));
    const firstDate = new Date(sortedTrips[0].departure_time);
    return firstDate.toLocaleDateString('vi-VN');
  };

  const handleSaveLine = async () => {
    try {
      const payload = {
        departure: formLine.departure,
        destination: formLine.destination,
        distance: parseFloat(formLine.distance),
        duration: formLine.duration,
        base_price: parseFloat(formLine.base_price),
        description: formLine.description,
        status: formLine.status,
      };

      if (editingLine) {
        await api.put(`/admin/lines/${editingLine.id}`, payload);
        alert('Cập nhật tuyến thành công');
      } else {
        await api.post('/admin/lines', payload);
        alert('Tạo tuyến thành công');
      }

      setShowModal(false);
      setEditingLine(null);
      resetForm();

      // Reload data
      const linesData = await fetchSortedData(api, '/admin/lines');
      setLines(linesData);

    } catch (error) {
      console.error('Lỗi khi lưu tuyến:', error);
      alert('Lỗi khi lưu tuyến');
    }
  };

  const handleDeleteLine = async (id) => {
    if (window.confirm(`Bạn có chắc muốn xóa tuyến ID ${id}?`)) {
      try {
        await api.delete(`/admin/lines/${id}`);
        alert('Xóa tuyến thành công');
        const linesData = await fetchSortedData(api, '/admin/lines');
        setLines(linesData);
      } catch (error) {
        alert('Lỗi khi xóa tuyến');
      }
    }
  };

  const handleEditLine = (line) => {
    setEditingLine(line);
    setFormLine({
      departure: line.departure || '',
      destination: line.destination || '',
      distance: line.distance || '',
      duration: line.duration || '',
      base_price: line.base_price || '',
      description: line.description || '',
      status: line.status || 'active',
    });
    setShowModal(true);
  };

  const resetForm = () => {
    setFormLine({
      departure: '',
      destination: '',
      distance: '',
      duration: '',
      base_price: '',
      description: '',
      status: 'active',
    });
  };

  const columns = [
    { label: 'ID', key: 'id' },
    {
      label: 'Tuyến đường',
      key: 'route',
      render: (_, line) => (
        <span>{line.departure} - {line.destination}</span>
      ),
    },
    {
      label: 'Số ghế',
      key: 'total_seats',
    },
    {
      label: 'Ngày đi',
      key: 'departure_date',
    },
    {
      label: 'Đơn giá',
      key: 'base_price',
      render: (value) => value ? value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' }) : '-',
    },
    {
      label: 'Hành động',
      key: 'actions',
    },
  ];

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Tuyến</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              resetForm();
              setShowModal(true);
            }}
          >
            Thêm Tuyến
          </button>
        </div>
        <ReusableTable
          columns={columns}
          data={lines.map(line => ({
            ...line,
            total_seats: calculateTotalSeats(line.trips),
            departure_date: getFirstDepartureDate(line.trips),
            base_price: parseFloat(line.base_price),
            actions: (
              <div className="action-buttons">
                <button className="add-btn" onClick={() => alert(`Xem lịch sử đặt vé cho tuyến ID ${line.id}`)}>Lịch sử đặt vé</button>
                <button className="edit-btn" onClick={() => handleEditLine(line)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDeleteLine(line.id)}>Xóa</button>
              </div>
            ),
          }))}
          loading={loading}
        />

        <ReusableModal
          title={editingLine ? 'Sửa Tuyến' : 'Thêm Tuyến'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingLine(null);
          }}
          onSubmit={handleSaveLine}
        >
          <div className="form-group">
            <label>Nơi xuất phát:</label>
            <input
              type="text"
              value={formLine.departure}
              onChange={(e) => setFormLine({ ...formLine, departure: e.target.value })}
              placeholder="VD: Đà Nẵng"
            />
          </div>
          <div className="form-group">
            <label>Nơi đến:</label>
            <input
              type="text"
              value={formLine.destination}
              onChange={(e) => setFormLine({ ...formLine, destination: e.target.value })}
              placeholder="VD: Quảng Bình"
            />
          </div>
          <div className="form-group">
            <label>Khoảng cách (km):</label>
            <input
              type="number"
              value={formLine.distance}
              onChange={(e) => setFormLine({ ...formLine, distance: e.target.value })}
              placeholder="VD: 300"
            />
          </div>
          <div className="form-group">
            <label>Thời gian (phút):</label>
            <input
              type="text"
              value={formLine.duration}
              onChange={(e) => setFormLine({ ...formLine, duration: e.target.value })}
              placeholder="VD: 240"
            />
          </div>
          <div className="form-group">
            <label>Đơn giá (VND):</label>
            <input
              type="number"
              value={formLine.base_price}
              onChange={(e) => setFormLine({ ...formLine, base_price: e.target.value })}
              placeholder="VD: 300000"
            />
          </div>
          <div className="form-group">
            <label>Mô tả:</label>
            <textarea
              value={formLine.description}
              onChange={(e) => setFormLine({ ...formLine, description: e.target.value })}
              placeholder="Mô tả tuyến đường"
            />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select
              value={formLine.status}
              onChange={(e) => setFormLine({ ...formLine, status: e.target.value })}
            >
              <option value="active">Hoạt động</option>
              <option value="inactive">Không hoạt động</option>
            </select>
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Lines;
