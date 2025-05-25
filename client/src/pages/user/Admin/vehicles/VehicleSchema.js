import * as yup from 'yup';

const vehicleSchema = yup.object().shape({
  name: yup.string().required('Vui lòng nhập tên xe!'),
  license_plate: yup.string().required('Vui lòng nhập biển số xe!'),
  type: yup.string().required('Vui lòng chọn loại xe!'),
  capacity: yup
    .number()
    .required('Vui lòng nhập số ghế!')
    .typeError('Số ghế phải là số')
    .positive('Số ghế phải lớn hơn 0')
    .integer('Số ghế phải là số nguyên'),
  manufacture_year: yup
    .number()
    .required('Vui lòng nhập năm sản xuất')
    .typeError('Năm sản xuất phải là số')
    .integer('Năm sản xuất phải là số nguyên')
    .min(2000, 'Năm sản xuất không hợp lệ')
    .max(new Date().getFullYear(), 'Năm sản xuất không hợp lệ'),
  status: yup.string().required('Vui lòng chọn trạng thái'),
  amenities: yup.string(),
  model: yup.string(),
});

export default vehicleSchema;
