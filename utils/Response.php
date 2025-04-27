<?php
// utils/Response.php
// Lớp xử lý phản hồi API

class Response {
    // Phương thức gửi phản hồi JSON
    public static function json($data, $statusCode = 200) {
        // Thiết lập header
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        
        // Chuyển đổi dữ liệu thành JSON và trả về
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Phương thức trả về thành công
    public static function success($data = [], $message = 'Success', $statusCode = 200) {
        return self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    // Phương thức trả về lỗi
    public static function error($message = 'Error', $statusCode = 400, $errors = []) {
        return self::json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
    
    // Phương thức trả về lỗi không tìm thấy
    public static function notFound($message = 'Resource not found') {
        return self::error($message, 404);
    }
    
    // Phương thức trả về lỗi không được phép
    public static function unauthorized($message = 'Unauthorized') {
        return self::error($message, 401);
    }
    
    // Phương thức trả về lỗi không đủ quyền
    public static function forbidden($message = 'Forbidden') {
        return self::error($message, 403);
    }
    
    // Phương thức trả về lỗi server
    public static function serverError($message = 'Internal Server Error') {
        return self::error($message, 500);
    }
    
    // Phương thức trả về lỗi dữ liệu không hợp lệ
    public static function validationError($errors = [], $message = 'Validation Failed') {
        return self::error($message, 422, $errors);
    }
}