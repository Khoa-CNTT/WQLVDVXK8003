import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';
import { toast } from 'react-toastify';
import { confirmAction } from '../../../../utils/confirm';

import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import vehicleSchema from './VehicleSchema';



const Vehicles = () => {
  const api = useApi();
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [editingVehicle, setEditingVehicle] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);

  // Thay thế formData + handleChange bằng react-hook-form + yup
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(vehicleSchema),
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
          amenities: vehicle.amenities || '',
          model: vehicle.model || '',
        }));
        setVehicles(mappedVehicles);
        setError(null);
      } catch (err) {
        setError(err);
      } finally {
        setLoading(false);
      }
    };

    fetchVehicles();
  }, [refreshKey]);

  // Giữ nguyên các api create/update/delete

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
      toast.error(error.response?.data?.message || 'Lỗi khi cập nhật phương tiện');
      throw error;
    }
  };

  const deleteVehicleAPI = async (vehicleId) => {
    await api.delete(`/admin/vehicles/${vehicleId}`);
  };

  const handleDelete = async (id) => {
    confirmAction({
      title: "Bạn có chắc muốn xoá phương tiện này?",
      text: "Dữ liệu sẽ không thể khôi phục.",
      onConfirm: () => {
        deleteVehicleAPI(id)
          .then(() => {
            toast.success("Đã xoá thành công");
            setRefreshKey(prev => prev + 1);
          })
          .catch(() => {
            toast.error("Xoá thất bại");
          });
      }
    });
  };

  // Thay thế hàm submit để dùng handleSubmit react-hook-form
  const onSubmit = async (data) => {
    try {
      if (editingVehicle) {
        await updateVehicleAPI(editingVehicle.id, data);
        toast.success('Cập nhật phương tiện thành công!');
      } else {
        await createVehicleAPI(data);
        toast.success('Thêm phương tiện thành công!');
      }
      setShowModal(false);
      setEditingVehicle(null);
      reset();
      setRefreshKey(prev => prev + 1);
    } catch (error) {
      toast.error(error.message || 'Lỗi khi lưu phương tiện');
    }
  };

  const openModalForEdit = (vehicle) => {
    setEditingVehicle(vehicle);
    reset({
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

  const openModalForAdd = () => {
    setEditingVehicle(null);
    reset({
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
  };

  const statusMap = {
    active: { className: 'text-green-600 font-bold', label: 'Hoạt động' },
    inactive: { className: 'text-red-600 font-bold', label: 'Không hoạt động' },
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Biển số xe', key: 'licensePlate' },
    { label: 'Tên xe', key: 'name' },
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
            onClick={openModalForAdd}
          >
            Thêm Phương Tiện
          </button>
        </div>

        {error && <p className="error-message">Lỗi: {error.message || 'Không thể tải dữ liệu'}</p>}

        <ReusableTable
          columns={columns}
          data={vehicles.map(vehicle => ({
            ...vehicle,
            actions: (
              <div className="action-buttons">
                <button
                  className="edit-btn"
                  onClick={() => openModalForEdit(vehicle)}
                >
                  Sửa
                </button>
                <button
                  className="delete-btn"
                  onClick={() => handleDelete(vehicle.id)}
                >
                  Xóa
                </button>
              </div>
            )
          }))}
          loading={loading}
        />

        {/* Sửa modal form để dùng react-hook-form */}
        <ReusableModal
          title={editingVehicle ? 'Sửa Phương Tiện' : 'Thêm Phương Tiện Mới'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingVehicle(null);
            reset();
          }}
          onSubmit={handleSubmit(onSubmit)} // dùng handleSubmit từ react-hook-form
        >
          <div className="">
            <label>Tên xe:</label>
            <input
              type="text"
              placeholder="Nhập tên xe"
              {...register('name')}
            />
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.name?.message}</p>
          </div>

          <div className="">
            <label>Biển số xe:</label>
            <input
              type="text"
              placeholder="VD: 43A-12345"
              {...register('license_plate')}
            />
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.license_plate?.message}</p>
          </div>

          <div className="form-group">
            <label>Loại xe:</label>
            <select {...register('type')}>
              <option value="">-- Chọn loại xe --</option>
              <option value="sleeper">Sleeper</option>
              <option value="seater">Seater</option>
              <option value="limousine">Limousine</option>
              <option value="vip">VIP</option>
            </select>
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.type?.message}</p>
          </div>

          <div className="">
            <label>Số ghế:</label>
            <input
              type="number"
              placeholder="VD: 45"
              {...register('capacity')}
            />
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.capacity?.message}</p>
          </div>

          <div className="">
            <label>Năm sản xuất:</label>
            <input
              type="number"
              placeholder="VD: 2020"
              {...register('manufacture_year')}
            />
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.manufacture_year?.message}</p>
          </div>

          <div className="form-group">
            <label>Trạng thái:</label>
            <select {...register('status')}>
              <option value="">-- Chọn trạng thái --</option>
              <option value="active">Hoạt động</option>
              <option value="inactive">Không hoạt động</option>
            </select>
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.status?.message}</p>
          </div>

          <div className="">
            <label>Mô tả tiện nghi (amenities):</label>
            <input
              type="text"
              placeholder="VD: Wifi, điều hòa..."
              {...register('amenities')}
            />
            <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.amenities?.message}</p>
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Vehicles;
