import React, { useState } from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import ReusableModal from '../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../components/ReusableTable/ReusableTable';

const Vehicles = () => {
  const [vehicles, setVehicles] = useState([
    {
      id: 1,
      licensePlate: '51A-12345',
      type: 'Ghế ngồi',
      seats: 45,
      year: 2020,
      status: 'Đang hoạt động',
    },
    {
      id: 2,
      licensePlate: '61B-67890',
      type: 'Giường nằm',
      seats: 40,
      year: 2018,
      status: 'Bảo trì',
    },
    {
      id: 3,
      licensePlate: '70C-23456',
      type: 'Ghế ngồi',
      seats: 50,
      year: 2021,
      status: 'Đang hoạt động',
    },
    {
      id: 4,
      licensePlate: '80D-34567',
      type: 'Giường nằm',
      seats: 38,
      year: 2019,
      status: 'Đang hoạt động',
    },
    {
      id: 5,
      licensePlate: '90E-45678',
      type: 'Ghế ngồi',
      seats: 42,
      year: 2022,
      status: 'Bảo trì',
    },
    {
      id: 6,
      licensePlate: '10F-56789',
      type: 'Giường nằm',
      seats: 44,
      year: 2017,
      status: 'Đang hoạt động',
    },
  ]);

  const [showModal, setShowModal] = useState(false);
  const [editingVehicle, setEditingVehicle] = useState(null);
  const [newVehicle, setNewVehicle] = useState({
    licensePlate: '',
    type: '',
    seats: '',
    year: '',
    status: '',
  });

  const handleEdit = (vehicle) => {
    setEditingVehicle(vehicle);
    setNewVehicle({ ...vehicle });
    setShowModal(true);
  };

  const handleDelete = (id) => {
    if (window.confirm(`Xóa xe có ID: ${id}?`)) {
      setVehicles(vehicles.filter(vehicle => vehicle.id !== id));
    }
  };

  const handleSaveVehicle = () => {
    if (editingVehicle) {
      // Cập nhật xe
      /*
      updateVehicleAPI(editingVehicle.id, newVehicle).then(() => {
        fetchVehicles();
      });
      */
      setVehicles(vehicles.map(vehicle =>
        vehicle.id === editingVehicle.id
          ? { ...editingVehicle, ...newVehicle, seats: parseInt(newVehicle.seats), year: parseInt(newVehicle.year) }
          : vehicle
      ));
    } else {
      // Thêm mới
      /*
      createVehicleAPI(newVehicle).then(() => {
        fetchVehicles();
      });
      */
      const newId = vehicles.length ? Math.max(...vehicles.map((v) => v.id)) + 1 : 1;
      setVehicles([
        ...vehicles,
        {
          id: newId,
          ...newVehicle,
          seats: parseInt(newVehicle.seats),
          year: parseInt(newVehicle.year),
        },
      ]);
    }

    setNewVehicle({ licensePlate: '', type: '', seats: '', year: '', status: '' });
    setEditingVehicle(null);
    setShowModal(false);
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Biển số xe', key: 'licensePlate' },
    { label: 'Loại xe', key: 'type' },
    { label: 'Số ghế', key: 'seats' },
    { label: 'Năm sản xuất', key: 'year' },
    { label: 'Trạng thái', key: 'status' },
    { label: 'Hành động', key: 'actions' },
  ];

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Phương Tiện</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              setEditingVehicle(null);
              setNewVehicle({ licensePlate: '', type: '', seats: '', year: '', status: '' });
              setShowModal(true);
            }}
          >
            Thêm Phương Tiện
          </button>
        </div>

        <ReusableTable
          columns={columns}
          data={vehicles.map((vehicle) => ({
            ...vehicle,
            actions: (
              <div className="action-buttons">
                <button className="edit-btn" onClick={() => handleEdit(vehicle)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDelete(vehicle.id)}>Xóa</button>
              </div>
            ),
          }))}
        />

        <ReusableModal
          title={editingVehicle ? 'Sửa Phương Tiện' : 'Thêm Phương Tiện Mới'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingVehicle(null);
            setNewVehicle({ licensePlate: '', type: '', seats: '', year: '', status: '' });
          }}
          onSubmit={handleSaveVehicle}
        >
          <div className="form-group">
            <label>Biển số xe:</label>
            <input
              type="text"
              value={newVehicle.licensePlate}
              onChange={(e) => setNewVehicle({ ...newVehicle, licensePlate: e.target.value })}
              placeholder="VD: 51A-12345"
            />
          </div>
          <div className="form-group">
            <label>Loại xe:</label>
            <input
              type="text"
              value={newVehicle.type}
              onChange={(e) => setNewVehicle({ ...newVehicle, type: e.target.value })}
              placeholder="VD: Ghế ngồi"
            />
          </div>
          <div className="form-group">
            <label>Số ghế:</label>
            <input
              type="number"
              value={newVehicle.seats}
              onChange={(e) => setNewVehicle({ ...newVehicle, seats: e.target.value })}
              placeholder="VD: 45"
            />
          </div>
          <div className="form-group">
            <label>Năm sản xuất:</label>
            <input
              type="number"
              value={newVehicle.year}
              onChange={(e) => setNewVehicle({ ...newVehicle, year: e.target.value })}
              placeholder="VD: 2020"
            />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <input
              type="text"
              value={newVehicle.status}
              onChange={(e) => setNewVehicle({ ...newVehicle, status: e.target.value })}
              placeholder="VD: Đang hoạt động"
            />
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Vehicles;
