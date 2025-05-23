function extractData(response) {
  let data = response?.data;
  while (data && typeof data === 'object' && 'data' in data) {
    data = data.data;
  }
  return Array.isArray(data) ? data : [];
}
/**
 * Lấy và sắp xếp dữ liệu theo ID giảm dần từ một endpoint
 * @param {object} api - Đối tượng API đã cấu hình
 * @param {string} url - Đường dẫn endpoint
 * @param {function} [mapper] - Hàm tùy chọn để biến đổi từng item
 */
export async function fetchSortedData(api, url, mapper = (item) => ({ id: item.id, ...item })) {
  try {
    const response = await api.get(url);
    const rawData = extractData(response);

    const mappedData = rawData.map(mapper);
    mappedData.sort((a, b) => b.id - a.id);

    return mappedData;
  } catch (error) {
    console.error('Error fetching data:', error);
    throw error;
  }
}
