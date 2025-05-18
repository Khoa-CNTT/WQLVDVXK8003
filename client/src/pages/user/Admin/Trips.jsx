import React, { useState } from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import ReusableModal from '../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../components/ReusableTable/ReusableTable';

const Trips = () => {
  const [trips, setTrips] = useState([
    {
      id: 1,
      route: 'Hà Nội - Sài Gòn',
      vehicle: 'Xe giường nằm 45 chỗ',
      driver: 'Nguyễn Văn A',
      departure_date: '2025-05-20',
      departure_time: '08:00',
      status: 'completed',
    },
    {
      id: 2,
      route: 'Đà Nẵng - Nha Trang',
      vehicle: 'Xe limousine 16 chỗ',
      driver: 'Trần Văn B',
      departure_date: '2025-05-22',
      departure_time: '13:30',
      status: 'pending',
    },
    {
      id: 3,
      route: 'Huế - Hà Nội',
      vehicle: 'Xe thường 29 chỗ',
      driver: 'Lê Thị C',
      departure_date: '2025-05-25',
      departure_time: '06:00',
      status: 'canceled',
    },
    {
      id: 4,
      route: 'Sài Gòn - Phan Thiết',
      vehicle: 'Xe giường nằm 40 chỗ',
      driver: 'Phạm Văn D',
      departure_date: '2025-05-28',
      departure_time: '10:15',
      status: 'completed',
    },
    {
      id: 5,
      route: 'Nha Trang - Đà Lạt',
      vehicle: 'Xe limousine 12 chỗ',
      driver: 'Trần Thị E',
      departure_date: '2025-06-01',
      departure_time: '14:45',
      status: 'pending',
    },
    {
      id: 6,
      route: 'Hà Nội - Hải Phòng',
      vehicle: 'Xe thường 29 chỗ',
      driver: 'Nguyễn Văn F',
      departure_date: '2025-06-05',
      departure_time: '07:30',
      status: 'completed',
    },

  ]);

  const [showModal, setShowModal] = useState(false);
  const [editingTrip, setEditingTrip] = useState(null);
  const [newTrip, setNewTrip] = useState({
    route: '',
    vehicle: '',
    driver: '',
    departure_date: '',
    departure_time: '',
    status: 'pending',
  });

  const handleEdit = (trip) => {
    setEditingTrip(trip);
    setNewTrip({
      route: trip.route,
      vehicle: trip.vehicle,
      driver: trip.driver,
      departure_date: trip.departure_date,
      departure_time: trip.departure_time,
      status: trip.status,
    });
    setShowModal(true);
  };

  const handleDelete = (trip) => {
    if (window.confirm(`Bạn có chắc chắn muốn xóa chuyến xe ID ${trip.id}?`)) {
      setTrips(trips.filter((t) => t.id !== trip.id));
    }
  };

  const handleSaveTrip = () => {
    if (editingTrip) {
      setTrips(trips.map(t =>
        t.id === editingTrip.id ? { ...editingTrip, ...newTrip } : t
      ));
    } else {
      const newId = trips.length ? Math.max(...trips.map(t => t.id)) + 1 : 1;
      setTrips([...trips, { id: newId, ...newTrip }]);
    }

    setShowModal(false);
    setEditingTrip(null);
    setNewTrip({
      route: '',
      vehicle: '',
      driver: '',
      departure_date: '',
      departure_time: '',
      status: 'pending',
    });
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'route', label: 'Tuyến đường' },
    { key: 'vehicle', label: 'Phương tiện' },
    { key: 'driver', label: 'Tài xế' },
    { key: 'departure_date', label: 'Ngày khởi hành' },
    { key: 'departure_time', label: 'Thời gian khởi hành' },
    {
      key: 'status',
      label: 'Trạng thái',
      render: (status) => {
        const map = {
          completed: 'status-success',
          pending: 'status-pending',
          canceled: 'status-canceled',
        };
        const label = {
          completed: 'Hoàn thành',
          pending: 'Chờ chạy',
          canceled: 'Đã hủy',
        };
        return <span className={map[status]}>{label[status]}</span>;
      },
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
              setEditingTrip(null);
              setNewTrip({
                route: '',
                vehicle: '',
                driver: '',
                departure_date: '',
                departure_time: '',
                status: 'pending',
              });
              setShowModal(true);
            }}
          >
            Thêm Chuyến Xe
          </button>
        </div>

        <ReusableTable
          columns={[...columns, { key: 'actions', label: 'Hành động' }]}
          data={trips.map((trip) => ({
            ...trip,
            actions: (
              <div className="action-buttons">
                <button className="edit-btn" onClick={() => handleEdit(trip)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDelete(trip)}>Xóa</button>
              </div>
            ),
          }))}
          loading={false}
          error={null}
        />

        <ReusableModal
          title={editingTrip ? 'Sửa Chuyến Xe' : 'Thêm Chuyến Xe'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingTrip(null);
            setNewTrip({
              route: '',
              vehicle: '',
              driver: '',
              departure_date: '',
              departure_time: '',
              status: 'pending',
            });
          }}
          onSubmit={handleSaveTrip}
        >
          <div className="form-group">
            <label>Tuyến đường</label>
            <input
              type="text"
              value={newTrip.route}
              onChange={(e) => setNewTrip({ ...newTrip, route: e.target.value })}
              placeholder="VD: Đà Nẵng - Huế"
            />
          </div>
          <div className="form-group">
            <label>Phương tiện</label>
            <input
              type="text"
              value={newTrip.vehicle}
              onChange={(e) => setNewTrip({ ...newTrip, vehicle: e.target.value })}
              placeholder="VD: Xe 45 chỗ"
            />
          </div>
          <div className="form-group">
            <label>Tài xế</label>
            <input
              type="text"
              value={newTrip.driver}
              onChange={(e) => setNewTrip({ ...newTrip, driver: e.target.value })}
              placeholder="VD: Nguyễn Văn A"
            />
          </div>
          <div className="form-group">
            <label>Ngày khởi hành</label>
            <input
              type="date"
              value={newTrip.departure_date}
              onChange={(e) => setNewTrip({ ...newTrip, departure_date: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Thời gian khởi hành</label>
            <input
              type="time"
              value={newTrip.departure_time}
              onChange={(e) => setNewTrip({ ...newTrip, departure_time: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Trạng thái</label>
            <select
              value={newTrip.status}
              onChange={(e) => setNewTrip({ ...newTrip, status: e.target.value })}
            >
              <option value="pending">Chờ chạy</option>
              <option value="completed">Hoàn thành</option>
              <option value="canceled">Đã hủy</option>
            </select>
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Trips;
