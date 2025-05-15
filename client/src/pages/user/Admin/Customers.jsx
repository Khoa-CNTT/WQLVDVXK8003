import React, { useEffect, useState } from 'react';
import Table from '../../../components/ReusableTable/Table';
import HomeAdminLayout from '../../../layouts/AdminLayout';

const Customers = () => {
  const [customers, setCustomers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    setLoading(true);
    // Giả lập gọi API
    setTimeout(() => {
      try {
        setCustomers([
          {
            id: 1,
            name: 'Nguyễn Văn D',
            email: 'nguyenvand@example.com',
            phone: '0909999999',
            birthdate: '1995-01-01',
            status: 'active',
          },
          {
            id: 2,
            name: 'Phạm Thị E',
            email: 'phamthie@example.com',
            phone: '0911222333',
            birthdate: '1990-10-12',
            status: 'blocked',
          },
        ]);
        setLoading(false);
      } catch (err) {
        setError('Không thể tải danh sách khách hàng.');
        setLoading(false);
      }
    }, 500);
  }, []);

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Họ tên' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Số điện thoại' },
    { key: 'birthdate', label: 'Ngày sinh' },
    {
      key: 'status',
      label: 'Trạng thái',
      render: (value) => {
        const statusMap = {
          active: 'Hoạt động',
          blocked: 'Tạm khoá',
        };
        const classMap = {
          active: 'status-success',
          blocked: 'status-canceled',
        };
        return <span className={classMap[value]}>{statusMap[value]}</span>;
      },
    },
  ];

  const handleEdit = (customer) => {
    alert(`Sửa khách hàng: ${customer.name}`);
  };

  const handleDelete = (customer) => {
    if (window.confirm(`Xoá khách hàng ${customer.name}?`)) {
      setCustomers((prev) => prev.filter((c) => c.id !== customer.id));
    }
  };

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Khách Hàng</h1>
        <div className="action-bar">
          <button className="add-btn">Thêm Khách Hàng</button>
        </div>
        <Table
          columns={columns}
          data={customers}
          loading={loading}
          error={error}
          onEdit={handleEdit}
          onDelete={handleDelete}
        />
      </div>
    </HomeAdminLayout>
  );
};

export default Customers;
