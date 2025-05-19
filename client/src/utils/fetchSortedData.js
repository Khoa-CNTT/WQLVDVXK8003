export async function fetchSortedData(api, url) {
  try {
    const response = await api.get(url);
    const rawData = response?.data?.data.data || [];

    const mappedData = rawData.map(item => ({
      id: item.id,
      ...item,
    }));

    mappedData.sort((a, b) => b.id - a.id);

    return mappedData;
  } catch (error) {
    console.error('Error fetching data:', error);
    throw error;
  }
}
