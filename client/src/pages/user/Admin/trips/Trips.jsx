import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';

const Trips = () => {
  const api = useApi();

  const [trips, setTrips] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [lines, setLines] = useState([]);
  const [drivers, setDrivers] = useState([]);
  const [vehicles, setVehicles] = useState([]);

  const [showModal, setShowModal] = useState(false);
  const [editingTrip, setEditingTrip] = useState(null);

  const [formTrip, setFormTrip] = useState({
    line_id: '',
    vehicle_id: '',
    driver_id: '',
    departure_time: '',
    arrival_time: '',
    price: '',
    status: 'scheduled',
  });

  const statusMap = {
    scheduled: { className: 'text-blue-500 font-bold', label: 'Đã lên lịch' },
    in_progress: { className: 'text-yellow-600 font-bold', label: 'Đang chạy' },
    completed: { className: 'text-green-600 font-bold', label: 'Đã hoàn thành' },
    cancelled: { className: 'text-red-600 font-bold', label: 'Đã hủy' },
  };

  useEffect(() => {
    loadTrips();
  }, []);

  const loadTrips = async () => {
    try {
      setLoading(true);
      const data = await fetchSortedData(api, '/admin/trips');
      setTrips(data);
    } catch (err) {
      setError(err);
    } finally {
      setLoading(false);
    }
  };

  function formatDateTime(datetimeLocalStr) {
    if (!datetimeLocalStr) return '';
    // datetimeLocalStr: "2025-05-23T15:30"
    return datetimeLocalStr.replace('T', ' ') + ':00';
    // chuyển thành "2025-05-23 15:30:00"
  }

  useEffect(() => {
    const fetchOptions = async () => {
      try {
        const [lineRes, driverRes, vehicleRes] = await Promise.all([
          api.get('/admin/lines'),
          api.get('/admin/drivers'),
          api.get('/admin/vehicles'),
        ]);
        const lineData = lineRes.data
        const driverData = driverRes.data.data.data
        const vehicleData = vehicleRes.data.data.data

        setLines(lineData);
        setDrivers(driverData);
        setVehicles(vehicleData);
      } catch (err) {
        console.error('Lỗi tải danh sách:', err);
      }
    };

    fetchOptions();
    loadTrips(); // đã định nghĩa ở trên
  }, []);

  const resetForm = () => {
    setFormTrip({
      line_id: '',
      vehicle_id: '',
      driver_id: '',
      departure_time: '',
      arrival_time: '',
      price: '',
      status: 'scheduled',
    });
  };

  const handleEditTrip = (trip) => {
    setEditingTrip(trip);
    setFormTrip({
      line_id: trip.line_id,
      vehicle_id: trip.vehicle_id,
      driver_id: trip.driver_id,
      departure_time: trip.departure_time?.slice(0, 16),
      arrival_time: trip.arrival_time?.slice(0, 16),
      price: trip.price,
      status: trip.status,
    });
    setShowModal(true);
  };

  const handleDeleteTrip = async (id) => {
    if (window.confirm(`Bạn có chắc muốn xóa chuyến xe ID ${id}?`)) {
      try {
        await api.delete(`/admin/trips/${id}`);
        alert('Xóa chuyến xe thành công');
        loadTrips();
      } catch (err) {
        alert('Lỗi khi xóa chuyến xe');
      }
    }
  };

  const handleSaveTrip = async () => {
    try {
      const payload = {
        line_id: formTrip.line_id,
        vehicle_id: formTrip.vehicle_id,
        driver_id: formTrip.driver_id,
        departure_time: formatDateTime(formTrip.departure_time),
        arrival_time: formatDateTime(formTrip.arrival_time),
        price: formTrip.price,
        status: formTrip.status,
      };

      console.log('payload trước khi gửi', payload);

      if (editingTrip) {
        await api.put(`/admin/trips/${editingTrip.id}`, payload);
        alert('Cập nhật chuyến xe thành công');
      } else {
        await api.post('/admin/trips', payload);
        alert('Tạo chuyến xe thành công');
      }

      setShowModal(false);
      setEditingTrip(null);
      resetForm();
      loadTrips();
    } catch (err) {
      alert('Lỗi khi lưu chuyến xe');
    }
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Tuyến đường', key: 'line.departure', render: (_, row) => `${row.line?.departure} → ${row.line?.destination}` },
    { label: 'Phương tiện', key: 'vehicle.name', render: (_, row) => row.vehicle?.name },
    { label: 'Tài xế', key: 'driver.name', render: (_, row) => row.driver?.name },
    { label: 'Ngày khởi hành', key: 'departure_time', render: val => new Date(val).toLocaleDateString('vi-VN') },
    { label: 'Thời gian khởi hành', key: 'departure_time', render: val => new Date(val).toLocaleTimeString('vi-VN') },
    {
      label: 'Trạng thái', key: 'status',
      render: value => {
        const s = statusMap[value] || { className: '', label: value };
        return <span className={s.className}>{s.label}</span>;
      }
    },
    {
      label: 'Hành động', key: 'actions',
      render: (_, row) => (
        <div className="action-buttons">
          <button className="edit-btn" onClick={() => handleEditTrip(row)}>Sửa</button>
          <button className="delete-btn" onClick={() => handleDeleteTrip(row.id)}>Xóa</button>
        </div>
      )
    },
  ];

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Chuyến Xe</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              resetForm();
              setShowModal(true);
            }}
          >
            Thêm Chuyến Xe
          </button>
        </div>
        <ReusableTable
          columns={columns}
          data={trips}
          loading={loading}
        />

        <ReusableModal
          title={editingTrip ? 'Sửa Chuyến Xe' : 'Thêm Chuyến Xe'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingTrip(null);
          }}
          onSubmit={handleSaveTrip}
        >
          <div className="form-group">
            <label>Tuyến đường:</label>
            <select
              value={formTrip.line_id}               // sửa từ route_id thành line_id
              onChange={(e) => setFormTrip({ ...formTrip, line_id: e.target.value })} // sửa từ route_id thành line_id
            >
              <option value="">-- Chọn tuyến đường --</option>
              {lines.map((line) => (
                <option key={line.id} value={line.id}>
                  {line.departure} → {line.destination}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label>Phương tiện:</label>
            <select
              value={formTrip.vehicle_id}
              onChange={(e) => setFormTrip({ ...formTrip, vehicle_id: e.target.value })}
            >
              <option value="">-- Chọn phương tiện --</option>
              {vehicles.map((vehicle) => (
                <option key={vehicle.id} value={vehicle.id}>
                  {vehicle.name} ({vehicle.license_plate})
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label>Tài xế:</label>
            <select
              value={formTrip.driver_id}
              onChange={(e) => setFormTrip({ ...formTrip, driver_id: e.target.value })}
            >
              <option value="">-- Chọn tài xế --</option>
              {drivers.map((driver) => (
                <option key={driver.id} value={driver.id}>
                  {driver.name} - {driver.phone}
                </option>
              ))}
            </select>
          </div>
          <div className="form-group">
            <label>Ngày & giờ khởi hành:</label>
            <input type="datetime-local" value={formTrip.departure_time} onChange={e => setFormTrip({ ...formTrip, departure_time: e.target.value })} />
          </div>
          <div className="form-group">
            <label>Giờ đến dự kiến:</label>
            <input type="datetime-local" value={formTrip.arrival_time} onChange={e => setFormTrip({ ...formTrip, arrival_time: e.target.value })} />
          </div>
          <div className="form-group">
            <label>Giá vé:</label>
            <input type="number" value={formTrip.price} onChange={e => setFormTrip({ ...formTrip, price: e.target.value })} />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select value={formTrip.status} onChange={e => setFormTrip({ ...formTrip, status: e.target.value })}>
              <option value="scheduled">Đã lên lịch</option>
              <option value="in_progress">Đang chạy</option>
              <option value="completed">Đã hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Trips;
