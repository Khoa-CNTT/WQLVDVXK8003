import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import ReusableModal from '../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../components/ReusableTable/ReusableTable';

const Drivers = () => {
  const [drivers, setDrivers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const [showModal, setShowModal] = useState(false);
  const [editingDriver, setEditingDriver] = useState(null);
  const [newDriver, setNewDriver] = useState({
    name: '',
    phone: '',
    birthdate: '',
    experience: '',
    status: 'active',
  });

  // useEffect giả lập fetch API
  useEffect(() => {
    setLoading(true);
    setTimeout(() => {
      try {
        setDrivers([
          {
            id: 1,
            name: 'Nguyễn Văn A',
            phone: '0909123456',
            birthdate: '1985-05-20',
            experience: '10 năm',
            status: 'active',
          },
          {
            id: 2,
            name: 'Trần Văn B',
            phone: '0912123456',
            birthdate: '1990-08-15',
            experience: '7 năm',
            status: 'on_leave',
          },
          {
            id: 3,
            name: 'Lê Văn C',
            phone: '0922123456',
            birthdate: '1982-12-01',
            experience: '15 năm',
            status: 'inactive',
          },
          {
            id: 4,
            name: 'Phạm Văn D',
            phone: '0933456789',
            birthdate: '1988-03-22',
            experience: '9 năm',
            status: 'active',
          },
          {
            id: 5,
            name: 'Đỗ Thị E',
            phone: '0944567890',
            birthdate: '1992-11-10',
            experience: '5 năm',
            status: 'on_leave',
          },
          {
            id: 6,
            name: 'Bùi Văn F',
            phone: '0955678901',
            birthdate: '1980-07-07',
            experience: '20 năm',
            status: 'inactive',
          }
        ]);
        setLoading(false);
      } catch (e) {
        setError('Không thể tải dữ liệu tài xế.');
        setLoading(false);
      }
    }, 500);
  }, []);

  const handleEdit = (driver) => {
    setEditingDriver(driver);
    setNewDriver({ ...driver });
    setShowModal(true);
  };

  const handleDelete = (id) => {
    if (window.confirm(`Xoá tài xế có ID: ${id}?`)) {
      setDrivers(drivers.filter((driver) => driver.id !== id));
    }
  };

  const handleSaveDriver = () => {
    if (editingDriver) {
      // Cập nhật
      /*
      updateDriverAPI(editingDriver.id, newDriver).then(() => fetchDrivers());
      */
      setDrivers(drivers.map((d) =>
        d.id === editingDriver.id ? { ...editingDriver, ...newDriver } : d
      ));
    } else {
      // Thêm mới
      /*
      createDriverAPI(newDriver).then(() => fetchDrivers());
      */
      const newId = drivers.length ? Math.max(...drivers.map((d) => d.id)) + 1 : 1;
      setDrivers([...drivers, { id: newId, ...newDriver }]);
    }

    setNewDriver({ name: '', phone: '', birthdate: '', experience: '', status: 'active' });
    setEditingDriver(null);
    setShowModal(false);
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Họ tên' },
    { key: 'phone', label: 'Số điện thoại' },
    { key: 'birthdate', label: 'Ngày sinh' },
    { key: 'experience', label: 'Kinh nghiệm' },
    {
      key: 'status',
      label: 'Trạng thái',
      render: (value) => {
        const statusMap = {
          active: 'Hoạt động',
          on_leave: 'Nghỉ phép',
          inactive: 'Ngừng làm việc',
        };
        const classMap = {
          active: 'status-success',
          on_leave: 'status-pending',
          inactive: 'status-canceled',
        };
        return <span className={classMap[value]}>{statusMap[value]}</span>;
      },
    },
    {
      key: 'actions',
      label: 'Hành động',
      render: (_, driver) => (
        <div className="action-buttons">
          <button className="edit-btn" onClick={() => handleEdit(driver)}>Sửa</button>
          <button className="delete-btn" onClick={() => handleDelete(driver.id)}>Xoá</button>
        </div>
      ),
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
              setEditingDriver(null);
              setNewDriver({
                name: '',
                phone: '',
                birthdate: '',
                experience: '',
                status: 'active',
              });
              setShowModal(true);
            }}
          >
            Thêm Tài Xế
          </button>
        </div>
        <ReusableTable
          columns={columns}
          data={drivers}
          loading={loading}
          error={error}
        />

        <ReusableModal
          title={editingDriver ? 'Sửa Tài Xế' : 'Thêm Tài Xế Mới'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingDriver(null);
            setNewDriver({ name: '', phone: '', birthdate: '', experience: '', status: 'active' });
          }}
          onSubmit={handleSaveDriver}
        >
          <div className="form-group">
            <label>Họ tên:</label>
            <input
              type="text"
              value={newDriver.name}
              onChange={(e) => setNewDriver({ ...newDriver, name: e.target.value })}
              placeholder="VD: Nguyễn Văn A"
            />
          </div>
          <div className="form-group">
            <label>Số điện thoại:</label>
            <input
              type="text"
              value={newDriver.phone}
              onChange={(e) => setNewDriver({ ...newDriver, phone: e.target.value })}
              placeholder="VD: 0909123456"
            />
          </div>
          <div className="form-group">
            <label>Ngày sinh:</label>
            <input
              type="date"
              value={newDriver.birthdate}
              onChange={(e) => setNewDriver({ ...newDriver, birthdate: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Kinh nghiệm:</label>
            <input
              type="text"
              value={newDriver.experience}
              onChange={(e) => setNewDriver({ ...newDriver, experience: e.target.value })}
              placeholder="VD: 5 năm"
            />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select
              value={newDriver.status}
              onChange={(e) => setNewDriver({ ...newDriver, status: e.target.value })}
            >
              <option value="active">Hoạt động</option>
              <option value="on_leave">Nghỉ phép</option>
              <option value="inactive">Ngừng làm việc</option>
            </select>
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Drivers;
