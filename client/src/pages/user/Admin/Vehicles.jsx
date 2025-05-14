import React, { useState } from 'react';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import Table from '../../../components/ReusableTable/Table';

const Vehicles = () => {
    // // Mock vehicle data
    // const [vehicles, setVehicles] = useState([
    //     { id: 1, plate: "51B-12345", type: "Xe khách", seats: 45, year: 2018, status: "Hoạt động" },
    //     { id: 2, plate: "51B-67890", type: "Xe giường nằm", seats: 40, year: 2020, status: "Bảo trì" },
    //     { id: 3, plate: "51B-54321", type: "Xe khách", seats: 50, year: 2019, status: "Hoạt động" }
    // ]);

    // const [currentTime, setCurrentTime] = useState('');
    // const [currentDate, setCurrentDate] = useState('');
    // const [showModal, setShowModal] = useState(false);
    // const [editingVehicleId, setEditingVehicleId] = useState(null);

    // // Form state
    // const [vehiclePlate, setVehiclePlate] = useState('');
    // const [vehicleType, setVehicleType] = useState('');
    // const [vehicleSeats, setVehicleSeats] = useState('');
    // const [vehicleYear, setVehicleYear] = useState('');
    // const [vehicleStatus, setVehicleStatus] = useState('');

    // // Action log data (loaded from localStorage or initialized)
    // const [actionLog, setActionLog] = useState(() => {
    //     const savedLog = localStorage.getItem('actionLog');
    //     return savedLog ? JSON.parse(savedLog) : [];
    // });

    // // Update time regularly
    // useEffect(() => {
    //     const updateTime = () => {
    //         const now = new Date();
    //         const date = now.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    //         const time = now.toLocaleTimeString('vi-VN', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
    //         setCurrentDate(date);
    //         setCurrentTime(time);
    //     };

    //     updateTime();
    //     const interval = setInterval(updateTime, 1000);
    //     return () => clearInterval(interval);
    // }, []);

    // const openAddVehicleModal = () => {
    //     setEditingVehicleId(null);
    //     resetForm();
    //     setShowModal(true);
    // };

    // const resetForm = () => {
    //     setVehiclePlate('');
    //     setVehicleType('');
    //     setVehicleSeats('');
    //     setVehicleYear('');
    //     setVehicleStatus('');
    // };

    // const editVehicle = (id) => {
    //     const vehicle = vehicles.find(v => v.id === id);
    //     if (vehicle) {
    //         setEditingVehicleId(id);
    //         setVehiclePlate(vehicle.plate);
    //         setVehicleType(vehicle.type);
    //         setVehicleSeats(vehicle.seats.toString());
    //         setVehicleYear(vehicle.year.toString());
    //         setVehicleStatus(vehicle.status);
    //         setShowModal(true);
    //     }
    // };

    // const saveVehicle = () => {
    //     const plate = vehiclePlate.trim();
    //     const type = vehicleType.trim();
    //     const seats = parseInt(vehicleSeats);
    //     const year = parseInt(vehicleYear);
    //     const status = vehicleStatus;

    //     if (!plate || !type || !seats || !year || !status) {
    //         alert('Vui lòng điền đầy đủ thông tin!');
    //         return;
    //     }

    //     if (!/^\d{2}[A-Z]-[0-9]{5}$/.test(plate)) {
    //         alert('Biển số xe không hợp lệ! Vui lòng nhập theo định dạng: 51B-12345');
    //         return;
    //     }

    //     if (seats <= 0) {
    //         alert('Số ghế phải lớn hơn 0!');
    //         return;
    //     }

    //     if (year < 1900 || year > new Date().getFullYear()) {
    //         alert(`Năm sản xuất phải nằm trong khoảng 1900 đến ${new Date().getFullYear()}!`);
    //         return;
    //     }

    //     if (editingVehicleId) {
    //         setVehicles(vehicles.map(v => 
    //             v.id === editingVehicleId 
    //                 ? { ...v, plate, type, seats, year, status } 
    //                 : v
    //         ));
    //         logAction('Sửa phương tiện', `ID: ${editingVehicleId}, Biển số: ${plate}`);
    //     } else {
    //         const newId = vehicles.length ? Math.max(...vehicles.map(v => v.id)) + 1 : 1;
    //         setVehicles([...vehicles, { id: newId, plate, type, seats, year, status }]);
    //         logAction('Thêm phương tiện', `ID: ${newId}, Biển số: ${plate}`);
    //     }

    //     setShowModal(false);
    //     resetForm();
    // };

    // const deleteVehicle = (id) => {
    //     const vehicle = vehicles.find(v => v.id === id);
    //     if (vehicle && window.confirm('Bạn có chắc muốn xóa phương tiện này?')) {
    //         setVehicles(vehicles.filter(v => v.id !== id));
    //         logAction('Xóa phương tiện', `ID: ${id}, Biển số: ${vehicle.plate}`);
    //     }
    // };

    // const logAction = (action, details) => {
    //     const timestamp = new Date().toLocaleString('vi-VN');
    //     const newLog = [...actionLog, { timestamp, action, details }];
    //     setActionLog(newLog);
    //     localStorage.setItem('actionLog', JSON.stringify(newLog));
    // };

    // const logout = () => {
    //     if (window.confirm('Bạn có chắc muốn đăng xuất?')) {
    //         window.location.href = 'loginadmin.html';
    //     }
    // };

const [vehicles, setVehicles] = useState([
    {
      id: 1,
      licensePlate: '51A-12345',
      type: 'Ghế ngồi',
      seats: 45,
      year: 2020,
      status: 'Đang hoạt động',
    },
    {
      id: 2,
      licensePlate: '61B-67890',
      type: 'Giường nằm',
      seats: 40,
      year: 2018,
      status: 'Bảo trì',
    },
  ]);

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Biển số xe', key: 'licensePlate' },
    { label: 'Loại xe', key: 'type' },
    { label: 'Số ghế', key: 'seats' },
    { label: 'Năm sản xuất', key: 'year' },
    { label: 'Trạng thái', key: 'status' },
  ];

  const handleEdit = (id) => {
    alert(`Chỉnh sửa xe có ID: ${id}`);
  };

  const handleDelete = (id) => {
    if (window.confirm(`Xóa xe có ID: ${id}?`)) {
      setVehicles(vehicles.filter(vehicle => vehicle.id !== id));
    }
  };


    return (
        <HomeAdminLayout>
         <div className="ticket-container">
        <h1 className="page-title">Danh Sách Phương Tiện</h1>
        <div className="action-bar">
          <button className="add-btn">Thêm Phương Tiện</button>
        </div>

        <Table
          columns={columns}
          data={vehicles}
          onEdit={handleEdit}
          onDelete={handleDelete}
        />
      </div>
        </HomeAdminLayout>

    );
};

export default Vehicles;