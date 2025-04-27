<?php
// controllers/ConfigController.php
// Controller xử lý các API liên quan đến cấu hình website

require_once __DIR__ . '/../models/SiteConfig.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ConfigController {
    private $configModel;
    
    public function __construct() {
        $this->configModel = new SiteConfig();
    }
    
    // Phương thức lấy tất cả cấu hình theo ngôn ngữ
    public function getAll() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số từ query string
        $lang = isset($_GET['lang']) ? $_GET['lang'] : 'vi';
        
        // Lấy tất cả cấu hình
        $configs = $this->configModel->getAllByLang($lang);
        
        return Response::success([
            'configs' => $configs
        ], 'Lấy danh sách cấu hình thành công');
    }
    
    // Phương thức lấy cấu hình theo khóa và ngôn ngữ
    public function get() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số từ query string
        $key = isset($_GET['key']) ? $_GET['key'] : '';
        $lang = isset($_GET['lang']) ? $_GET['lang'] : 'vi';
        
        if (empty($key)) {
            return Response::validationError(['key' => 'Khóa cấu hình là bắt buộc']);
        }
        
        // Lấy cấu hình
        $value = $this->configModel->get($key, $lang);
        
        if ($value === null) {
            return Response::notFound('Không tìm thấy cấu hình');
        }
        
        return Response::success([
            'key' => $key,
            'value' => $value
        ], 'Lấy cấu hình thành công');
    }
    
    // Phương thức lấy nhóm cấu hình theo tiền tố và ngôn ngữ
    public function getGroup() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số từ query string
        $prefix = isset($_GET['prefix']) ? $_GET['prefix'] : '';
        $lang = isset($_GET['lang']) ? $_GET['lang'] : 'vi';
        
        if (empty($prefix)) {
            return Response::validationError(['prefix' => 'Tiền tố là bắt buộc']);
        }
        
        // Lấy nhóm cấu hình
        $configs = $this->configModel->getGroup($prefix, $lang);
        
        return Response::success([
            'prefix' => $prefix,
            'configs' => $configs
        ], 'Lấy nhóm cấu hình thành công');
    }
    
    // Phương thức cập nhật cấu hình
    public function set() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['key']) || !isset($data['value']) || !isset($data['lang'])) {
            return Response::validationError([
                'key' => 'Khóa cấu hình là bắt buộc',
                'value' => 'Giá trị cấu hình là bắt buộc',
                'lang' => 'Ngôn ngữ là bắt buộc'
            ]);
        }
        
        // Cập nhật cấu hình
        $this->configModel->set($data['key'], $data['value'], $data['lang']);
        
        return Response::success([
            'key' => $data['key'],
            'value' => $data['value']
        ], 'Cập nhật cấu hình thành công');
    }
    
    // Phương thức cập nhật nhiều cấu hình cùng lúc
    public function setBulk() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['configs']) || !isset($data['lang'])) {
            return Response::validationError([
                'configs' => 'Danh sách cấu hình là bắt buộc',
                'lang' => 'Ngôn ngữ là bắt buộc'
            ]);
        }
        
        // Cập nhật nhiều cấu hình
        $this->configModel->setBulk($data['configs'], $data['lang']);
        
        return Response::success([
            'configs' => $data['configs']
        ], 'Cập nhật cấu hình thành công');
    }
    
    // Phương thức xóa cấu hình
    public function delete() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Lấy tham số từ query string
        $key = isset($_GET['key']) ? $_GET['key'] : '';
        $lang = isset($_GET['lang']) ? $_GET['lang'] : 'vi';
        
        if (empty($key)) {
            return Response::validationError(['key' => 'Khóa cấu hình là bắt buộc']);
        }
        
        // Xóa cấu hình
        $this->configModel->delete($key, $lang);
        
        return Response::success([], 'Xóa cấu hình thành công');
    }
}