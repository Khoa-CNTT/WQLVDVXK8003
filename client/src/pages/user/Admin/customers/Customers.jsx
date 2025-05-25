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
import customerSchema from './customerSchema';

const Customers = () => {
  const api = useApi();
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [showModal, setShowModal] = useState(false);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(customerSchema),
    mode: 'onChange',
    defaultValues: {
      name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      role_id: 2,
      address: '',
      status: 'active',
      isEditing: false,
    },
  });

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setLoading(true);
        const data = await fetchSortedData(api, '/admin/users');
        setUsers(data);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetchUsers();
  }, []);

  const onSubmit = async (data) => {
    console.log('Submit data:', data);
    try {
      const payload = {
        name: data.name,
        email: data.email,
        phone: data.phone,
        role_id: data.role_id,
        status: data.status,
        address: data.address,
      };

      if (!editingUser || data.password) {
        payload.password = data.password;
        payload.password_confirmation = data.password_confirmation;
      }

      if (editingUser) {
        console.log('update')
        await api.put(`/admin/users/${editingUser.id}`, payload);
        toast.success('Cập nhật người dùng thành công!');
      } else {
        await api.post('/admin/users', payload);
        toast.success('Tạo người dùng thành công!');
      }

      setShowModal(false);
      setEditingUser(null);
      reset({ ...payload, password: '', password_confirmation: '', isEditing: false });

      const updatedUsers = await fetchSortedData(api, '/admin/users');
      setUsers(updatedUsers);
    } catch (error) {
      console.error(error);
      toast.error('Lỗi khi lưu người dùng');
    }
  };

  const handleEditUser = (user) => {
    setEditingUser(user);
    reset({
      ...user,
      password: '',
      password_confirmation: '',
      isEditing: true,
    });
    setShowModal(true);
  };

  const handleDeleteUser = (id) => {
    confirmAction({
      title: 'Xác nhận xóa người dùng',
      text: `Bạn có chắc muốn xóa user ID ${id}?`,
      onConfirm: async () => {
        try {
          await api.delete(`/admin/users/${id}`);
          toast.success('Xóa user thành công');
          const updatedUsers = await fetchSortedData(api, '/admin/users');
          setUsers(updatedUsers);
        } catch (error) {
          toast.error('Lỗi khi xóa user');
        }
      },
    });
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

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Khách Hàng</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              reset({
                name: '',
                email: '',
                phone: '',
                password: '',
                password_confirmation: '',
                role_id: 2,
                address: '',
                status: 'active',
                isEditing: false,
              });
              setEditingUser(null);
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
          onSubmit={handleSubmit(onSubmit)}
        >
          <div className="">
            <label>Họ tên:</label>
            <input {...register('name')} placeholder="VD: Nguyễn Văn A" />
            {errors.name && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.name.message}</p>}
          </div>
          <div className="">
            <label>Email:</label>
            <input {...register('email')} placeholder="VD: user@email.com" />
            {errors.email && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.email.message}</p>}
          </div>
          <div className="">
            <label>Số điện thoại:</label>
            <input {...register('phone')} placeholder="VD: 0905123456" />
            {errors.phone && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.phone.message}</p>}
          </div>
          <div className="form-group">
            <label>Vai trò:</label>
            <select {...register('role_id')}>
              <option value={1}>Admin</option>
              <option value={2}>Customer</option>
            </select>
            {errors.role_id && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.role_id.message}</p>}
          </div>
          <div className="form-group">
            <label>Trạng thái:</label>
            <select {...register('status')}>
              <option value="active">Hoạt động</option>
              <option value="banned">Bị khóa</option>
            </select>
            {errors.status && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.status.message}</p>}
          </div>
          {!editingUser && (
            <>
              <div className="">
                <label>Mật khẩu:</label>
                <input type="password" {...register('password')} />
                {errors.password && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.password.message}</p>}
              </div>
              <div className="">
                <label>Xác nhận mật khẩu:</label>
                <input type="password" {...register('password_confirmation')} />
                {errors.password_confirmation && (
                  <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.password_confirmation.message}</p>
                )}
              </div>
            </>
          )}
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Customers;
