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
      extraInfo = "Bạn có thể đặt vé trực tuyến trên website hoặc liên hệ hotline 0905.999999 để được hỗ trợ.";
      found = true;
    }

    // Nếu không tìm thấy thông tin liên quan
    if (!found) {
      extraInfo = "Xin lỗi, tôi chỉ có thể hỗ trợ các thông tin về dịch vụ, tuyến xe, lịch trình, vé, khuyến mãi, thanh toán của Nhà xe Phương Thanh Express. Nếu bạn cần hỗ trợ thêm, vui lòng liên hệ hotline 0905.999999 để được tư vấn.";
    }

    const systemPrompt = `
      Bạn là trợ lý ảo của Nhà xe Phương Thanh Express. ${extraInfo}
      Hãy trả lời trực tiếp, ngắn gọn, thân thiện và bằng tiếng Việt cho khách hàng về các dịch vụ, tuyến xe, lịch trình, khuyến mãi, đặt vé, liên hệ, thông tin công ty, và các nội dung có trên website phuongthanhexpress.com. Nếu câu hỏi không liên quan, hãy lịch sự từ chối và đề nghị khách liên hệ hotline 0905.999999 để được hỗ trợ. Tuyệt đối không giải thích về cách bạn trả lời, không nói về vai trò của mình, không liệt kê các bước suy nghĩ, không nói về việc kiểm tra lại thông tin, không nói về quy trình trả lời, không nói về việc sẽ trả lời như thế nào, chỉ trả lời đúng nội dung khách hỏi. Chỉ trả lời một lần, không lặp lại nội dung trong câu trả lời, Quan trọng : .
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
