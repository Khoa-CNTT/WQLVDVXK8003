import * as yup from 'yup';

const tripSchema = yup.object().shape({
  line_id: yup.string().required('Vui lòng chọn tuyến đường'),
  vehicle_id: yup.string().required('Vui lòng chọn phương tiện'),
  driver_id: yup.string().required('Vui lòng chọn tài xế'),
  departure_time: yup.string().required('Vui lòng chọn thời gian khởi hành'),
  arrival_time: yup.string().required('Vui lòng chọn thời gian đến dự kiến'),
  price: yup
    .number()
    .required('Vui lòng nhập giá vé')
    .typeError('Giá vé phải là số')
    .positive('Giá vé phải lớn hơn 0'),
  status: yup.string().required('Vui lòng chọn trạng thái'),
});

export default tripSchema;
