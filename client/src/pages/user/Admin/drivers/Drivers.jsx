import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';

const Drivers = () => {
  const api = useApi();

  const [drivers, setDrivers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [editingDriver, setEditingDriver] = useState(null);

  const [formDriver, setFormDriver] = useState({
    name: '',
    phone: '',
    birth_date: '',
    experience_years: '',
    license_number: '',
    license_expiry: '',
    status: 'active',
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
        console.log('drivesData', data)
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
    setFormDriver({
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
    setFormDriver({
      name: driver.name || '',
      phone: driver.phone || '',
      birth_date: driver.birth_date ? driver.birth_date.slice(0, 10) : '',
      experience_years: driver.experience_years || '',
      license_number: driver.license_number || '',
      license_expiry: driver.license_expiry ? driver.license_expiry.slice(0, 10) : '',
      status: driver.status || 'active',
    });
    setShowModal(true);
  };

  const handleSaveDriver = async () => {
    try {
      const payload = { ...formDriver };
      console.log('payload trước khi gửi', payload)

      if (editingDriver) {
        await api.put(`/admin/drivers/${editingDriver.id}`, payload);
        alert('Cập nhật tài xế thành công');
      } else {
        await api.post(`/admin/drivers`, payload);
        alert('Tạo tài xế thành công');
      }

      setShowModal(false);
      setEditingDriver(null);
      resetForm();
      const data = await fetchSortedData(api, '/admin/drivers');
      setDrivers(data);
    } catch (error) {
      console.error('Lỗi khi lưu tài xế:', error);
      alert('Có lỗi xảy ra khi lưu tài xế.');
    }
  };

  const handleDeleteDriver = async (id) => {
    if (window.confirm(`Bạn có chắc muốn xóa tài xế ID ${id}?`)) {
      try {
        await api.delete(`/admin/drivers/${id}`);
        alert('Đã xóa tài xế.');
        const data = await fetchSortedData(api, '/admin/drivers');
        setDrivers(data);
      } catch (error) {
        alert('Lỗi khi xóa tài xế.');
      }
    }
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Họ tên', key: 'name' },
    { label: 'Số điện thoại', key: 'phone' },
    {
      label: 'Ngày sinh', key: 'birth_date',
      render: (value) => new Date(value).toLocaleDateString('vi-VN')
    },
    { label: 'Kinh nghiệm (năm)', key: 'experience_years' },
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
          onSubmit={handleSaveDriver}
        >
          <div className="form-group">
            <label>Họ tên:</label>
            <input
              type="text"
              value={formDriver.name}
              onChange={(e) => setFormDriver({ ...formDriver, name: e.target.value })}
              placeholder="VD: Trần Văn Tài"
            />
          </div>
          <div className="form-group">
            <label>Số điện thoại:</label>
            <input
              type="text"
              value={formDriver.phone}
              onChange={(e) => setFormDriver({ ...formDriver, phone: e.target.value })}
              placeholder="VD: 0905123456"
            />
          </div>
          <div className="form-group">
            <label>Ngày sinh:</label>
            <input
              type="date"
              value={formDriver.birth_date}
              onChange={(e) => setFormDriver({ ...formDriver, birth_date: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Kinh nghiệm (năm):</label>
            <input
              type="number"
              value={formDriver.experience_years}
              onChange={(e) => setFormDriver({ ...formDriver, experience_years: e.target.value })}
              placeholder="VD: 5"
            />
          </div>
          <div className="form-group">
            <label>Số giấy phép lái xe:</label>
            <input
              type="text"
              value={formDriver.license_number}
              onChange={(e) => setFormDriver({ ...formDriver, license_number: e.target.value })}
              placeholder="VD: B2-123456"
            />
          </div>

          <div className="form-group">
            <label>Ngày hết hạn GPLX:</label>
            <input
              type="date"
              value={formDriver.license_expiry}
              onChange={(e) => setFormDriver({ ...formDriver, license_expiry: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select
              value={formDriver.status}
              onChange={(e) => setFormDriver({ ...formDriver, status: e.target.value })}
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

export default Drivers;
