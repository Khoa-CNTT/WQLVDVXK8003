import React from "react";
import "./security.css";

const Security = () => {
  return (
    <div className="font-poppins bg-gradient min-h-screen">
      {/* Header */}
      <header className="header-gradient text-white py-6 shadow-lg">
        <div className="container mx-auto flex justify-between items-center px-6">
          <h1 className="text-3xl font-bold tracking-tight">Phương Thanh Express</h1>
          <nav>
            <ul className="flex space-x-8">
              <li><a href="/" className="hover:text-orange-200 transition duration-300">Trang Chủ</a></li>
              {/* <li><a href="utilities.html" className="hover:text-orange-200 transition duration-300">Tuyến Hoạt Động</a></li>
              <li><a href="#" className="hover:text-orange-200 transition duration-300">Khuyến Mãi</a></li>
              <li><a href="utilities.html" className="hover:text-orange-200 transition duration-300">Tiện ích</a></li> */}
            </ul>
          </nav>
        </div>
      </header>

      {/* Chính sách bảo mật */}
      <section className="container mx-auto p-8 bg-white shadow-xl rounded-2xl my-10">
        <h2 className="text-4xl font-bold section-title text-center mb-8">CHÍNH SÁCH BẢO MẬT</h2>
        <div className="text-gray-700 leading-relaxed space-y-6">
          {[
            {
              title: "1. Mục đích thu thập thông tin",
              content: "Tại Phương Thanh Express, chúng tôi cam kết bảo vệ thông tin cá nhân của Quý khách. Thông tin được thu thập nhằm mục đích cung cấp dịch vụ vận chuyển, đặt vé, gửi hàng và hỗ trợ khách hàng một cách hiệu quả nhất. Các thông tin này bao gồm: họ tên, số điện thoại, email, địa chỉ và thông tin thanh toán."
            },
            {
              title: "2. Phạm vi thu thập thông tin",
              content: "Chúng tôi chỉ thu thập thông tin khi Quý khách sử dụng dịch vụ trên website, ứng dụng hoặc liên hệ trực tiếp qua hotline. Thông tin được thu thập sẽ được sử dụng trong phạm vi các hoạt động kinh doanh của Phương Thanh Express và không vượt quá mục đích đã nêu."
            },
            {
              title: "3. Thời gian lưu trữ thông tin",
              content: "Thông tin cá nhân của Quý khách sẽ được lưu trữ trong thời gian cần thiết để thực hiện các dịch vụ hoặc theo yêu cầu của pháp luật. Sau khi không còn cần thiết, thông tin sẽ được xóa bỏ hoặc ẩn danh để đảm bảo an toàn."
            },
            {
              title: "4. Bảo mật thông tin",
              content: "Chúng tôi áp dụng các biện pháp kỹ thuật và tổ chức tiên tiến để bảo vệ thông tin cá nhân khỏi truy cập trái phép, mất mát hoặc lạm dụng. Dữ liệu được mã hóa trong quá trình truyền tải và lưu trữ trên hệ thống an toàn."
            },
            {
              title: "5. Chia sẻ thông tin",
              content: "Phương Thanh Express cam kết không bán, trao đổi hoặc chia sẻ thông tin cá nhân của Quý khách với bên thứ ba, trừ khi có sự đồng ý của Quý khách hoặc theo yêu cầu của cơ quan pháp luật."
            },
            {
              title: "6. Quyền của khách hàng",
              content: "Quý khách có quyền yêu cầu xem, chỉnh sửa hoặc xóa thông tin cá nhân của mình bất kỳ lúc nào. Để thực hiện, vui lòng liên hệ qua email phuongthanh@gmail.com hoặc hotline 0905.999999."
            },
            {
              title: "7. Thay đổi chính sách",
              content: "Chính sách bảo mật này có thể được cập nhật để phù hợp với quy định pháp luật hoặc cải thiện dịch vụ. Mọi thay đổi sẽ được thông báo trên website chính thức của chúng tôi."
            }
          ].map((item, index) => (
            <div key={index}>
              <h3 className="text-2xl font-semibold text-orange-600">{item.title}</h3>
              <p className="mt-2">{item.content}</p>
            </div>
          ))}
          <div className="text-center mt-8">
            <p className="font-bold text-orange-600 italic text-lg">
              Cảm ơn Quý khách đã tin tưởng và đồng hành cùng <strong>Phương Thanh Express</strong>!
            </p>
            <a href="/" className="mt-6 inline-block btn-modern text-white px-6 py-3 rounded-lg">Quay lại Trang Chủ</a>
          </div>
        </div>
      </section>

      {/* Footer có thể tách component riêng nếu muốn */}
    </div>
  );
};

export default Security;
