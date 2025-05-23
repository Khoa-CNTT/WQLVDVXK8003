import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const MySwal = withReactContent(Swal);

/**
 * 
 * @param {string} title 
 * @param {string} text 
 * @param {function} onConfirm 
 */
export const confirmAction = ({ title, text, onConfirm }) => {
  MySwal.fire({
    title,
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Huỷ',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
  }).then((result) => {
    if (result.isConfirmed) {
      onConfirm();
    }
  });
};
