import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import './BookingResultsLogin.css';
import { useApi } from '../../hooks/useApi';

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

  const goToDetail = (tripId, trip) => {
    navigate(`/ticket-detaillogin/${tripId}?line_id=${trip.line ? trip.line.id : ''}&departure=${departure}&destination=${destination}&date=${date}`);
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
        // Replace with your actual API call
        // const response = await api.searchTrips(departure, destination, date);
        // const data = response.trips;

        // For now using sample data
        const data = generateSampleData(departure, destination, date);

        // Default sorting by departure time
        const sortedData = [...data].sort((a, b) =>
          new Date(a.departure_time) - new Date(b.departure_time)
        );

        setTripData(data);
        setFilteredData(sortedData);
      } catch (error) {
        console.error("Error fetching trips:", error);

        // Use sample data when API fails
        const sampleData = generateSampleData(departure, destination, date);
        const sortedData = [...sampleData].sort((a, b) =>
          new Date(a.departure_time) - new Date(b.departure_time)
        );

        setTripData(sampleData);
        setFilteredData(sortedData);
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

  // Generate sample data
  const generateSampleData = (departure, destination, date) => {
    const dateObj = new Date(date);
    const sampleData = [];

    // Generate 10 sample trips
    for (let i = 0; i < 10; i++) {
      const hour = 6 + (i * 2) % 24; // Trips every 2 hours starting from 6 AM
      const tripTime = new Date(dateObj);
      tripTime.setHours(hour, i % 2 === 0 ? 0 : 30, 0);

      sampleData.push({
        id: `trip-${i + 1}`,
        route: {
          departure: departure,
          destination: destination
        },
        vehicle: {
          type: i % 3 === 0 ? 'Limousine VIP' : (i % 3 === 1 ? 'Giường nằm cao cấp' : 'Xe thường')
        },
        departure_time: tripTime.toISOString(),
        price: 250000 + (i * 10000),
        available_seats: i % 5 === 0 ? 0 : Math.floor(Math.random() * 20) + 1,
        total_seats: 40
      });
    }

    return sampleData;
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

  return (
    <div className="booking-resultslogin">
      {/* Header */}
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <Link to="/home" className="back-link">Quay lại Trang Chủ</Link>
        </div>
      </header>

      {/* Main Content */}
      <section className="container">
        <h2>KẾT QUẢ TÌM KIẾM</h2>

        {trips.length === 0 ? (
          <div className="search-info">
            <p>Không tìm thấy chuyến xe phù hợp. Vui lòng thử lại với tuyến khác hoặc ngày khác.</p>
            <Link to="/home" className="btn-modern">Quay lại trang chủ</Link>
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
                    <p>🪑 <span className={`status-badge ${trip.available_seats === 0 ? 'status-full' : trip.available_seats <= 5 ? 'status-limited' : 'status-available'}`}></span></p>
                    <p className="amenities">✅ WiFi miễn phí, nước uống, điều hòa</p>
                    <button className='btn-modern' onClick={() => goToDetail(trip.id, trip)}>Đặt vé ngay</button>
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