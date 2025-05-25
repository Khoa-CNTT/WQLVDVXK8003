import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';
import { toast } from 'react-toastify';
import { confirmAction } from '../../../../utils';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import { driverSchema } from './driverSchema';


const Drivers = () => {
  const api = useApi();

  const [drivers, setDrivers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [editingDriver, setEditingDriver] = useState(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(driverSchema),
  });

  const statusMap = {
    active: { label: 'Hoạt động', className: 'text-green-600 font-bold' },
    inactive: { label: 'Không hoạt động', className: 'text-red-600 font-bold' },
  };

  useEffect(() => {
    const fetchDrivers = async () => {
      try {
        setLoading(true);
        const data = await fetchSortedData(api, '/admin/drivers');
        setDrivers(data);
      } catch (err) {
        setError(err);
      } finally {
        setLoading(false);
      }
    };
    fetchDrivers();
  }, []);

  const resetForm = () => {
    reset({
      name: '',
      phone: '',
      birth_date: '',
      experience_years: '',
      license_number: '',
      license_expiry: '',
      status: 'active',
    });
  };

  const handleEditDriver = (driver) => {
    setEditingDriver(driver);
    reset({
      name: driver.name || '',
      phone: driver.phone || '',
      birth_date: driver.birth_date?.slice(0, 10) || '',
      experience_years: driver.experience_years || '',
      license_number: driver.license_number || '',
      license_expiry: driver.license_expiry?.slice(0, 10) || '',
      status: driver.status || 'active',
    });
    setShowModal(true);
  };

  const handleSaveDriver = async (data) => {
    try {
      if (editingDriver) {
        await api.put(`/admin/drivers/${editingDriver.id}`, data);
        toast.success('Cập nhật tài xế thành công');
      } else {
        await api.post(`/admin/drivers`, data);
        toast.success('Tạo tài xế thành công');
      }

      setShowModal(false);
      setEditingDriver(null);
      resetForm();
      const dataFetched = await fetchSortedData(api, '/admin/drivers');
      setDrivers(dataFetched);
    } catch (error) {
      console.error('Lỗi khi lưu tài xế:', error);
      toast.error('Có lỗi xảy ra khi lưu tài xế.');
    }
  };

  const handleDeleteDriver = (id) => {
    confirmAction({
      title: 'Xác nhận xóa tài xế',
      text: `Bạn có chắc muốn xóa tài xế ID ${id}?`,
      onConfirm: async () => {
        try {
          await api.delete(`/admin/drivers/${id}`);
          toast.success('Xóa tài xế thành công');
          const newData = await fetchSortedData(api, '/admin/drivers');
          setDrivers(newData);
        } catch (error) {
          toast.error('Lỗi khi xóa tài xế');
        }
      },
    });
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Họ tên', key: 'name' },
    { label: 'Số điện thoại', key: 'phone' },
    {
      label: 'Ngày sinh',
      key: 'birth_date',
      render: (value) => new Date(value).toLocaleDateString('vi-VN'),
    },
    {
      label: 'Kinh nghiệm',
      key: 'experience_years',
      render: (value) => (value != null ? `${value} năm` : '-'),
    },
    {
      label: 'Trạng thái',
      key: 'status',
      render: (value) => {
        const s = statusMap[value] || { label: value, className: '' };
        return <span className={s.className}>{s.label}</span>;
      },
    },
    {
      label: 'Hành động',
      key: 'actions',
    },
  ];

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Tài Xế</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              resetForm();
              setShowModal(true);
            }}
          >
            Thêm Tài Xế
          </button>
        </div>
        <ReusableTable
          columns={columns}
          data={drivers.map((driver) => ({
            ...driver,
            actions: (
              <div className="action-buttons">
                <button className="edit-btn" onClick={() => handleEditDriver(driver)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDeleteDriver(driver.id)}>Xóa</button>
              </div>
            ),
          }))}
          loading={loading}
        />

        <ReusableModal
          title={editingDriver ? 'Sửa Tài Xế' : 'Thêm Tài Xế'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingDriver(null);
          }}
          onSubmit={handleSubmit(handleSaveDriver)}
        >
          <div className="">
            <label>Họ tên:</label>
            <input type="text" {...register('name')} placeholder="VD: Trần Văn Tài" />
            {errors.name && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.name.message}</p>}
          </div>

          <div className="">
            <label>Số điện thoại:</label>
            <input type="text" {...register('phone')} placeholder="VD: 0905123456" />
            {errors.phone && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.phone.message}</p>}
          </div>

          <div className="form-group">
            <label>Ngày sinh:</label>
            <input type="date" {...register('birth_date')} />
            {errors.birth_date && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.birth_date.message}</p>}
          </div>

          <div className="">
            <label>Kinh nghiệm (năm):</label>
            <input type="number" {...register('experience_years')} placeholder="VD: 5" />
            {errors.experience_years && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.experience_years.message}</p>}
          </div>

          <div className="">
            <label>Số giấy phép lái xe:</label>
            <input type="text" {...register('license_number')} placeholder="VD: B2-123456" />
            {errors.license_number && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.license_number.message}</p>}
          </div>

          <div className="form-group">
            <label>Ngày hết hạn GPLX:</label>
            <input type="date" {...register('license_expiry')} />
            {errors.license_expiry && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.license_expiry.message}</p>}
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

export default Drivers;
