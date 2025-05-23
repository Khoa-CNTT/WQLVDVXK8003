import React, { useState } from 'react';
import './index.css';
import LoadingTable from '../LoadingTable';

const ReusableTable = ({ columns, data, loading, error, onEdit, onDelete }) => {
  const rowsPerPage = 6;
  const [currentPage, setCurrentPage] = useState(1);

  const totalPages = Math.ceil(data.length / rowsPerPage);
  const startIdx = (currentPage - 1) * rowsPerPage;
  const paginatedData = data.slice(startIdx, startIdx + rowsPerPage);

  const handlePageChange = (page) => {
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
    }
  };

  return (
    <div className="table-container">
      {loading ? (
        <LoadingTable/>
      ) : error ? (
        <div className="table-error">{error}</div>
      ) : (
        <>
          <table className="shared-table">
            <thead>
              <tr>
                {columns.map((col) => (
                  <th key={col.key}>{col.label}</th>
                ))}
                {(onEdit || onDelete) && <th>Hành động</th>}
              </tr>
            </thead>
            <tbody>
              {paginatedData.length === 0 ? (
                <tr>
                  <td colSpan={columns.length + 1}>Không có dữ liệu</td>
                </tr>
              ) : (
                paginatedData.map((item, idx) => (
                  <tr key={item.id || idx}>
                    {columns.map((col) => (
                      <td key={col.key}>
                        {col.render ? col.render(item[col.key], item) : item[col.key]}
                      </td>
                    ))}
                    {(onEdit || onDelete) && (
                      <td>
                        <div className="table-actions">
                          {onEdit && (
                            <button
                              className="table-edit-btn"
                              onClick={() => onEdit(item)}
                            >
                              Sửa
                            </button>
                          )}
                          {onDelete && (
                            <button
                              className="table-delete-btn"
                              onClick={() => onDelete(item)}
                            >
                              Xoá
                            </button>
                          )}
                        </div>
                      </td>
                    )}
                  </tr>
                ))
              )}
            </tbody>
          </table>

          {totalPages > 1 && (
            <div className="table-pagination">
              <button
                className="pagination-btn"
                onClick={() => handlePageChange(currentPage - 1)}
                disabled={currentPage === 1}
              >
                ←
              </button>
              {Array.from({ length: totalPages }, (_, i) => (
                <button
                  key={i + 1}
                  className={`pagination-btn ${
                    currentPage === i + 1 ? 'active' : ''
                  }`}
                  onClick={() => handlePageChange(i + 1)}
                >
                  {i + 1}
                </button>
              ))}
              <button
                className="pagination-btn"
                onClick={() => handlePageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
              >
                →
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default ReusableTable;
