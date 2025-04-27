<?php
// controllers/CategoryController.php
// Controller xử lý các API liên quan đến danh mục với đa ngôn ngữ

require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Language.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class CategoryController {
    private $categoryModel;
    private $languageModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
        $this->languageModel = new Language();
    }
    
    // Phương thức lấy tất cả danh mục theo ngôn ngữ
    public function getAll() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số từ query string
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        $hierarchical = isset($_GET['hierarchical']) && $_GET['hierarchical'] === 'true';
        
        // Lấy danh sách danh mục
        if ($hierarchical) {
            $categories = $this->categoryModel->getHierarchicalCategories($lang);
        } else {
            $categories = $this->categoryModel->getAllByLang($lang);
        }
        
        return Response::success([
            'categories' => $categories
        ], 'Lấy danh sách danh mục thành công');
    }
    
    // Phương thức lấy chi tiết danh mục theo ID
    public function getById($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số ngôn ngữ
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        
        // Lấy thông tin danh mục
        $category = $this->categoryModel->findById($id, $lang);
        
        if (!$category) {
            return Response::notFound('Không tìm thấy danh mục');
        }
        
        // Lấy tất cả bản dịch của danh mục
        $translations = $this->categoryModel->getAllTranslations($id);
        $category['translations'] = $translations;
        
        return Response::success([
            'category' => $category
        ], 'Lấy thông tin danh mục thành công');
    }
    
    // Phương thức lấy chi tiết danh mục theo slug và ngôn ngữ
    public function getBySlug() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy tham số từ query string
        $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        
        if (empty($slug)) {
            return Response::validationError(['slug' => 'Slug là bắt buộc']);
        }
        
        // Lấy thông tin danh mục
        $category = $this->categoryModel->findBySlug($slug, $lang);
        
        if (!$category) {
            return Response::notFound('Không tìm thấy danh mục');
        }
        
        return Response::success([
            'category' => $category
        ], 'Lấy thông tin danh mục thành công');
    }
    
    // Phương thức tạo danh mục mới với đa ngôn ngữ
    public function create() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['translations']) || empty($data['translations'])) {
            return Response::validationError([
                'translations' => 'Cần ít nhất một bản dịch'
            ]);
        }
        
        // Kiểm tra xem có ít nhất một bản dịch với ngôn ngữ mặc định
        $defaultLang = $this->languageModel->getDefault();
        $hasDefaultLang = false;
        
        foreach ($data['translations'] as $translation) {
            if (!isset($translation['language_code']) || !isset($translation['name'])) {
                return Response::validationError([
                    'translation' => 'Mỗi bản dịch cần có language_code và name'
                ]);
            }
            
            if ($translation['language_code'] === $defaultLang['code']) {
                $hasDefaultLang = true;
            }
        }
        
        if (!$hasDefaultLang) {
            return Response::validationError([
                'translations' => 'Cần có bản dịch cho ngôn ngữ mặc định (' . $defaultLang['name'] . ')'
            ]);
        }
        
        // Tạo danh mục mới
        try {
            $category = $this->categoryModel->create($data);
            
            // Lấy tất cả bản dịch của danh mục mới
            $translations = $this->categoryModel->getAllTranslations($category['id']);
            $category['translations'] = $translations;
            
            return Response::success([
                'category' => $category
            ], 'Tạo danh mục thành công', 201);
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
    
    // Phương thức cập nhật danh mục
    public function update($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Kiểm tra xem danh mục có tồn tại không
        $existingCategory = $this->categoryModel->findById($id);
        
        if (!$existingCategory) {
            return Response::notFound('Không tìm thấy danh mục');
        }
        
        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Nếu có dữ liệu về translations, kiểm tra tính hợp lệ
        if (isset($data['translations'])) {
            foreach ($data['translations'] as $translation) {
                if (!isset($translation['language_code']) || !isset($translation['name'])) {
                    return Response::validationError([
                        'translation' => 'Mỗi bản dịch cần có language_code và name'
                    ]);
                }
            }
        }
        
        // Cập nhật danh mục
        try {
            $category = $this->categoryModel->update($id, $data);
            
            // Lấy tất cả bản dịch của danh mục
            $translations = $this->categoryModel->getAllTranslations($id);
            $category['translations'] = $translations;
            
            return Response::success([
                'category' => $category
            ], 'Cập nhật danh mục thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
    
    // Phương thức xóa danh mục
    public function delete($id) {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Xác thực người dùng
        AuthMiddleware::authenticate();
        
        // Kiểm tra xem danh mục có tồn tại không
        $existingCategory = $this->categoryModel->findById($id);
        
        if (!$existingCategory) {
            return Response::notFound('Không tìm thấy danh mục');
        }
        
        // Xóa danh mục
        try {
            $this->categoryModel->delete($id);
            return Response::success([], 'Xóa danh mục thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
    
    // Phương thức lấy danh sách ngôn ngữ
    public function getLanguages() {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }
        
        // Lấy danh sách ngôn ngữ hoạt động
        $languages = $this->languageModel->getActive();
        
        return Response::success([
            'languages' => $languages
        ], 'Lấy danh sách ngôn ngữ thành công');
    }
}