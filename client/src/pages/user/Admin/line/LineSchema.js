import * as Yup from 'yup';

const LineSchema = Yup.object().shape({
    departure: Yup.string().required('Vui lòng nhập nơi xuất phát'),
    destination: Yup.string()
        .required('Vui lòng nhập nơi đến')
        .test(
            'not-same-as-departure',
            'Nơi đến phải khác nơi xuất phát',
            function (value) {
                const { departure } = this.parent;
                if (!value || !departure) return true; 
                return value.trim().toLowerCase() !== departure.trim().toLowerCase();
            }
        ),
    distance: Yup
        .number()
        .typeError('Khoảng cách phải là số')
        .transform((value, originalValue) => (originalValue === '' ? undefined : value))
        .required('Khoảng cách không được để trống')
        .min(50, 'Khoảng cách phải lớn hơn hoặc bằng 50 km'),
    duration: Yup.string().required('Vui lòng nhập thời gian'),
    base_price: Yup
        .number()
        .typeError('Đơn giá phải là số')
        .transform((value, originalValue) => (originalValue === '' ? undefined : value))
        .required('Đơn giá không được để trống')
        .min(50000, 'Đơn giá phải lớn hơn hoặc bằng 50,000'),
    description: Yup.string().required('Vui lòng nhập mô tả'),
    status: Yup.string().oneOf(['active', 'inactive'], 'Trạng thái không hợp lệ'),
});

export default LineSchema;
