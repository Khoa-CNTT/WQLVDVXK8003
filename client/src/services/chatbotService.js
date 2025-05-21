import axios from 'axios';

const API_URL = 'http://localhost:8888'; // Port của chatbot server

export const chatbotService = {
    async sendMessage(message) {
        try {
            const response = await axios.post(`${API_URL}/chat`, { message });
            return response.data;
        } catch (error) {
            console.error('Error sending message to chatbot:', error);
            throw error;
        }
    }
}; 