<?php
// controllers/LanguageController.php
// Controller xử lý các API liên quan đến quản lý ngôn ngữ

require_once __DIR__ . '/../models/Language.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class LanguageController {
    private $languageModel;
    
    public function __construct() {
        $this->languageModel = new Language();
    }
    
    // Phương thức lấy tất cả ngôn ngữ
    public function getAll() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số từ query string
        $active_only = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
        
        // Lấy danh sách ngôn ngữ
        if ($active_only) {
            $languages = $this->languageModel->getActive();
        } else {
            $languages = $this->languageModel->getAll();
        }
        
        return Response::success([
            'languages' => $languages
        ], 'Lấy danh sách ngôn ngữ thành công');
    }
    
    // Phương thức lấy ngôn ngữ mặc định
    public function getDefault() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy ngôn ngữ mặc định
        $language = $this->languageModel->getDefault();
        
        if (!$language) {
            return Response::notFound('Không tìm thấy ngôn ngữ mặc định');
        }
        
        return Response::success([
            'language' => $language
        ], 'Lấy ngôn ngữ mặc định thành công');
    }
    
    // Phương thức lấy chi tiết ngôn ngữ theo ID
    public function getById($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy thông tin ngôn ngữ
        $language = $this->languageModel->findById($id);
        
        if (!$language) {
            return Response::notFound('Không tìm thấy ngôn ngữ');
        }
        
        return Response::success([
            'language' => $language
        ], 'Lấy thông tin ngôn ngữ thành công');
    }
    
    // Phương thức tạo ngôn ngữ mới
    public function create() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate dữ liệu đầu vào
        $errors = [];
        
        if (!isset($data['code']) || empty($data['code'])) {
            $errors['code'] = 'Mã ngôn ngữ là bắt buộc';
        } else if ($this->languageModel->codeExists($data['code'])) {
            $errors['code'] = 'Mã ngôn ngữ đã tồn tại';
        }
        
        if (!isset($data['name']) || empty($data['name'])) {
            $errors['name'] = 'Tên ngôn ngữ là bắt buộc';
        }
        
        // Kiểm tra lỗi
        if (!empty($errors)) {
            return Response::validationError($errors);
        }
        
        // Tạo ngôn ngữ mới
        try {
            $language = $this->languageModel->create($data);
            
            return Response::success([
                'language' => $language
            ], 'Tạo ngôn ngữ thành công', 201);
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
    
    // Phương thức cập nhật ngôn ngữ
    public function update($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Kiểm tra xem ngôn ngữ có tồn tại không
        $existingLanguage = $this->languageModel->findById($id);
        
        if (!$existingLanguage) {
            return Response::notFound('Không tìm thấy ngôn ngữ');
        }
        
        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate dữ liệu đầu vào
        $errors = [];
        
        if (isset($data['code'])) {
            if (empty($data['code'])) {
                $errors['code'] = 'Mã ngôn ngữ là bắt buộc';
            } else if ($this->languageModel->codeExists($data['code'], $id)) {
                $errors['code'] = 'Mã ngôn ngữ đã tồn tại';
            }
        }
        
        if (isset($data['name']) && empty($data['name'])) {
            $errors['name'] = 'Tên ngôn ngữ là bắt buộc';
        }
        
        // Kiểm tra lỗi
        if (!empty($errors)) {
            return Response::validationError($errors);
        }
        
        // Cập nhật ngôn ngữ
        try {
            $language = $this->languageModel->update($id, $data);
            
            return Response::success([
                'language' => $language
            ], 'Cập nhật ngôn ngữ thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
    
    // Phương thức xóa ngôn ngữ
    public function delete($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Kiểm tra xem ngôn ngữ có tồn tại không
        $existingLanguage = $this->languageModel->findById($id);
        
        if (!$existingLanguage) {
            return Response::notFound('Không tìm thấy ngôn ngữ');
        }
        
        // Xóa ngôn ngữ
        try {
            $this->languageModel->delete($id);
            return Response::success([], 'Xóa ngôn ngữ thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
    
    // Phương thức đặt ngôn ngữ mặc định
    public function setDefault($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Kiểm tra xem ngôn ngữ có tồn tại không
        $existingLanguage = $this->languageModel->findById($id);
        
        if (!$existingLanguage) {
            return Response::notFound('Không tìm thấy ngôn ngữ');
        }
        
        // Đặt làm ngôn ngữ mặc định
        try {
            $language = $this->languageModel->setDefault($id);
            
            return Response::success([
                'language' => $language
            ], 'Đặt ngôn ngữ mặc định thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
}