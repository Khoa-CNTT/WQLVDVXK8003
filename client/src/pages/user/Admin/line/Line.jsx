import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';
import { toast } from 'react-toastify';

import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as Yup from 'yup';
import LineSchema from './LineSchema';
import { confirmAction } from '../../../../utils';


const Lines = () => {
  const api = useApi();

  const [lines, setLines] = useState([]);
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [editingLine, setEditingLine] = useState(null);

  const {
    register,
    handleSubmit,
    reset,
    setValue,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(LineSchema),
  });

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const linesData = await fetchSortedData(api, '/admin/lines');
        const vehiclesData = await fetchSortedData(api, '/admin/vehicles');
        setLines(linesData);
        setVehicles(vehiclesData);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  const calculateTotalSeats = (trips) => {
    if (!trips || trips.length === 0) return 0;
    return trips.reduce((sum, trip) => {
      const vehicle = vehicles.find(v => v.id === trip.vehicle_id);
      return sum + (vehicle?.capacity || 0);
    }, 0);
  };

  const getFirstDepartureDate = (trips) => {
    if (!trips || trips.length === 0) return '-';
    const sorted = trips.slice().sort((a, b) => new Date(a.departure_time) - new Date(b.departure_time));
    return new Date(sorted[0].departure_time).toLocaleDateString('vi-VN');
  };

  const onSubmit = async (data) => {
    try {
      const payload = {
        ...data,
        distance: parseFloat(data.distance),
        base_price: parseFloat(data.base_price),
      };

      if (editingLine) {
        await api.put(`/admin/lines/${editingLine.id}`, payload);
        toast.success('Cập nhật tuyến thành công');
      } else {
        await api.post('/admin/lines', payload);
        toast.success('Tạo tuyến thành công');
      }

      setShowModal(false);
      setEditingLine(null);
      reset();

      const updatedLines = await fetchSortedData(api, '/admin/lines');
      setLines(updatedLines);
    } catch (error) {
      console.error('Lỗi khi lưu tuyến:', error);
      alert('Lỗi khi lưu tuyến');
    }
  };

  const handleDeleteLine = (id) => {
    confirmAction({
      title: 'Xác nhận xóa tài xế',
      text: `Bạn có chắc muốn xóa tuyến ID ${id}?`,
      onConfirm: async () => {
        try {
          await api.delete(`/admin/lines/${id}`);
          toast.success('Xóa tuyến thành công');
          const newData = await fetchSortedData(api, '/admin/lines');
          setLines(newData);
        } catch (error) {
          toast.error('Lỗi khi xóa tuyến');
        }
      },
    });
  };



  const handleEditLine = (line) => {
    setEditingLine(line);
    setValue('departure', line.departure || '');
    setValue('destination', line.destination || '');
    setValue('distance', line.distance || '');
    setValue('duration', line.duration || '');
    setValue('base_price', line.base_price || '');
    setValue('description', line.description || '');
    setValue('status', line.status || 'active');
    setShowModal(true);
  };

  const columns = [
    { label: 'ID', key: 'id' },
    {
      label: 'Tuyến đường',
      key: 'route',
      render: (_, line) => <span>{line.departure} - {line.destination}</span>,
    },
    {
      label: 'Khoảng cách',
      key: 'distance',
      render: (value) => value ? `${parseFloat(value).toFixed(0)} km` : '-',
    },
    {
      label: 'Thời gian di chuyển',
      key: 'duration',
      render: (value) => {
        const hours = Math.floor(value / 60);
        const minutes = value % 60;
        return `${hours}h ${minutes}m`; // hoặc chọn format khác như gợi ý
      },
    },
    {
      label: 'Đơn giá',
      key: 'base_price',
      render: (value) => value ? `${value.toLocaleString('vi-VN')} VNĐ` : '-',
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
              reset();
              setEditingLine(null);
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
            reset();
          }}
          onSubmit={handleSubmit(onSubmit)}
        >
          <div className="">
            <label>Nơi xuất phát:</label>
            <input type="text" {...register('departure')} placeholder="VD: Đà Nẵng" />
            {errors.departure && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.departure.message}</p>}
          </div>
          <div className="">
            <label>Nơi đến:</label>
            <input type="text" {...register('destination')} placeholder="VD: Quảng Bình" />
            {errors.destination && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.destination.message}</p>}
          </div>
          <div className="">
            <label>Khoảng cách (km):</label>
            <input type="number" {...register('distance')} placeholder="VD: 300" />
            {errors.distance && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.distance.message}</p>}
          </div>
          <div className="">
            <label>Thời gian (phút):</label>
            <input type="text" {...register('duration')} placeholder="VD: 240" />
            {errors.duration && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.duration.message}</p>}
          </div>
          <div className="">
            <label>Đơn giá (VND):</label>
            <input type="number" {...register('base_price')} placeholder="VD: 300000" />
            {errors.base_price && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.base_price.message}</p>}
          </div>
          <div className="">
            <label>Mô tả:</label>
            <textarea {...register('description')} className='w-full min-h-[80px] p-2 border border-gray-300 rounded-md' placeholder="Mô tả tuyến đường" />
            {errors.description && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.description.message}</p>}
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select {...register('status')}>
              <option value="active">Hoạt động</option>
              <option value="inactive">Không hoạt động</option>
            </select>
            {errors.status && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.status.message}</p>}
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Lines;
