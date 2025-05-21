<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ChatbotService
{
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;
    protected $cacheTtl;

    public function __construct()
    {
        $this->apiKey = env('CHATBOT_API_KEY', 'f5f2d613156777d0e0455273788d46e4');
        $this->baseUrl = Config::get('chatbot.base_url');
        $this->timeout = Config::get('chatbot.timeout');
        $this->cacheTtl = Config::get('chatbot.cache_ttl');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Chatbot API key is not configured');
        }
    }

    /**
     * Gửi câu hỏi đến chatbot và nhận phản hồi
     *
     * @param string $query Câu hỏi của người dùng
     * @param string $sessionId ID phiên chat (có thể là user_id hoặc một unique identifier)
     * @return array
     */
    public function sendQuery(string $query, string $sessionId): array
    {
        $query = mb_strtolower($query);

        Log::info('Chatbot query received', ['query' => $query]);

        // Danh sách từ khóa và câu trả lời
        $faq = [
            [
                'keywords' => ['Xin Chào', 'Hello','Alo','Chào'],
                'answer' => 'Xin chào! Tôi là chatbot của Nhà xe Phương Thanh. Tôi có thể giúp bạn:\n1. Đặt vé xe\n2. Xem lịch trình\n3. Tìm hiểu về chúng tôi\n4. Hỗ trợ khác\nBạn cần tôi giúp gì ạ?'
            ],
            [
                'keywords' => ['1', 'đặt vé xe', 'đặt vé'],
                'answer' => 'Bạn muốn đặt vé xe? Vui lòng cho biết:\n1. Tuyến xe bạn muốn đi\n2. Ngày khởi hành\n3. Số lượng vé\n4. Thông tin liên hệ'
            ],
            [
                'keywords' => ['2', 'xem lịch trình', 'lịch trình'],
                'answer' => 'Bạn muốn xem lịch trình tuyến nào? Vui lòng chọn:\n1. Đà Nẵng - Quảng Bình\n2. Đà Nẵng - Nghệ An\n3. Đà Nẵng - Hà Giang\n4. Đà Nẵng - HCM'
            ],
            [
                'keywords' => ['3', 'tìm hiểu về chúng tôi', 'thông tin'],
                'answer' => 'Bạn muốn tìm hiểu thông tin gì về chúng tôi?\n1. Giới thiệu về nhà xe\n2. Tiện nghi xe\n3. Chính sách khách hàng\n4. Liên hệ'
            ],
            [
                'keywords' => ['4', 'hỗ trợ khác', 'giúp đỡ'],
                'answer' => 'Bạn cần hỗ trợ gì thêm?\n1. Gửi hàng\n2. Đổi/hủy vé\n3. Khiếu nại\n4. Quay lại menu chính'
            ],
            // Thêm các câu trả lời chi tiết cho từng lựa chọn
            [
                'keywords' => ['đà nẵng - quảng bình', 'quảng bình'],
                'answer' => 'Tuyến Đà Nẵng - Quảng Bình:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 6-7 tiếng\n- Giá vé: 250.000đ - 350.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['đà nẵng - nghệ an', 'nghệ an'],
                'answer' => 'Tuyến Đà Nẵng - Nghệ An:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 10-12 tiếng\n- Giá vé: 350.000đ - 450.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['đà nẵng - hà giang', 'hà giang'],
                'answer' => 'Tuyến Đà Nẵng - Hà Giang:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 24-26 tiếng\n- Giá vé: 650.000đ - 750.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['đà nẵng - hcm', 'hcm', 'sài gòn'],
                'answer' => 'Tuyến Đà Nẵng - HCM:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 18-20 tiếng\n- Giá vé: 550.000đ - 650.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['giới thiệu về nhà xe', 'nhà xe'],
                'answer' => 'Nhà xe Phương Thanh:\n- Thành lập năm 2010\n- Đội xe hiện đại, an toàn\n- Đội ngũ tài xế chuyên nghiệp\n- Dịch vụ chất lượng cao\nBạn muốn biết thêm thông tin gì?'
            ],
            [
                'keywords' => ['tiện nghi xe', 'tiện nghi'],
                'answer' => 'Tiện nghi trên xe:\n1. Wifi miễn phí\n2. Nước uống miễn phí\n3. Chăn đắp\n4. Nhà vệ sinh\n5. Điều hòa\nBạn cần biết thêm gì không?'
            ],
            [
                'keywords' => ['chính sách khách hàng', 'chính sách'],
                'answer' => 'Chính sách khách hàng:\n1. Hoàn/đổi vé\n2. Bảo hiểm hành khách\n3. Ưu đãi sinh viên\n4. Chính sách nhóm\nBạn muốn biết chi tiết về chính sách nào?'
            ],
            [
                'keywords' => ['liên hệ', 'contact'],
                'answer' => 'Thông tin liên hệ:\n- Hotline: 0905.3333.33\n- Email: phuongthanh@gmail.com\n- Địa chỉ: 12 Bàu Cầu 12, Hòa Xuân, Hòa Vang, Đà Nẵng\nBạn cần hỗ trợ gì thêm?'
            ],
            [
                'keywords' => ['gửi hàng', 'chuyển hàng'],
                'answer' => 'Dịch vụ gửi hàng:\n1. Gửi hàng thông thường\n2. Gửi xe máy\n3. Chuyển phát nhanh\n4. Liên hệ hotline\nBạn muốn gửi loại hàng nào?'
            ],
            [
                'keywords' => ['đổi/hủy vé', 'hủy vé'],
                'answer' => 'Chính sách đổi/hủy vé:\n1. Hủy vé trước 2h\n2. Đổi vé trước 2h\n3. Hoàn tiền\n4. Quay lại menu chính\nBạn cần thực hiện thao tác nào?'
            ],
            [
                'keywords' => ['khiếu nại', 'phản hồi'],
                'answer' => 'Khiếu nại/Phản hồi:\n1. Gửi email\n2. Gọi hotline\n3. Đến văn phòng\n4. Quay lại menu chính\nBạn muốn gửi khiếu nại qua kênh nào?'
            ],
            [
                'keywords' => ['quay lại menu chính', 'menu chính', 'quay lại'],
                'answer' => 'Xin chào! Tôi là chatbot của Nhà xe Phương Thanh. Tôi có thể giúp bạn:\n1. Đặt vé xe\n2. Xem lịch trình\n3. Tìm hiểu về chúng tôi\n4. Hỗ trợ khác\nBạn cần tôi giúp gì ạ?'
            ],
            [
                'keywords' => ['giá vé', 'bao nhiêu tiền', 'giá', 'vé', 'vé xe', 'vé xe khách'],
                'answer' => 'Giá vé tùy vào tuyến và thời điểm. Bạn vui lòng cung cấp tuyến đi và ngày đi để được báo giá chính xác.'
            ],
            [
                'keywords' => ['lịch trình', 'giờ chạy', 'thời gian', 'mấy giờ', 'khởi hành', 'lịch trình xe', 'lịch trình xe khách','lịch trình xe khách phương thanh'],
                'answer' => 'Bạn muốn xem lịch trình tuyến nào? Vui lòng cung cấp điểm đi và điểm đến hoặc xem mục Tuyến Hoạt Động trên website.'
            ],
            [
                'keywords' => ['wifi', 'tiện nghi', 'nước', 'dịch vụ', 'máy lạnh', 'điều hòa', 'tivi', 'chăn', 'nhà vệ sinh', 'tiện nghi', 'tiện nghi xe', 'tiện nghi xe khách', 'tiện nghi xe khách phương thanh','tiện nghi xe khách phương thanh phương thanh'],
                'answer' => 'Xe Phương Thanh có wifi, nước uống, điều hòa, chăn đắp, tivi, nhà vệ sinh và nhiều tiện nghi khác.'
            ],
            [
                'keywords' => ['khuyến mãi', 'giảm giá', 'ưu đãi', 'chương trình', 'tích điểm', 'thân thiết', 'blind box', 'khuyến mãi xe khách', 'khuyến mãi xe khách phương thanh', 'khuyến mãi xe khách phương thanh phương thanh','Nhà xe bạn đang có chương trình khuyến mãi gì'],
                'answer' => 'Chúng tôi có nhiều chương trình khuyến mãi: giảm giá cho khách hàng thân thiết, Blind Box trúng thưởng iPhone, mã giảm giá và nhiều quà tặng hấp dẫn.'
            ],
            [
                'keywords' => ['gửi hàng', 'chuyển hàng', 'vận chuyển', 'gửi đồ', 'gửi xe', 'gửi hàng xe khách', 'gửi hàng xe khách phương thanh'],
                'answer' => 'Bạn có thể gửi hàng qua hotline 0905.888.888 (Anh Mạnh) hoặc đến văn phòng tại Đà Nẵng. Chúng tôi nhận gửi hàng, gửi xe máy, chuyển phát nhanh.'
            ],
            [
                'keywords' => ['đặt vé', 'mua vé', 'book vé', 'đặt chỗ', 'mua chỗ', 'đặt vé xe khách', 'đặt vé xe khách phương thanh'],
                'answer' => 'Bạn có thể đặt vé trực tuyến trên website hoặc gọi hotline 0905.3333.33 để được hỗ trợ đặt chỗ nhanh nhất.'
            ],
            [
                'keywords' => ['hotline', 'số điện thoại', 'liên hệ', 'tổng đài', 'gọi điện', 'hotline xe khách', 'hotline xe khách phương thanh','liên hệ xe khách', 'liên hệ xe khách phương thanh','liên lạc', 'liên lạc xe khách', 'liên lạc xe khách phương thanh'],
                'answer' => 'Hotline đặt vé: 0905.3333.33 | Gửi hàng: 0905.888.888 (Anh Mạnh) | Thuê xe: 0905.1111.11 | Hợp đồng: 0905.2222.22 (Anh Hùng)'
            ],
            [
                'keywords' => ['địa chỉ', 'văn phòng', 'trụ sở', 'đâu', 'ở đâu', 'địa chỉ xe khách', 'địa chỉ nhà xe phương thanh'],
                'answer' => 'Văn phòng nhà xe Phương Thanh: 12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.'
            ],
            [
                'keywords' => ['xe giường nằm', 'loại xe', 'ghế', 'giường', 'phòng', 'chất lượng', 'xe giường nằm xe khách', 'xe giường nằm xe khách phương thanh'],
                'answer' => 'Nhà xe Phương Thanh sử dụng xe giường nằm cao cấp, có phòng riêng, ghế massage, wifi, nước uống miễn phí và nhiều tiện nghi khác.'
            ],
            [
                'keywords' => ['chính sách', 'bảo hiểm', 'an toàn', 'hoàn vé', 'đổi vé', 'trả vé', 'chính sách xe giường nằm', 'chính sách xe giường nằm xe khách', 'chính sách xe giường nằm xe khách phương thanh'],
                'answer' => 'Chúng tôi có chính sách hoàn/đổi vé linh hoạt, bảo hiểm hành khách đầy đủ và cam kết an toàn tuyệt đối cho khách hàng.'
            ],
            [
                'keywords' => ['tài xế', 'lái xe', 'phục vụ', 'nhân viên', 'tài xế xe khách', 'tài xế xe khách phương thanh'],
                'answer' => 'Đội ngũ tài xế và nhân viên Phương Thanh được đào tạo chuyên nghiệp, phục vụ tận tâm, lịch sự và chu đáo.'
            ],
            [
                'keywords' => ['thanh toán', 'trả tiền', 'momo', 'vnpay', 'chuyển khoản', 'tiền mặt', 'thanh toán xe khách', 'thanh toán xe khách phương thanh'],
                'answer' => 'Bạn có thể thanh toán bằng tiền mặt, chuyển khoản, ví MoMo, VNPAY hoặc các phương thức thanh toán điện tử khác.'
            ],
            [
                'keywords' => ['đón trả', 'điểm đón', 'điểm trả', 'bến xe', 'trung chuyển', 'đón trả xe khách', 'đón trả xe khách phương thanh'],
                'answer' => 'Nhà xe có nhiều điểm đón/trả linh hoạt tại Đà Nẵng, Quảng Bình, Nghệ An, Hà Giang, HCM... và hỗ trợ trung chuyển tận nơi trong nội thành.'
            ],
            [
                'keywords' => ['hành lý', 'vali', 'balo', 'gửi hành lý', 'quy định hành lý', 'hành lý xe khách', 'hành lý xe khách phương thanh','Hành lý của tôi'],
                'answer' => 'Mỗi khách được mang theo 1 vali và 1 balo miễn phí. Nếu có thêm hành lý cồng kềnh, vui lòng liên hệ trước để được hỗ trợ.'
            ],
            [
                'keywords' => ['thời gian chạy', 'bao lâu', 'mất bao lâu', 'di chuyển','thời gian chạy khoảng bao lâu'],
                'answer' => 'Thời gian di chuyển tùy tuyến, ví dụ Đà Nẵng - Quảng Bình khoảng 6-7 tiếng, Đà Nẵng - Nghệ An khoảng 10-12 tiếng. Bạn cần hỏi tuyến cụ thể để biết chi tiết.'
            ],
            [
                'keywords' => ['feedback', 'đánh giá', 'phản hồi', 'góp ý', 'khiếu nại', 'feedback xe khách', 'feedback xe khách phương thanh','ý kiến'],
                'answer' => 'Bạn có thể gửi góp ý, phản hồi hoặc khiếu nại qua hotline hoặc email phuongthanh@gmail.com. Chúng tôi luôn lắng nghe để phục vụ tốt hơn.'
            ],
            [
                'keywords' => ['trẻ em', 'vé trẻ em', 'em bé', 'bé', 'trẻ nhỏ', 'trẻ em xe khách', 'trẻ em xe khách phương thanh','người già', 'người già xe khách', 'người già xe khách phương thanh','cựu chiến binh'],
                'answer' => 'Trẻ em dưới 5 tuổi được miễn phí vé nếu ngồi cùng người lớn. Trẻ từ 5 tuổi trở lên cần mua vé riêng.'
            ],
            [
                'keywords' => ['vật nuôi', 'thú cưng', 'chó', 'mèo', 'pet','thú nuôi'],
                'answer' => 'Nhà xe hỗ trợ vận chuyển vật nuôi nhỏ, vui lòng liên hệ trước để được tư vấn chi tiết.'
            ],
            [
                'keywords' => ['xuất bến', 'giờ xuất bến', 'lịch xuất bến'],
                'answer' => 'Xe xuất bến nhiều khung giờ trong ngày: 6h, 10h, 14h, 20h. Bạn vui lòng chọn giờ phù hợp khi đặt vé.'
            ],
            [
                'keywords' => ['đổi lịch', 'đổi chuyến', 'chuyển chuyến', 'chuyển vé'],
                'answer' => 'Bạn có thể đổi lịch/chuyến trước giờ xuất bến tối thiểu 2 tiếng, vui lòng liên hệ hotline để được hỗ trợ.'
            ],
            [
                'keywords' => ['mất đồ', 'quên đồ', 'bỏ quên', 'đồ thất lạc'],
                'answer' => 'Nếu bạn bỏ quên đồ trên xe, hãy liên hệ ngay hotline để được hỗ trợ tìm lại.'
            ],
            [
                'keywords' => ['ưu đãi sinh viên', 'giảm giá sinh viên', 'vé sinh viên','sinh viên'],
                'answer' => 'Nhà xe có chính sách giảm giá cho sinh viên, vui lòng xuất trình thẻ sinh viên khi đặt vé.'
            ],
            [
                'keywords' => ['đặt vé nhóm', 'đoàn', 'nhiều người', 'đặt nhiều vé','đoàn', 'đoàn vé'],
                'answer' => 'Đặt vé nhóm từ 5 người trở lên sẽ được ưu đãi đặc biệt, vui lòng liên hệ hotline để nhận báo giá.'
            ],
            [
                'keywords' => ['xe trung chuyển', 'trung chuyển miễn phí', 'xe đưa đón'],
                'answer' => 'Nhà xe có xe trung chuyển miễn phí trong nội thành Đà Nẵng và các điểm lớn, vui lòng báo trước khi đặt vé.'
            ],
            [
                'keywords' => ['người già', 'hỗ trợ người già', 'người khuyết tật', 'phụ nữ mang thai'],
                'answer' => 'Nhà xe ưu tiên hỗ trợ người già, người khuyết tật, phụ nữ mang thai. Vui lòng báo trước để được phục vụ tốt nhất.'
            ],
            [
                'keywords' => ['trẻ em đi một mình', 'bé đi một mình', 'gửi trẻ em'],
                'answer' => 'Trẻ em dưới 12 tuổi không được đi xe một mình. Nếu cần gửi trẻ, phải có người lớn đi kèm.'
            ],
            [
                'keywords' => ['điều kiện thời tiết', 'mưa bão', 'hoãn chuyến', 'dời chuyến'],
                'answer' => 'Trong trường hợp thời tiết xấu, nhà xe sẽ chủ động liên hệ khách để đổi/hoãn chuyến và hỗ trợ hoàn tiền nếu cần.'
            ],
            [
                'keywords' => ['bảo mật', 'an ninh', 'thông tin cá nhân'],
                'answer' => 'Nhà xe cam kết bảo mật thông tin cá nhân và an ninh cho khách hàng.'
            ],
            [
                'keywords' => ['đặt vé online', 'website', 'app', 'ứng dụng'],
                'answer' => 'Bạn có thể đặt vé online qua website hoặc ứng dụng, thanh toán linh hoạt và nhận vé điện tử nhanh chóng.'
            ],
            [
                'keywords' => ['hủy vé', 'bỏ vé', 'không đi'],
                'answer' => 'Bạn có thể hủy vé trước giờ xuất bến tối thiểu 2 tiếng để được hoàn tiền theo chính sách.'
            ],
            [
                'keywords' => ['chuyển tuyến', 'đi tuyến khác', 'đổi tuyến'],
                'answer' => 'Bạn có thể chuyển tuyến nếu còn chỗ, vui lòng liên hệ hotline để được hỗ trợ.'
            ],
            [
                'keywords' => ['đặt vé tết', 'vé tết', 'lịch tết', 'giá vé tết'],
                'answer' => 'Nhà xe mở bán vé Tết sớm, giá vé có thể thay đổi theo từng thời điểm. Vui lòng liên hệ hotline để đặt vé Tết.'
            ],
            [
                'keywords' => ['đặt vé khứ hồi', 'vé khứ hồi', 'giảm giá khứ hồi'],
                'answer' => 'Đặt vé khứ hồi sẽ được giảm giá, vui lòng báo trước khi đặt để nhận ưu đãi.'
            ],
            [
                'keywords' => ['đặt vé qua điện thoại', 'gọi đặt vé', 'đặt vé qua hotline'],
                'answer' => 'Bạn có thể đặt vé qua hotline 0905.3333.33, nhân viên sẽ hỗ trợ bạn nhanh chóng.'
            ],
            [
                'keywords' => ['đặt vé cho người khác', 'mua vé hộ', 'đặt hộ vé'],
                'answer' => 'Bạn có thể đặt vé cho người thân, chỉ cần cung cấp thông tin người đi khi đặt vé.'
            ],
            [
                'keywords' => ['chính sách đổi trả', 'đổi trả vé', 'đổi vé', 'trả vé'],
                'answer' => 'Chính sách đổi trả vé linh hoạt, hoàn tiền theo quy định. Vui lòng liên hệ để biết chi tiết.'
            ],
            [
                'keywords' => ['giờ làm việc', 'thời gian làm việc', 'mấy giờ mở cửa'],
                'answer' => 'Văn phòng làm việc từ 7h00 đến 21h00 hàng ngày, hotline hoạt động 24/7.'
            ],
            [
                'keywords' => ['đặt vé quốc tế', 'đi nước ngoài', 'tuyến quốc tế'],
                'answer' => 'Hiện tại nhà xe chỉ phục vụ các tuyến nội địa Việt Nam.'
            ],
            [
                'keywords' => ['đặt vé xe máy', 'gửi xe máy', 'vận chuyển xe máy'],
                'answer' => 'Bạn có thể gửi xe máy cùng chuyến, vui lòng báo trước để được sắp xếp chỗ.'
            ],
            [
                'keywords' => ['đặt vé xe khách', 'xe khách', 'xe bus'],
                'answer' => 'Nhà xe Phương Thanh chuyên xe khách giường nằm chất lượng cao, đặt vé dễ dàng qua website hoặc hotline.'
            ],
            // Có thể bổ sung thêm nữa nếu muốn!
        ];

        foreach ($faq as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (trim($query) === mb_strtolower(trim($keyword))) {
                    Log::info('Chatbot FAQ matched (exact)', ['query' => $query, 'keyword' => $keyword, 'answer' => $item['answer']]);
                    return [
                        'success' => true,
                        'data' => [
                            'message' => str_replace(["\\r\\n", "\\r", "\\n", "\r\n", "\r", "\n"], "<br>", $item['answer'])
                        ]
                    ];
                }
            }
        }

        // Nếu không khớp từ khóa nào
        try {
            // Gửi prompt lên DistributeAI Sync API
            $apiKey = $this->apiKey;
            $syncUrl = 'https://api.distribute.ai/v1/chat/completions';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($syncUrl, [
                'model' => 'Llama-3.1 8B',
                'messages' => [
                    ['role' => 'user', 'content' => $query]
                ]
            ]);
            $data = $response->json();
            if (isset($data['choices'][0]['message']['content'])) {
                $result = $data['choices'][0]['message']['content'];
                Log::info('Chatbot AI response', ['query' => $query, 'ai_response' => $result]);
                return [
                    'success' => true,
                    'data' => [
                        'message' => nl2br($result)
                    ]
                ];
            } else {
                Log::error('DistributeAI raw response', ['raw' => $response->body(), 'json' => $data]);
                throw new \Exception('Không lấy được kết quả từ DistributeAI');
            }
        } catch (\Exception $e) {
            Log::error('DistributeAI Chatbot error', ['error' => $e->getMessage()]);
            return [
                'success' => true,
                'data' => [
                    'message' => 'Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn có thể hỏi về giá vé, lịch trình, tiện nghi, khuyến mãi, gửi hàng... hoặc gọi hotline 0905.999999 để được hỗ trợ!'
                ]
            ];
        }
    }

    /**
     * Lấy lịch sử chat của một phiên
     *
     * @param string $sessionId
     * @return array
     */
    public function getChatHistory(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withoutVerifying()
            ->get($this->baseUrl . '/history/' . $sessionId);

            if (!$response->successful()) {
                throw new \Exception('Failed to get chat history: ' . $response->body());
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Failed to get chat history', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy lịch sử chat',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa lịch sử chat của một phiên
     *
     * @param string $sessionId
     * @return array
     */
    public function clearChatHistory(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withoutVerifying()
            ->delete($this->baseUrl . '/history/' . $sessionId);

            if (!$response->successful()) {
                throw new \Exception('Failed to clear chat history: ' . $response->body());
            }

            return [
                'success' => true,
                'message' => 'Đã xóa lịch sử chat'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to clear chat history', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể xóa lịch sử chat',
                'error' => $e->getMessage()
            ];
        }
    }
}
