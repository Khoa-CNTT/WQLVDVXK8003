import React, { useState } from 'react';
import { chatbotService } from '../services/chatbotService';

const Chatbot = () => {
    const [message, setMessage] = useState('');
    const [response, setResponse] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!message.trim()) return;

        setLoading(true);
        setError('');

        try {
            console.log("Received from client:", message);
            const result = await chatbotService.sendMessage(message);
            setResponse(result.response);
        } catch (err) {
            setError('Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto p-4">
            <div className="bg-white rounded-lg shadow-lg p-6">
                <h2 className="text-2xl font-bold mb-4">Chatbot</h2>
                
                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {error}
                    </div>
                )}

                {response && (
                    <div className="mb-4 p-4 bg-gray-100 rounded">
                        <p className="whitespace-pre-wrap">{response}</p>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="flex gap-2">
                    <input
                        type="text"
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        placeholder="Nhập câu hỏi của bạn..."
                        className="flex-1 p-2 border rounded"
                        disabled={loading}
                    />
                    <button
                        type="submit"
                        disabled={loading}
                        className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-blue-300"
                    >
                        {loading ? 'Đang xử lý...' : 'Gửi'}
                    </button>
                </form>
            </div>
        </div>
    );
};

export default Chatbot; 