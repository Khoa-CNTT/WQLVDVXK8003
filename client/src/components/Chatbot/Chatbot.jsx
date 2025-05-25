import { useState } from "react";

export default function Chatbot() {
  const [chatbotOpen, setChatbotOpen] = useState(false);
  const [chatMessages, setChatMessages] = useState([]);
  const [chatInput, setChatInput] = useState("");

  const toggleChatbot = () => {
    setChatbotOpen(prev => {
      const newState = !prev;
      if (newState && chatMessages.length === 0) {
        setChatMessages([{
          text: "Xin chào! Tôi là chatbot của Nhà xe Phương Thanh.<br>Tôi có thể giúp bạn:<br>1. Đặt vé xe<br>2. Xem lịch trình<br>3. Tìm hiểu về chúng tôi<br>4. Hỗ trợ khác<br>Bạn cần tôi giúp gì ạ?",
          type: "bot"
        }]);
      }
      return newState;
    });
  };

  const handleChatInputChange = (e) => {
    setChatInput(e.target.value);
  };

  const handleChatSubmit = async (e) => {
    if (e.key === "Enter" && chatInput.trim()) {
      const userMessage = chatInput.trim();
      setChatMessages(prev => [...prev, { text: userMessage, type: "user" }, { text: "Đang trả lời...", type: "loading" }]);
      setChatInput("");
      try {
        const response = await fetch('/api/v1/chatbot/query', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query: userMessage, session_id: '1' })
        });
        const data = await response.json();
        const botResponse = data?.data?.data?.message || data?.data?.message || data?.message || "Bot không trả lời.";
        setChatMessages(prev => {
          const newMsgs = [...prev];
          const idx = newMsgs.findIndex(m => m.type === 'loading');
          if (idx !== -1) newMsgs[idx] = { text: botResponse, type: "bot" };
          return newMsgs;
        });
      } catch (error) {
        setChatMessages(prev => {
          const newMsgs = [...prev];
          const idx = newMsgs.findIndex(m => m.type === 'loading');
          if (idx !== -1) newMsgs[idx] = { text: "Đã xảy ra lỗi khi truy vấn bot. Vui lòng thử lại sau.", type: "bot" };
          return newMsgs;
        });
      }
    }
  };

  return (
    <>
      {chatbotOpen && (
        <div className="chatbot">
          <div className="chatbot-header">
            <h3 className="chatbot-title">Hỗ trợ Phương Thanh Express</h3>
            <button onClick={toggleChatbot} className="chatbot-close">✖</button>
          </div>
          <div className="chatbot-messages">
            {chatMessages.map((message, index) => (
              <div
                key={index}
                className={`message ${message.type === "user" ? "user-message" : "bot-message"}`}
              >
                <span dangerouslySetInnerHTML={{ __html: message.text }} />
              </div>
            ))}
          </div>
          <div className="chatbot-input-container">
            <input
              type="text"
              placeholder="Nhập câu hỏi của bạn..."
              className="chatbot-input"
              value={chatInput}
              onChange={handleChatInputChange}
              onKeyPress={handleChatSubmit}
            />
          </div>
        </div>
      )}
      <button
        onClick={toggleChatbot}
        className={`chatbot-toggle ${chatbotOpen ? 'hidden' : ''}`}
      >
        💬 Chat
      </button>
    </>
  );
}
