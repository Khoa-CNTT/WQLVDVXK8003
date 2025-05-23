import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';

const Vehicles = () => {
  const api = useApi();
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [editingVehicle, setEditingVehicle] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);

  const [newVehicle, setNewVehicle] = useState({
    name: '',
    license_plate: '',
    type: '',
    model: '',
    capacity: '',
    manufacture_year: '',
    status: '',
    amenities: '',
  });

  useEffect(() => {
    const fetchVehicles = async () => {
      try {
        setLoading(true);
        const sortedData = await fetchSortedData(api, '/admin/vehicles');
        const mappedVehicles = sortedData.map(vehicle => ({
          id: vehicle.id,
          licensePlate: vehicle.license_plate,
          type: vehicle.type,
          seats: vehicle.capacity,
          year: vehicle.manufacture_year,
          status: vehicle.status,
          name: vehicle.name,
          description: vehicle.description,
          lastMaintenance: vehicle.last_maintenance,
          createdAt: vehicle.created_at,
          updatedAt: vehicle.updated_at,
        }));
        setVehicles(mappedVehicles);
      } catch (err) {
        console.error('Lỗi khi tải dữ liệu phương tiện:', err);
        setError(err);
      } finally {
        setLoading(false);
      }
    };

    fetchVehicles();
  }, [refreshKey]);

  const createVehicleAPI = async (vehicle) => {
    const payload = {
      name: vehicle.name,
      license_plate: vehicle.license_plate,
      type: vehicle.type,
      model: vehicle.model,
      capacity: parseInt(vehicle.capacity, 10),
      manufacture_year: parseInt(vehicle.manufacture_year, 10),
      status: vehicle.status,
      amenities: vehicle.amenities,
    };

    const response = await api.post('/admin/vehicles', payload);
    return response.data;
  };

  const updateVehicleAPI = async (vehicleId, vehicle) => {
    try {
      const payload = {
        name: vehicle.name,
        license_plate: vehicle.license_plate,
        type: vehicle.type,
        model: vehicle.model,
        capacity: parseInt(vehicle.capacity, 10),
        manufacture_year: parseInt(vehicle.manufacture_year, 10),
        status: vehicle.status,
        amenities: vehicle.amenities,
      };

      const response = await api.put(`/admin/vehicles/${vehicleId}`, payload);
      return response.data;
    } catch (error) {
      const message = error.response?.data?.message || 'Lỗi khi cập nhật phương tiện';
      alert(message);
      throw new Error(message);
    }
  };

  const deleteVehicleAPI = async (vehicleId) => {
    await api.delete(`/admin/vehicles/${vehicleId}`);
  };

  const handleDelete = async (id) => {
    if (window.confirm(`Xóa xe có ID: ${id}?`)) {
      try {
        await deleteVehicleAPI(id);
        setRefreshKey(prev => prev + 1);
        alert('Xóa thành công!');
      } catch (err) {
        alert('Lỗi khi xoá phương tiện');
      }
    }
  };

  const handleSaveVehicle = async () => {
    try {
      if (editingVehicle) {
        await updateVehicleAPI(editingVehicle.id, newVehicle);
        alert('Cập nhật phương tiện thành công!');
      } else {
        await createVehicleAPI(newVehicle);
        alert('Thêm phương tiện thành công!');
      }

      setShowModal(false);
      setEditingVehicle(null);
      setNewVehicle({
        name: '',
        license_plate: '',
        type: '',
        model: '',
        capacity: '',
        manufacture_year: '',
        status: '',
        amenities: '',
      });
      setRefreshKey(prev => prev + 1);
    } catch (error) {
      console.error('Lỗi khi lưu phương tiện:', error);
    }
  };

  const handleEdit = (vehicle) => {
    setEditingVehicle(vehicle);
    setNewVehicle({
      name: vehicle.name || '',
      license_plate: vehicle.licensePlate || '',
      type: vehicle.type || '',
      model: vehicle.model || '',
      capacity: vehicle.seats || '',
      manufacture_year: vehicle.year || '',
      status: vehicle.status || '',
      amenities: vehicle.amenities || '',
    });
    setShowModal(true);
  };

  const statusMap = {
    active: { className: 'text-green-600 font-bold', label: 'Hoạt động' },
    inactive: { className: 'text-red-600 font-bold', label: 'Không hoạt động' },
    maintenance: { className: 'text-yellow-500 font-bold', label: 'Bảo trì' },
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Biển số xe', key: 'licensePlate' },
    { label: 'Loại xe', key: 'type' },
    { label: 'Số ghế', key: 'seats' },
    { label: 'Năm sản xuất', key: 'year' },
    {
      label: 'Trạng thái',
      key: 'status',
      render: (value) => {
        const status = statusMap[value] || { className: '', label: value };
        return <span className={status.className}>{status.label}</span>;
      }
    },
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
              setNewVehicle({
                name: '',
                license_plate: '',
                type: '',
                model: '',
                capacity: '',
                manufacture_year: '',
                status: '',
                amenities: '',
              });
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
          loading={loading}
        />

        <ReusableModal
          title={editingVehicle ? 'Sửa Phương Tiện' : 'Thêm Phương Tiện Mới'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingVehicle(null);
            setNewVehicle({
              name: '',
              license_plate: '',
              type: '',
              model: '',
              capacity: '',
              manufacture_year: '',
              status: '',
              amenities: '',
            });
          }}
          onSubmit={handleSaveVehicle}
        >
          {/* Form input fields */}
          <div className="form-group">
            <label>Tên xe:</label>
            <input
              type="text"
              value={newVehicle.name}
              onChange={(e) => setNewVehicle({ ...newVehicle, name: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Biển số xe:</label>
            <input
              type="text"
              value={newVehicle.license_plate}
              onChange={(e) => setNewVehicle({ ...newVehicle, license_plate: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Loại xe:</label>
            <select
              value={newVehicle.type}
              onChange={(e) => setNewVehicle({ ...newVehicle, type: e.target.value })}
            >
              <option value="">-- Chọn loại xe --</option>
              <option value="sleeper">Sleeper</option>
              <option value="seater">Seater</option>
              <option value="limousine">Limousine</option>
              <option value="vip">VIP</option>
            </select>
          </div>
          <div className="form-group">
            <label>Model:</label>
            <input
              type="text"
              value={newVehicle.model}
              onChange={(e) => setNewVehicle({ ...newVehicle, model: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Số ghế:</label>
            <input
              type="number"
              value={newVehicle.capacity}
              onChange={(e) => setNewVehicle({ ...newVehicle, capacity: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Năm sản xuất:</label>
            <input
              type="number"
              value={newVehicle.manufacture_year}
              onChange={(e) => setNewVehicle({ ...newVehicle, manufacture_year: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select
              value={newVehicle.status}
              onChange={(e) => setNewVehicle({ ...newVehicle, status: e.target.value })}
            >
              <option value="">-- Chọn trạng thái --</option>
              <option value="active">Hoạt động</option>
              <option value="inactive">Không hoạt động</option>
              <option value="maintenance">Bảo trì</option>
            </select>
          </div>
          <div className="form-group">
            <label>Tiện nghi:</label>
            <input
              type="text"
              value={newVehicle.amenities}
              onChange={(e) => setNewVehicle({ ...newVehicle, amenities: e.target.value })}
            />
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Vehicles;
