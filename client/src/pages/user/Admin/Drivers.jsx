import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import Table from '../../../components/ReusableTable/Table';

const Drivers = () => {
  const [drivers, setDrivers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Fetch data giả lập
  useEffect(() => {
    setLoading(true);
    // Mô phỏng gọi API
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
        ]);
        setLoading(false);
      } catch (e) {
        setError('Không thể tải dữ liệu tài xế.');
        setLoading(false);
      }
    }, 500);
  }, []);

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
  ];

  const handleEdit = (driver) => {
    alert(`Chỉnh sửa tài xế: ${driver.name}`);
  };

  const handleDelete = (driver) => {
    if (window.confirm(`Xác nhận xóa tài xế ${driver.name}?`)) {
      setDrivers((prev) => prev.filter((d) => d.id !== driver.id));
    }
  };

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Tài Xế</h1>
        <div className="action-bar">
          <button className="add-btn">Thêm Tài Xế</button>
        </div>
        <Table
          columns={columns}
          data={drivers}
          loading={loading}
          error={error}
          onEdit={handleEdit}
          onDelete={handleDelete}
        />
      </div>
    </HomeAdminLayout>
  );
};

export default Drivers;
