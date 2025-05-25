export const formatTime = (dateTime) => {
  if (!dateTime) return '';
  // Luôn hiển thị theo múi giờ Việt Nam
  return new Intl.DateTimeFormat('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
    timeZone: 'Asia/Ho_Chi_Minh',
  }).format(new Date(dateTime));
};
