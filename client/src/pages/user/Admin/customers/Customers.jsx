import React, { useEffect, useState } from 'react';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import ReusableTable from '../../../../components/ReusableTable/ReusableTable';
import { useApi } from '../../../../hooks/useApi';
import { fetchSortedData } from '../../../../utils/fetchSortedData';

const Customers = () => {
  const api = useApi();

  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [editingUser, setEditingUser] = useState(null);

  const [formUser, setFormUser] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role_id: 2,   // default customer
    address: '',
    status: 'active',
  });

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setLoading(true);
        const UsersData = await fetchSortedData(api, '/admin/users');
        setUsers(UsersData);
      } catch (err) {
        setError(err);
      } finally {
        setLoading(false);
      }
    };
    fetchUsers();
  }, []);

  const handleSaveUser = async () => {
    try {
      const payload = {
        name: formUser.name,
        email: formUser.email,
        phone: formUser.phone,
        role_id: formUser.role_id,  // gửi role_id số
        address: formUser.address,
        status: formUser.status,
      };

      if (!editingUser || formUser.password) {
        payload.password = formUser.password;
        payload.password_confirmation = formUser.password_confirmation;
      }

      console.log('Payload gửi lên:', payload);

      if (editingUser) {
        await api.put(`/admin/users/${editingUser.id}`, payload);
        alert('Cập nhật người dùng thành công');
      } else {
        await api.post('/admin/users', payload);
        alert('Tạo người dùng thành công');
      }

      setShowModal(false);
      setEditingUser(null);
      resetForm();
      // Reload data
      const newData = await fetchSortedData(api, '/admin/users');
      setUsers(newData);

    } catch (error) {
      console.error('Lỗi khi lưu user:', error);
      alert('Lỗi khi lưu người dùng');
    }
  };

  const handleDeleteUser = async (id) => {
    if (window.confirm(`Bạn có chắc muốn xóa user ID ${id}?`)) {
      try {
        await api.delete(`/admin/users/${id}`);
        alert('Xóa user thành công');
        // Reload data
        const newData = await fetchSortedData(api, '/admin/users');
        setUsers(newData);
      } catch (error) {
        alert('Lỗi khi xóa user');
      }
    }
  };

  const handleEditUser = (user) => {
    setEditingUser(user);
    setFormUser({
      name: user.name || '',
      email: user.email || '',
      phone: user.phone || '',
      role_id: user.role_id || 2,
      status: user.status || 'active',
      address: user.address || '',
      password: '',
      password_confirmation: '',
    });
    setShowModal(true);
  };

  const statusMap = {
    active: { className: 'text-green-600 font-bold', label: 'Hoạt động' },
    banned: { className: 'text-red-600 font-bold', label: 'Bị khóa' },
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Tên', key: 'name' },
    { label: 'Email', key: 'email' },
    { label: 'Số điện thoại', key: 'phone' },
    {
      label: 'Vai trò',
      key: 'role_id',
      render: (roleId) => {
        const roleMap = {
          1: { label: 'Admin', className: 'text-orange-500 font-bold' },
          2: { label: 'Customer', className: 'text-green-600 font-bold' },
        };
        const role = roleMap[roleId] || { label: 'N/A', className: '' };
        return <span className={role.className}>{role.label}</span>;
      },
    },
    {
      label: 'Trạng thái',
      key: 'status',
      render: (value) => {
        const s = statusMap[value] || { className: '', label: value };
        return <span className={s.className}>{s.label}</span>;
      },
    },
    {
      label: 'Hành động',
      key: 'actions',
    },
  ];

  const resetForm = () => {
    setFormUser({
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      role_id: 2,
      address: '',
      status: 'active',
    });
  };

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Khách Hàng</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              resetForm();
              setShowModal(true);
            }}
          >
            Thêm Khách Hàng
          </button>
        </div>
        <ReusableTable
          columns={columns}
          data={users.map((user) => ({
            ...user,
            'role.name': user.role?.name,
            actions: (
              <div className="action-buttons">
                <button className="edit-btn" onClick={() => handleEditUser(user)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDeleteUser(user.id)}>Xóa</button>
              </div>
            ),
          }))}
          loading={loading}
        />

        <ReusableModal
          title={editingUser ? 'Sửa Người Dùng' : 'Thêm Người Dùng'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingUser(null);
          }}
          onSubmit={handleSaveUser}
        >
          <div className="form-group">
            <label>Họ tên:</label>
            <input
              type="text"
              value={formUser.name}
              onChange={(e) => setFormUser({ ...formUser, name: e.target.value })}
              placeholder="VD: Nguyễn Văn A"
            />
          </div>
          <div className="form-group">
            <label>Email:</label>
            <input
              type="email"
              value={formUser.email}
              onChange={(e) => setFormUser({ ...formUser, email: e.target.value })}
              placeholder="VD: user@email.com"
            />
          </div>
          <div className="form-group">
            <label>Số điện thoại:</label>
            <input
              type="text"
              value={formUser.phone}
              onChange={(e) => setFormUser({ ...formUser, phone: e.target.value })}
              placeholder="VD: 0905123456"
            />
          </div>
          <div className="form-group">
            <label>Vai trò:</label>
            <select
              value={formUser.role_id}
              onChange={(e) => setFormUser({ ...formUser, role_id: parseInt(e.target.value) })}
            >
              <option value={1}>Admin</option>
              <option value={2}>Customer</option>
            </select>
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select
              value={formUser.status}
              onChange={(e) => setFormUser({ ...formUser, status: e.target.value })}
            >
              <option value="active">Hoạt động</option>
              <option value="banned">Bị khóa</option>
            </select>
          </div>
          {!editingUser && (
            <>
              <div className="form-group">
                <label>Mật khẩu:</label>
                <input
                  type="password"
                  value={formUser.password}
                  onChange={(e) => setFormUser({ ...formUser, password: e.target.value })}
                />
              </div>
              <div className="form-group">
                <label>Xác nhận mật khẩu:</label>
                <input
                  type="password"
                  value={formUser.password_confirmation}
                  onChange={(e) => setFormUser({ ...formUser, password_confirmation: e.target.value })}
                />
              </div>
            </>
          )}
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Customers;
