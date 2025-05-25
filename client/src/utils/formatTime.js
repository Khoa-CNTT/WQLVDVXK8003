 export const formatTime = (dateTime) => {
    const date = new Date(dateTime);
    return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
  };
