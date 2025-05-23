
const LoadingTable = ({ rows = 5, cols = 5 }) => {
  return (
    <div className="w-full animate-pulse">
      <div className="overflow-x-auto rounded-xl shadow bg-white">
        <table className="min-w-full table-fixed border-separate border-spacing-y-2">
          <thead>
            <tr>
              {Array.from({ length: cols }).map((_, i) => (
                <th key={i} className="px-4 py-2 text-left">
                  <div className="h-4 bg-gray-300 rounded w-3/4" />
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {Array.from({ length: rows }).map((_, rowIdx) => (
              <tr key={rowIdx} className="bg-gray-100 rounded-lg shadow-sm">
                {Array.from({ length: cols }).map((_, colIdx) => (
                  <td key={colIdx} className="px-4 py-3">
                    <div className="h-4 bg-gray-200 rounded w-full" />
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default LoadingTable;
