const express = require("express");
const cors = require("cors");
const OpenAI = require("openai");
const mysql = require('mysql2/promise');

const app = express();
const port = 8888;

app.use(cors());
app.use(express.json());

const openai = new OpenAI({
  apiKey: "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",
  baseURL: "https://integrate.api.nvidia.com/v1",
});

const pool = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: '', // sửa nếu bạn có mật khẩu MySQL
  database: 'phuongthanh_express'
});

app.post("/chat", async (req, res) => {
  const { message } = req.body;
  // Xử lý riêng các câu chào
  if (/^(xin chào|hello|hi|chào bạn|chào|xin chào chatbot)$/i.test(message.trim())) {
    return res.json({ response: "Xin chào! 😊 Chào mừng bạn đến với Phương Thanh Express. Bạn cần hỗ trợ thông tin về tuyến xe, lịch trình, đặt vé hay khuyến mãi nào ạ?" });
  }
  let extraInfo = "";
  let found = false;

  try {
    // Ưu tiên tuyến xe, giá vé, lịch trình
    if (/tuyến xe|giá vé|lịch trình/i.test(message)) {
      const [lines] = await pool.query('SELECT departure, destination FROM `lines`');
      const [tickets] = await pool.query('SELECT base_price, departure, destination FROM `lines`');
      extraInfo = "Các tuyến xe hiện có:\n" +
        lines.map(l => `- ${l.departure} - ${l.destination}`).join('\n') +
        "\n\nGiá vé một số tuyến:\n" +
        tickets.map(t => `- ${t.departure} - ${t.destination}: ${t.base_price}đ`).join('\n');
      found = true;
    } else if (/xe|loại xe|phương tiện/i.test(message)) {
      const [vehicles] = await pool.query('SELECT name, type FROM vehicles');
      extraInfo = "Các loại xe:\n" +
        vehicles.map(v => `- ${v.name} (${v.type})`).join('\n');
      found = true;
    } else if (/tài xế|lái xe/i.test(message)) {
      const [drivers] = await pool.query('SELECT name, phone FROM drivers');
      extraInfo = "Một số tài xế:\n" +
        drivers.map(d => `- ${d.name} (${d.phone})`).join('\n');
      found = true;
    } else if (/khuyến mãi|giảm giá|ưu đãi/i.test(message)) {
      extraInfo = "Khuyến mãi: Giảm 10% cho khách hàng thân thiết, tặng mã giảm giá cho chuyến đầu tiên.";
      found = true;
    } else if (/vé|đặt vé|trạng thái vé/i.test(message)) {
      // Trả lời cứng, không để AI sinh nữa
      return res.json({ response: "Bạn có thể đặt vé xe Phương Thanh Express trên website phuongthanhexpress.com, qua app hoặc gọi hotline 0905.999999 để được hỗ trợ nhanh nhất ạ!" });
    } else if (/tiện ích|dịch vụ|trên xe|wifi|nước uống|ghế|giường|chăn|gối|giải trí/i.test(message)) {
      return res.json({ response: "Xe Phương Thanh Express có wifi, nước uống, giường nằm, chăn gối sạch sẽ, điều hòa và hệ thống giải trí hiện đại." });
    } else if (/thanh toán|trả tiền|tiền mặt|chuyển khoản|quẹt thẻ/i.test(message)) {
      return res.json({ response: "Bạn có thể thanh toán khi đặt vé bằng chuyển khoản, ví điện tử hoặc trả tiền mặt khi lên xe." });
    } else if (/đổi vé|đổi chuyến|đổi ngày/i.test(message)) {
      return res.json({ response: "Bạn có thể đổi vé hoặc đổi chuyến trước giờ khởi hành, vui lòng liên hệ hotline 0905.999999 để được hỗ trợ." });
    } else if (/hoàn vé|hủy vé|trả vé/i.test(message)) {
      return res.json({ response: "Bạn có thể hoàn vé theo chính sách của nhà xe, vui lòng liên hệ hotline 0905.999999 để được hướng dẫn chi tiết." });
    } else if (/giá vé|bao nhiêu tiền|vé bao nhiêu/i.test(message)) {
      return res.json({ response: "Giá vé tùy tuyến, bạn có thể xem chi tiết trên website phuongthanhexpress.com hoặc gọi hotline 0905.999999 để biết thêm." });
    } else if (/lịch trình|giờ chạy|giờ xuất phát|giờ xe chạy/i.test(message)) {
      return res.json({ response: "Bạn có thể xem lịch trình, giờ xuất phát các tuyến trên website hoặc liên hệ hotline 0905.999999 để được tư vấn." });
    } else if (/gửi hàng|chuyển hàng|vận chuyển|gửi đồ|gửi xe|gửi hàng xe khách|gửi hàng xe khách phương thanh/i.test(message)) {
      return res.json({ response: "Bạn có thể gửi hàng qua hotline 0905.888.888 (Anh Mạnh) hoặc đến văn phòng tại Đà Nẵng. Chúng tôi nhận gửi hàng, gửi xe máy, chuyển phát nhanh." });
    } else if (/chính sách khách hàng|chính sách|bảo hiểm|an toàn|hoàn vé|đổi vé|trả vé|chính sách xe giường nằm/i.test(message)) {
      return res.json({ response: "Chúng tôi có chính sách hoàn/đổi vé linh hoạt, bảo hiểm hành khách đầy đủ và cam kết an toàn tuyệt đối cho khách hàng." });
    } else if (/tài xế|lái xe|phục vụ|nhân viên|tài xế xe khách|tài xế xe khách phương thanh/i.test(message)) {
      return res.json({ response: "Đội ngũ tài xế và nhân viên Phương Thanh được đào tạo chuyên nghiệp, phục vụ tận tâm, lịch sự và chu đáo." });
    } else if (/đón trả|điểm đón|điểm trả|bến xe|trung chuyển|đón trả xe khách|đón trả xe khách phương thanh/i.test(message)) {
      return res.json({ response: "Nhà xe có nhiều điểm đón/trả linh hoạt tại Đà Nẵng, Quảng Bình, Nghệ An, Hà Giang, HCM... và hỗ trợ trung chuyển tận nơi trong nội thành." });
    } else if (/hành lý|vali|balo|gửi hành lý|quy định hành lý|hành lý xe khách|hành lý xe khách phương thanh/i.test(message)) {
      return res.json({ response: "Mỗi khách được mang theo 1 vali và 1 balo miễn phí. Nếu có thêm hành lý cồng kềnh, vui lòng liên hệ trước để được hỗ trợ." });
    } else if (/thời gian chạy|bao lâu|mất bao lâu|di chuyển|thời gian chạy khoảng bao lâu/i.test(message)) {
      return res.json({ response: "Thời gian di chuyển tùy tuyến, ví dụ Đà Nẵng - Quảng Bình khoảng 6-7 tiếng, Đà Nẵng - Nghệ An khoảng 10-12 tiếng. Bạn cần hỏi tuyến cụ thể để biết chi tiết." });
    } else if (/feedback|đánh giá|phản hồi|góp ý|khiếu nại|feedback xe khách|feedback xe khách phương thanh|ý kiến/i.test(message)) {
      return res.json({ response: "Bạn có thể gửi góp ý, phản hồi hoặc khiếu nại qua hotline hoặc email phuongthanh@gmail.com. Chúng tôi luôn lắng nghe để phục vụ tốt hơn." });
    } else if (/trẻ em|vé trẻ em|em bé|bé|trẻ nhỏ|trẻ em xe khách|trẻ em xe khách phương thanh|người già|người già xe khách|người già xe khách phương thanh|cựu chiến binh/i.test(message)) {
      return res.json({ response: "Trẻ em dưới 5 tuổi được miễn phí vé nếu ngồi cùng người lớn. Trẻ từ 5 tuổi trở lên cần mua vé riêng." });
    } else if (/vật nuôi|thú cưng|chó|mèo|pet|thú nuôi/i.test(message)) {
      return res.json({ response: "Nhà xe hỗ trợ vận chuyển vật nuôi nhỏ, vui lòng liên hệ trước để được tư vấn chi tiết." });
    } else if (/xuất bến|giờ xuất bến|lịch xuất bến/i.test(message)) {
      return res.json({ response: "Xe xuất bến nhiều khung giờ trong ngày: 6h, 10h, 14h, 20h. Bạn vui lòng chọn giờ phù hợp khi đặt vé." });
    } else if (/mất đồ|quên đồ|bỏ quên|đồ thất lạc/i.test(message)) {
      return res.json({ response: "Nếu bạn bỏ quên đồ trên xe, hãy liên hệ ngay hotline để được hỗ trợ tìm lại." });
    } else if (/ưu đãi sinh viên|giảm giá sinh viên|vé sinh viên|sinh viên/i.test(message)) {
      return res.json({ response: "Nhà xe có chính sách giảm giá cho sinh viên, vui lòng xuất trình thẻ sinh viên khi đặt vé." });
    } else if (/đặt vé nhóm|đoàn|nhiều người|đặt nhiều vé|đoàn vé/i.test(message)) {
      return res.json({ response: "Đặt vé nhóm từ 5 người trở lên sẽ được ưu đãi đặc biệt, vui lòng liên hệ hotline để nhận báo giá." });
    } else if (/xe trung chuyển|trung chuyển miễn phí|xe đưa đón/i.test(message)) {
      return res.json({ response: "Nhà xe có xe trung chuyển miễn phí trong nội thành Đà Nẵng và các điểm lớn, vui lòng báo trước khi đặt vé." });
    } else if (/người già|hỗ trợ người già|người khuyết tật|phụ nữ mang thai/i.test(message)) {
      return res.json({ response: "Nhà xe ưu tiên hỗ trợ người già, người khuyết tật, phụ nữ mang thai. Vui lòng báo trước để được phục vụ tốt nhất." });
    } else if (/trẻ em đi một mình|bé đi một mình|gửi trẻ em/i.test(message)) {
      return res.json({ response: "Trẻ em dưới 12 tuổi không được đi xe một mình. Nếu cần gửi trẻ, phải có người lớn đi kèm." });
    } else if (/điều kiện thời tiết|mưa bão|hoãn chuyến|dời chuyến/i.test(message)) {
      return res.json({ response: "Trong trường hợp thời tiết xấu, nhà xe sẽ chủ động liên hệ khách để đổi/hoãn chuyến và hỗ trợ hoàn tiền nếu cần." });
    } else if (/bảo mật|an ninh|thông tin cá nhân/i.test(message)) {
      return res.json({ response: "Nhà xe cam kết bảo mật thông tin cá nhân và an ninh cho khách hàng." });
    } else if (/đặt vé online|website|app|ứng dụng/i.test(message)) {
      return res.json({ response: "Bạn có thể đặt vé online qua website hoặc ứng dụng, thanh toán linh hoạt và nhận vé điện tử nhanh chóng." });
    } else if (/hủy vé|bỏ vé|không đi/i.test(message)) {
      return res.json({ response: "Bạn có thể hủy vé trước giờ xuất bến tối thiểu 2 tiếng để được hoàn tiền theo chính sách." });
    } else if (/chuyển tuyến|đi tuyến khác|đổi tuyến/i.test(message)) {
      return res.json({ response: "Bạn có thể chuyển tuyến nếu còn chỗ, vui lòng liên hệ hotline để được hỗ trợ." });
    } else if (/đặt vé tết|vé tết|lịch tết|giá vé tết/i.test(message)) {
      return res.json({ response: "Nhà xe mở bán vé Tết sớm, giá vé có thể thay đổi theo từng thời điểm. Vui lòng liên hệ hotline để đặt vé Tết." });
    } else if (/đặt vé khứ hồi|vé khứ hồi|giảm giá khứ hồi/i.test(message)) {
      return res.json({ response: "Đặt vé khứ hồi sẽ được giảm giá, vui lòng báo trước khi đặt để nhận ưu đãi." });
    } else if (/đặt vé qua điện thoại|gọi đặt vé|đặt vé qua hotline/i.test(message)) {
      return res.json({ response: "Bạn có thể đặt vé qua hotline 0905.3333.33, nhân viên sẽ hỗ trợ bạn nhanh chóng." });
    } else if (/đặt vé cho người khác|mua vé hộ|đặt hộ vé/i.test(message)) {
      return res.json({ response: "Bạn có thể đặt vé cho người thân, chỉ cần cung cấp thông tin người đi khi đặt vé." });
    } else if (/chính sách đổi trả|đổi trả vé|đổi vé|trả vé/i.test(message)) {
      return res.json({ response: "Chính sách đổi trả vé linh hoạt, hoàn tiền theo quy định. Vui lòng liên hệ để biết chi tiết." });
    } else if (/giờ làm việc|thời gian làm việc|mấy giờ mở cửa/i.test(message)) {
      return res.json({ response: "Văn phòng làm việc từ 7h00 đến 21h00 hàng ngày, hotline hoạt động 24/7." });
    } else if (/đặt vé quốc tế|đi nước ngoài|tuyến quốc tế/i.test(message)) {
      return res.json({ response: "Hiện tại nhà xe chỉ phục vụ các tuyến nội địa Việt Nam." });
    } else if (/đặt vé xe máy|gửi xe máy|vận chuyển xe máy/i.test(message)) {
      return res.json({ response: "Bạn có thể gửi xe máy cùng chuyến, vui lòng báo trước để được sắp xếp chỗ." });
    } else if (/đặt vé xe khách|xe khách|xe bus/i.test(message)) {
      return res.json({ response: "Nhà xe Phương Thanh chuyên xe khách giường nằm chất lượng cao, đặt vé dễ dàng qua website hoặc hotline." });
    }

    // Nếu không tìm thấy thông tin liên quan
    if (!found) {
      extraInfo = "Xin lỗi, tôi chỉ có thể hỗ trợ các thông tin về dịch vụ, tuyến xe, lịch trình, vé, khuyến mãi, thanh toán của Nhà xe Phương Thanh Express. Nếu bạn cần hỗ trợ thêm, vui lòng liên hệ hotline 0905.999999 để được tư vấn.";
    }

    const systemPrompt = `
      Bạn là trợ lý ảo của Nhà xe Phương Thanh Express. ${extraInfo}
      Luôn trả lời ngắn gọn, đúng trọng tâm, tối đa 2-3 câu, không giải thích, không nói về quy trình, không nói về dữ liệu, không nói về kiến thức của mình. Nếu không biết, hãy đề nghị khách liên hệ hotline 0905.999999.
    `;

    const completion = await openai.chat.completions.create({
      model: "deepseek-ai/deepseek-r1",
      messages: [
        {
          role: "system",
          content: systemPrompt,
        },
        {
          role: "user",
          content: message,
        },
      ],
      temperature: 0.6,
      top_p: 0.7,
      max_tokens: 512,
      stream: false,
    });

    if (!completion || !completion.choices || completion.choices.length === 0) {
      return res.status(500).json({ error: "API không trả về phản hồi hợp lệ" });
    }

    res.json({ response: completion.choices[0].message.content });
  } catch (error) {
    console.error("Lỗi khi gọi API OpenAI hoặc MySQL:", error);
    res.status(500).json({ error: "Có lỗi xảy ra khi xử lý yêu cầu" });
  }
});

app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
