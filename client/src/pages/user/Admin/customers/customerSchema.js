import * as yup from 'yup';

const customerSchema = yup.object().shape({
  name: yup.string().required('Vui lòng nhập họ tên'),
  email: yup.string().email('Email không hợp lệ').required('Vui lòng nhập email'),
  phone: yup
    .string()
    .matches(/^0\d{9}$/, 'Số điện thoại không hợp lệ')
    .required('Vui lòng nhập số điện thoại'),
  password: yup.string().when('isEditing', {
    is: false,
    then: (schema) =>
      schema
        .required('Vui lòng nhập mật khẩu')
        .min(8, 'Mật khẩu phải có ít nhất 8 ký tự')
        .matches(
          /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/,
          'Mật khẩu phải bao gồm cả chữ và số'
        ),
    otherwise: (schema) => schema.notRequired(),
  }),
  password_confirmation: yup
    .string()
    .oneOf([yup.ref('password')], 'Mật khẩu xác nhận không khớp')
    .when('isEditing', {
      is: false,
      then: (schema) => schema.required('Vui lòng xác nhận mật khẩu'),
      otherwise: (schema) => schema.notRequired(),
    }),
});

export default customerSchema;
