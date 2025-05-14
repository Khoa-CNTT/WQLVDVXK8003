import React from 'react';
import './index.css'
const Table = ({ columns, data, loading, error, onEdit, onDelete }) => {
  return (
    <div className="table-container">
      {loading ? (
        <div className="table-loading">Đang tải dữ liệu...</div>
      ) : error ? (
        <div className="table-error">{error}</div>
      ) : (
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
            {data.length === 0 ? (
              <tr>
                <td colSpan={columns.length + 1}>Không có dữ liệu</td>
              </tr>
            ) : (
              data.map((item, idx) => (
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
                            Xóa
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
      )}
    </div>
  );
};

export default Table;
