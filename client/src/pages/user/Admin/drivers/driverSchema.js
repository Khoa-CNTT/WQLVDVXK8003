// validations/driverSchema.js
import * as yup from 'yup';

export const driverSchema = yup.object().shape({
    name: yup.string().required('Vui lòng nhập họ tên'),
    phone: yup
        .string()
        .required('Vui lòng nhập số điện thoại')
        .matches(/^0\d{9}$/, 'Số điện thoại không hợp lệ'),
    birth_date: yup
        .date()
        .required('Vui lòng chọn ngày sinh')
        .typeError('Ngày sinh không hợp lệ'),
    experience_years: yup
        .number()
        .required('Vui lòng nhập số năm kinh nghiệm')
        .typeError('Kinh nghiệm phải là số')
        .min(0, 'Kinh nghiệm không được âm')
        .integer('Kinh nghiệm phải là số nguyên'),
    license_number: yup
        .string()
        .required('Vui lòng nhập số giấy phép lái xe')
        .matches(/^(B[12]|D|E|FD|FE)-\d{6}$/, 'Số giấy phép không hợp lệ. Định dạng: D-123456, B2-123456,...'),
    license_expiry: yup
        .date()
        .required('Vui lòng chọn ngày hết hạn')
        .typeError('Ngày hết hạn không hợp lệ')
        .min(new Date(new Date().setDate(new Date().getDate() + 1)), 'Ngày hết hạn phải sau hôm nay'),
    status: yup.string().required('Vui lòng chọn trạng thái'),
});
