import React from 'react';
import './index.css';

const ReusableModal = ({
  title = 'Modal',
  children,
  onClose,
  onSubmit,
  show = false,
  submitText = 'Lưu',
  cancelText = 'Hủy',
}) => {
  if (!show) return null;

  return (
    <div className="modal">
      <div className="modal-content">
        <h2 className="modal-title">{title}</h2>
        <div className="form-container">{children}</div>
        <div className="modal-actions">
          <button className="cancel-btn" onClick={onClose}>
            {cancelText}
          </button>
          <button className="save-btn" onClick={onSubmit}>
            {submitText}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ReusableModal;
