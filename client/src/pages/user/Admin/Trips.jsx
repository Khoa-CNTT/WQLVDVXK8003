import React from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import Table from '../../../components/ReusableTable/Table';

export const Trips = () => {
  const trips = [
    {
      id: 1,
      route: 'Hà Nội - Sài Gòn',
      vehicle: 'Xe giường nằm 45 chỗ',
      driver: 'Nguyễn Văn A',
      departure_date: '2025-05-20',
      departure_time: '08:00',
      status: 'completed',
    },
    {
      id: 2,
      route: 'Đà Nẵng - Nha Trang',
      vehicle: 'Xe limousine 16 chỗ',
      driver: 'Trần Văn B',
      departure_date: '2025-05-22',
      departure_time: '13:30',
      status: 'pending',
    },
    {
      id: 3,
      route: 'Huế - Hà Nội',
      vehicle: 'Xe thường 29 chỗ',
      driver: 'Lê Thị C',
      departure_date: '2025-05-25',
      departure_time: '06:00',
      status: 'canceled',
    },
  ];

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'route', label: 'Tuyến đường' },
    { key: 'vehicle', label: 'Phương tiện' },
    { key: 'driver', label: 'Tài xế' },
    { key: 'departure_date', label: 'Ngày khởi hành' },
    { key: 'departure_time', label: 'Thời gian khởi hành' },
    {
      key: 'status',
      label: 'Trạng thái',
      render: (status) => {
        const map = {
          completed: 'status-success',
          pending: 'status-pending',
          canceled: 'status-canceled',
        };
        const label = {
          completed: 'Hoàn thành',
          pending: 'Chờ chạy',
          canceled: 'Đã hủy',
        };
        return <span className={map[status]}>{label[status]}</span>;
      },
    },
  ];

  const handleEdit = (trip) => {
    console.log('Edit trip', trip);
  };

  const handleDelete = (trip) => {
    console.log('Delete trip', trip);
  };

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Chuyến Xe</h1>

        <div className="action-bar">
          <button className="add-btn">Thêm Chuyến Xe</button>
        </div>

        <Table
          columns={columns}
          data={trips}
          loading={false}
          error={null}
          onEdit={handleEdit}
          onDelete={handleDelete}
        />
      </div>
    </HomeAdminLayout>
  );
};

export default Trips;
