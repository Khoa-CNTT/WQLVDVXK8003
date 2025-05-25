import React, { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';
import { toast } from 'react-toastify';
import { confirmAction } from '../../../../utils';
import tripSchema from './tripSchema';

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

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors }
  } = useForm({
    resolver: yupResolver(tripSchema),
  });

  const statusMap = {
    active: { className: 'text-sm text-blue-500 font-bold', label: 'Đã lên lịch' },
    completed: { className: 'text-sm text-green-500 font-bold', label: 'Hoàn thành' },
    cancelled: { className: 'text-sm text-red-500 font-bold', label: 'Đã hủy' },
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
    return datetimeLocalStr.replace('T', ' ') + ':00';
  }

  useEffect(() => {
    const fetchOptions = async () => {
      try {
        const [lineRes, driverRes, vehicleRes] = await Promise.all([
          api.get('/admin/lines'),
          api.get('/admin/drivers'),
          api.get('/admin/vehicles'),
        ]);
        console.log('driverRes', driverRes)
        console.log('vehicleRes', vehicleRes)
        setLines(lineRes.data);
        setDrivers(driverRes.data.data.data);
        setVehicles(vehicleRes.data.data.data);
      } catch (err) {
        console.error('Lỗi tải danh sách:', err);
      }
    };

    fetchOptions();
    loadTrips();
  }, []);

  const handleEditTrip = (trip) => {
    setEditingTrip(trip);
    reset({
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

  const handleDeleteTrip = (id) => {
    confirmAction({
      title: 'Xác nhận chuyến xe',
      text: `Bạn có chắc muốn xóa chuyến xe ID ${id}?`,
      onConfirm: async () => {
        try {
          await api.delete(`/admin/trips/${id}`);
          toast.success('Xóa chuyến xe thành công');
          const newData = await fetchSortedData(api, '/admin/trips');
          setTrips(newData);
        } catch (error) {
          toast.error('Lỗi khi xóa chuyến xe');
        }
      },
    });
  };

  const onSubmit = async (data) => {
    try {
      const payload = {
        ...data,
        departure_time: formatDateTime(data.departure_time),
        arrival_time: formatDateTime(data.arrival_time),
      };

      if (editingTrip) {
        await api.put(`/admin/trips/${editingTrip.id}`, payload);
        toast.success('Cập nhật chuyến xe thành công');
      } else {
        await api.post('/admin/trips', payload);
        toast.success('Tạo chuyến xe thành công');
      }

      setShowModal(false);
      setEditingTrip(null);
      reset();
      loadTrips();
    } catch (err) {
      toast.error('Lỗi khi lưu chuyến xe');
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
      render: (_, row) => {
        const now = new Date();
        const arrival = new Date(row.arrival_time);
        let displayStatus = row.status;
        if (row.status === 'active' && arrival < now) {
          displayStatus = 'completed';
        }
        const s = statusMap[displayStatus] || { className: '', label: displayStatus };
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
          <button className="add-btn" onClick={() => {
            reset();
            setEditingTrip(null);
            setShowModal(true);
          }}>
            Thêm Chuyến Xe
          </button>
        </div>

        <ReusableTable columns={columns} data={trips} loading={loading} />

        <ReusableModal
          title={editingTrip ? 'Sửa Chuyến Xe' : 'Thêm Chuyến Xe'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingTrip(null);
          }}
          onSubmit={handleSubmit(onSubmit)}
        >
          <div className="form-group">
            <label>Tuyến đường:</label>
            <select {...register('line_id')}>
              <option value="">-- Chọn tuyến đường --</option>
              {lines.map((line) => (
                <option key={line.id} value={line.id}>
                  {line.departure} → {line.destination}
                </option>
              ))}
            </select>
            {errors.line_id && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.line_id.message}</p>}
          </div>

          <div className="form-group">
            <label>Phương tiện:</label>
            <select {...register('vehicle_id')}>
              <option value="">-- Chọn phương tiện --</option>
              {vehicles
                .filter(vehicle => vehicle.status !== 'inactive')
                .map((vehicle) => (
                  <option key={vehicle.id} value={vehicle.id}>
                    {vehicle.name} ({vehicle.license_plate})
                  </option>
                ))}
            </select>
            {errors.vehicle_id && (
              <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.vehicle_id.message}</p>
            )}
          </div>

          <div className="form-group">
            <label>Tài xế:</label>
            <select {...register('driver_id')}>
              <option value="">-- Chọn tài xế --</option>
              {drivers
                .filter(driver => driver.status !== 'inactive')
                .map((driver) => (
                  <option key={driver.id} value={driver.id}>
                    {driver.name} - {driver.phone}
                  </option>
                ))}
            </select>
            {errors.driver_id && (
              <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.driver_id.message}</p>
            )}
          </div>

          <div className="form-group">
            <label>Ngày & giờ khởi hành:</label>
            <input type="datetime-local" {...register('departure_time')} />
            {errors.departure_time && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.departure_time.message}</p>}
          </div>

          <div className="form-group">
            <label>Giờ đến dự kiến:</label>
            <input type="datetime-local" {...register('arrival_time')} />
            {errors.arrival_time && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.arrival_time.message}</p>}
          </div>

          <div className="">
            <label>Giá vé:</label>
            <input type="number" {...register('price')} />
            {errors.price && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.price.message}</p>}
          </div>

          <div className="form-group">
            <label>Trạng thái:</label>
            <select {...register('status')}>
              <option value="active">Đã lên lịch</option>
              <option value="completed">Đã hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
            {errors.status && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.status.message}</p>}
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Trips;
