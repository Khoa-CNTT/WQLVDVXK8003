import axios from 'axios';

const API_URL = '/api/v1/chatbot'; // API endpoint của Laravel

export const chatbotService = {
    async sendMessage(message) {
        try {
            const response = await axios.post(`${API_URL}/query`, { 
                query: message,
                session_id: '1' // Có thể thay đổi session_id tùy theo user
            });
            return response.data;
        } catch (error) {
            console.error('Error sending message to chatbot:', error);
            throw error;
        }
    }
}; 