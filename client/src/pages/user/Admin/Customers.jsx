import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import ReusableModal from '../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../components/ReusableTable/ReusableTable';

const Customers = () => {
  const [customers, setCustomers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const [showModal, setShowModal] = useState(false);
  const [editingCustomer, setEditingCustomer] = useState(null);
  const [newCustomer, setNewCustomer] = useState({
    name: '',
    email: '',
    phone: '',
    birthdate: '',
    status: 'active',
  });

  useEffect(() => {
    setLoading(true);
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
          {
            id: 3,
            name: 'Lê Thị F',
            email: 'lethif@example.com',
            phone: '0922333444',
            birthdate: '1988-05-05',
            status: 'active',
          },
          {
            id: 4,
            name: 'Trần Văn G',
            email: 'tranvang@example.com',
            phone: '0933444555',
            birthdate: '1992-09-20',
            status: 'active',
          },
          {
            id: 5,
            name: 'Hoàng Thị H',
            email: 'hoangthih@example.com',
            phone: '0944555666',
            birthdate: '1985-12-15',
            status: 'blocked',
          },
          {
            id: 6,
            name: 'Phan Văn I',
            email: 'phanvani@example.com',
            phone: '0955666777',
            birthdate: '1993-07-07',
            status: 'active',
          },
        ]);
        setLoading(false);
      } catch (err) {
        setError('Không thể tải danh sách khách hàng.');
        setLoading(false);
      }
    }, 500);
  }, []);

  const handleEdit = (customer) => {
    setEditingCustomer(customer);
    setNewCustomer({ ...customer });
    setShowModal(true);
  };

  const handleDelete = (id) => {
    if (window.confirm(`Xoá khách hàng có ID: ${id}?`)) {
      setCustomers(customers.filter((c) => c.id !== id));
    }
  };

  const handleSaveCustomer = () => {
    if (editingCustomer) {
      setCustomers(customers.map((c) =>
        c.id === editingCustomer.id ? { ...editingCustomer, ...newCustomer } : c
      ));
    } else {
      const newId = customers.length ? Math.max(...customers.map(c => c.id)) + 1 : 1;
      setCustomers([...customers, { id: newId, ...newCustomer }]);
    }

    setShowModal(false);
    setEditingCustomer(null);
    setNewCustomer({ name: '', email: '', phone: '', birthdate: '', status: 'active' });
  };

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
    {
      key: 'actions',
      label: 'Hành động',
      render: (_, customer) => (
        <div className="action-buttons">
          <button className="edit-btn" onClick={() => handleEdit(customer)}>Sửa</button>
          <button className="delete-btn" onClick={() => handleDelete(customer.id)}>Xoá</button>
        </div>
      ),
    },
  ];

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Khách Hàng</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              setEditingCustomer(null);
              setNewCustomer({ name: '', email: '', phone: '', birthdate: '', status: 'active' });
              setShowModal(true);
            }}
          >
            Thêm Khách Hàng
          </button>
        </div>
        <ReusableTable
          columns={columns}
          data={customers}
          loading={loading}
          error={error}
        />

        <ReusableModal
          title={editingCustomer ? 'Sửa Khách Hàng' : 'Thêm Khách Hàng'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingCustomer(null);
            setNewCustomer({ name: '', email: '', phone: '', birthdate: '', status: 'active' });
          }}
          onSubmit={handleSaveCustomer}
        >
          <div className="form-group">
            <label>Họ tên:</label>
            <input
              type="text"
              value={newCustomer.name}
              onChange={(e) => setNewCustomer({ ...newCustomer, name: e.target.value })}
              placeholder="VD: Nguyễn Văn D"
            />
          </div>
          <div className="form-group">
            <label>Email:</label>
            <input
              type="email"
              value={newCustomer.email}
              onChange={(e) => setNewCustomer({ ...newCustomer, email: e.target.value })}
              placeholder="VD: example@email.com"
            />
          </div>
          <div className="form-group">
            <label>Số điện thoại:</label>
            <input
              type="text"
              value={newCustomer.phone}
              onChange={(e) => setNewCustomer({ ...newCustomer, phone: e.target.value })}
              placeholder="VD: 0909999999"
            />
          </div>
          <div className="form-group">
            <label>Ngày sinh:</label>
            <input
              type="date"
              value={newCustomer.birthdate}
              onChange={(e) => setNewCustomer({ ...newCustomer, birthdate: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select
              value={newCustomer.status}
              onChange={(e) => setNewCustomer({ ...newCustomer, status: e.target.value })}
            >
              <option value="active">Hoạt động</option>
              <option value="blocked">Tạm khoá</option>
            </select>
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Customers;
