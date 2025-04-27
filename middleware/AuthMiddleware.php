<?php
// middleware/AuthMiddleware.php
// Middleware xác thực JWT

require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    // Phương thức xác thực token
    public static function authenticate() {
        // Lấy header Authorization
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        // Kiểm tra xem header có bắt đầu bằng 'Bearer ' không
        if (!preg_match('/^Bearer\s+(.*)$/', $authHeader, $matches)) {
            Response::unauthorized('Token không được cung cấp hoặc không đúng định dạng');
        }
        
        $token = $matches[1];
        
        // Xác thực token
        $payload = JWT::verify($token);
        if (!$payload) {
            Response::unauthorized('Token không hợp lệ hoặc đã hết hạn');
        }
        
        // Kiểm tra user_id có tồn tại trong payload không
        if (!isset($payload['user_id'])) {
            Response::unauthorized('Token không hợp lệ');
        }
        
        // Kiểm tra user có tồn tại trong hệ thống không
        $userModel = new User();
        $user = $userModel->findById($payload['user_id']);
        
        if (!$user) {
            Response::unauthorized('Người dùng không tồn tại');
        }
        
        // Lưu thông tin người dùng vào REQUEST để sử dụng trong controller
        $_REQUEST['user'] = $user;
        
        // Cho phép tiếp tục xử lý request
        return true;
    }
}