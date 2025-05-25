import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useApi } from '../../hooks/useApi';
import './BookingResults.css';

function useQuery() {
  return new URLSearchParams(useLocation().search);
}

const BookingResults = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const query = useQuery();
  const lineId = query.get('line_id'); // Lấy line_id từ url
  const api = useApi();
  const [trips, setTrips] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Get search parameters
  const departure = queryParams.get('departure');
  const destination = queryParams.get('destination');
  const date = queryParams.get('date');

  useEffect(() => {
    if (!departure || !destination || !date) {
      setLoading(false);
      return;
    }

    const fetchTrips = async () => {
      try {
        const response = await api.get('/trips/search', {
          params: { departure, destination, date }
        });
        setTrips(response.data.data.trips);
      } catch (error) {
        setTrips([]);
      } finally {
        setLoading(false);
      }
    };

    fetchTrips();
  }, [departure, destination, date]);

  // Đồng bộ filteredData và tripData với trips mỗi khi trips thay đổi
  useEffect(() => {
    setTripData(trips);
    setFilteredData(trips);
  }, [trips]);

  const goToDetail = (tripId, trip) => {
    navigate(`/ticket-detail/${tripId}?line_id=${trip.line ? trip.line.id : ''}&departure=${departure}&destination=${destination}&date=${date}`);
  };

  // State variables
  const [tripData, setTripData] = useState([]);
  const [filteredData, setFilteredData] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [priceFilter, setPriceFilter] = useState('all');
  const [seatFilter, setSeatFilter] = useState('all');
  const [timeFilter, setTimeFilter] = useState('all');
  const [sortOption, setSortOption] = useState('default');
  
  const itemsPerPage = 6;

  useEffect(() => {
    // Check if all required params exist
    if (!departure || !destination || !date) {
      setLoading(false);
      return;
    }

    // Fetch trip data
    const fetchData = async () => {
      try {
        const response = await api.get('/trips/search', {
          params: { departure, destination, date }
        });
        const data = response.data.data.trips;
        
        // Default sorting by departure time
        const sortedData = [...data].sort((a, b) => 
          new Date(a.departure_time) - new Date(b.departure_time)
        );
        
        setTripData(data);
        setFilteredData(sortedData);
      } catch (error) {
        console.error("Error fetching trips:", error);
        setTripData([]);
        setFilteredData([]);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [departure, destination, date]);

  // Apply filters and sorting
  const applyFiltersAndSort = () => {
    let filtered = [...tripData];

    // Filter by price
    if (priceFilter === 'below300') {
      filtered = filtered.filter(trip => trip.price < 300000);
    } else if (priceFilter === 'above300') {
      filtered = filtered.filter(trip => trip.price >= 300000);
    }

    // Filter by available seats
    if (seatFilter === 'available') {
      filtered = filtered.filter(trip => trip.available_seats > 0);
    } else if (seatFilter === 'full') {
      filtered = filtered.filter(trip => trip.available_seats === 0);
    }

    // Filter by time
    if (timeFilter === 'morning') {
      filtered = filtered.filter(trip => {
        const hour = new Date(trip.departure_time).getHours();
        return hour >= 0 && hour < 12;
      });
    } else if (timeFilter === 'afternoon') {
      filtered = filtered.filter(trip => {
        const hour = new Date(trip.departure_time).getHours();
        return hour >= 12 && hour < 18;
      });
    } else if (timeFilter === 'evening') {
      filtered = filtered.filter(trip => {
        const hour = new Date(trip.departure_time).getHours();
        return hour >= 18 && hour < 24;
      });
    }

    // Apply sorting
    if (sortOption === 'price-asc') {
      filtered.sort((a, b) => a.price - b.price);
    } else if (sortOption === 'price-desc') {
      filtered.sort((a, b) => b.price - a.price);
    } else if (sortOption === 'time-asc') {
      filtered.sort((a, b) => new Date(a.departure_time) - new Date(b.departure_time));
    } else if (sortOption === 'seats-desc') {
      filtered.sort((a, b) => b.available_seats - a.available_seats);
    } else {
      // Default sort by departure time
      filtered.sort((a, b) => new Date(a.departure_time) - new Date(b.departure_time));
    }

    setFilteredData(filtered);
    setCurrentPage(1);
  };

  // Format time function
  const formatDateTime = (isoString) => {
    const date = new Date(isoString);
  
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0"); // Tháng bắt đầu từ 0
    const year = date.getFullYear();
  
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
  
    return `${day}/${month}/${year} ${hours}:${minutes}`;
  };

  // Format currency function
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0
    }).format(amount);
  };

  // Pagination
  const goToPreviousPage = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1);
    }
  };

  const goToNextPage = () => {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (currentPage < totalPages) {
      setCurrentPage(currentPage + 1);
    }
  };

  // Get current page items
  const getCurrentPageItems = () => {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return filteredData.slice(startIndex, endIndex);
  };

  const handleBookingSubmit = (e) => {
    e.preventDefault();
    const departure = e.target.departure.value;
    const destination = e.target.destination.value;
    const date = e.target.date.value;
    const today = new Date().toISOString().split("T")[0];

    if (date < today) {
      alert("Không được chọn ngày trong quá khứ.");
      return;
    }
    if (departure !== "Đà Nẵng" && destination !== "Đà Nẵng") {
      alert("Chỉ hỗ trợ chuyến xe từ Đà Nẵng đi hoặc về Đà Nẵng!");
      return;
    }
    if (departure && destination && date) {
      navigate(`/booking-results?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(date)}`);
    } else {
      alert("Vui lòng chọn đầy đủ thông tin.");
    }
  };

  return (
    <div className="booking-resultslogin">
      {/* Header */}
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <Link to="/" className="back-link">Quay lại Trang Chủ</Link>
        </div>
      </header>

      {/* Main Content */}
      <section className="container">
        <h2>KẾT QUẢ TÌM KIẾM</h2>

        {trips.length === 0 ? (
          <div className="search-info">
            <p>Không tìm thấy chuyến xe phù hợp. Vui lòng thử lại với tuyến khác hoặc ngày khác.</p>
            <Link to="/" className="btn-modern">Quay lại trang chủ</Link>
          </div>
        ) : (
          <>
            <div className="results-grid">
              {trips.map((trip) => {
                const formattedTime = formatDateTime(trip.departure_time);
                return (
                  <div className="bus-card" key={trip.id}>
                    <h3>{trip.vehicle ? trip.vehicle.type : 'Xe Limousine'} - {trip.line ? trip.line.departure + ' → ' + trip.line.destination : departure + ' → ' + destination}</h3>
                    <p>⏰ Giờ khởi hành: <strong>{formattedTime}</strong></p>
                    <p>💰 Giá vé: <strong>{formatCurrency(trip.price)}</strong></p>
                    <p>🪑 <span className={`status-badge ${trip.available_seats === 0 ? 'status-full' : trip.available_seats <= 5 ? 'status-limited' : 'status-available'}`}>{trip.available_seats === 0 ? 'Hết chỗ' : `Còn ${trip.available_seats} ghế`}</span></p>
                    <p className="amenities">✅ WiFi miễn phí, nước uống, điều hòa</p>
                    <button className='btn-modern' onClick={() => goToDetail(trip.id, trip)} disabled={trip.available_seats === 0}>Đặt vé ngay</button>
                  </div>
                );
              })}
            </div>

            {/* Pagination */}
            <div className="pagination">
              <button
                className={`btn-modern ${currentPage === 1 ? 'disabled' : ''}`}
                onClick={goToPreviousPage}
                disabled={currentPage === 1}
              >
                Trang trước
              </button>
              <span className="page-info">
                Trang {currentPage} / {Math.ceil(filteredData.length / itemsPerPage)}
              </span>
              <button
                className={`btn-modern ${currentPage === Math.ceil(filteredData.length / itemsPerPage) ? 'disabled' : ''}`}
                onClick={goToNextPage}
                disabled={currentPage === Math.ceil(filteredData.length / itemsPerPage)}
              >
                Trang sau
              </button>
            </div>
          </>
        )}
      </section>
    </div>
  );
};

export default BookingResults;