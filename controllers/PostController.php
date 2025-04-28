<?php
// controllers/PostController.php
// Controller xử lý các API liên quan đến bài viết với đa ngôn ngữ

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Language.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PostController
{
    private $postModel;
    private $languageModel;
    private $categoryModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->languageModel = new LanguageModel();
        $this->categoryModel = new Category();
    }

    // Lấy tất cả bài viết theo ngôn ngữ
    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy tham số từ query string
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
        $is_active = isset($_GET['is_active']) ? $_GET['is_active'] : null;
        $is_featured = isset($_GET['is_featured']) ? $_GET['is_featured'] : null;

        $filters = [];
        if ($category_id !== null) $filters['category_id'] = $category_id;
        if ($is_active !== null) $filters['is_active'] = $is_active;
        if ($is_featured !== null) $filters['is_featured'] = $is_featured;

        // Lấy danh sách bài viết
        $posts = $this->postModel->getAllByLang($lang, $filters);

        return Response::success([
            'posts' => $posts
        ], 'Lấy danh sách bài viết thành công');
    }

    // Lấy chi tiết bài viết theo ID
    public function getById($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy tham số ngôn ngữ
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;

        // Lấy thông tin bài viết
        $post = $this->postModel->findById($id, $lang);

        if (!$post) {
            return Response::notFound('Không tìm thấy bài viết');
        }

        // Lấy tất cả bản dịch của bài viết
        $translations = $this->postModel->getAllTranslations($id);
        $post['translations'] = $translations;

        // Tăng lượt xem
        $this->postModel->incrementViews($id);

        return Response::success([
            'post' => $post
        ], 'Lấy thông tin bài viết thành công');
    }

    // Lấy chi tiết bài viết theo slug và ngôn ngữ
    public function getBySlug()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy tham số từ query string
        $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;

        if (empty($slug)) {
            return Response::validationError(['slug' => 'Slug là bắt buộc']);
        }

        // Lấy thông tin bài viết
        $post = $this->postModel->findBySlug($slug, $lang);

        if (!$post) {
            return Response::notFound('Không tìm thấy bài viết');
        }

        // Tăng lượt xem
        $this->postModel->incrementViews($post['id']);

        return Response::success([
            'post' => $post
        ], 'Lấy thông tin bài viết thành công');
    }

    // Tạo bài viết mới với đa ngôn ngữ
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Xác thực người dùng
        AuthMiddleware::authenticate();

        // Lấy dữ liệu từ FormData
        $data = [];
        $data['code'] = isset($_POST['code']) ? $_POST['code'] : '';

        // Xử lý category_id: chuyển empty string thành null
        if (isset($_POST['category_id']) && $_POST['category_id'] !== '') {
            $data['category_id'] = $_POST['category_id'];
        } else {
            $data['category_id'] = null;
        }

        $data['is_active'] = isset($_POST['is_active']) ? $_POST['is_active'] : 1;
        $data['is_featured'] = isset($_POST['is_featured']) ? $_POST['is_featured'] : 0;

        // Xử lý published_at: chuyển empty string thành null
        if (isset($_POST['published_at']) && $_POST['published_at'] !== '') {
            $data['published_at'] = $_POST['published_at'];
        } else {
            $data['published_at'] = null;
        }

        // Xử lý file upload nếu có
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/posts/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetPath)) {
                $data['thumbnail'] = '/uploads/posts/' . $fileName;
            }
        }

        // Parse translations từ JSON string
        if (isset($_POST['translations'])) {
            $data['translations'] = json_decode($_POST['translations'], true);
        }

        // Kiểm tra dữ liệu đầu vào - chỉ cần ít nhất một bản dịch
        if (!isset($data['translations']) || empty($data['translations'])) {
            return Response::validationError([
                'translations' => 'Cần ít nhất một bản dịch'
            ]);
        }

        // Kiểm tra các bản dịch có đầy đủ thông tin
        $validTranslations = [];
        foreach ($data['translations'] as $translation) {
            if (isset($translation['title']) && !empty(trim($translation['title']))) {
                $validTranslations[] = $translation;
            }
        }

        if (empty($validTranslations)) {
            return Response::validationError([
                'translations' => 'Cần ít nhất một bản dịch có tiêu đề'
            ]);
        }

        $data['translations'] = $validTranslations;

        // Tạo bài viết mới
        try {
            $post = $this->postModel->create($data);

            // Lấy tất cả bản dịch của bài viết mới
            $translations = $this->postModel->getAllTranslations($post['id']);
            $post['translations'] = $translations;

            return Response::success([
                'post' => $post
            ], 'Tạo bài viết thành công', 201);
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Cập nhật bài viết
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Xác thực người dùng
        AuthMiddleware::authenticate();

        // Kiểm tra xem bài viết có tồn tại không
        $existingPost = $this->postModel->findById($id);

        if (!$existingPost) {
            return Response::notFound('Không tìm thấy bài viết');
        }

        // Parse data từ FormData hoặc JSON
        $data = [];
        if (!empty($_POST)) {
            // Dữ liệu từ FormData
            if (isset($_POST['code'])) $data['code'] = $_POST['code'];
            
            // Xử lý category_id: chuyển empty string thành null
            if (isset($_POST['category_id'])) {
                $data['category_id'] = $_POST['category_id'] !== '' ? $_POST['category_id'] : null;
            }
            
            if (isset($_POST['is_active'])) $data['is_active'] = $_POST['is_active'];
            if (isset($_POST['is_featured'])) $data['is_featured'] = $_POST['is_featured'];
            
            // Xử lý published_at: chuyển empty string thành null
            if (isset($_POST['published_at'])) {
                $data['published_at'] = $_POST['published_at'] !== '' ? $_POST['published_at'] : null;
            }

            // Xử lý file upload nếu có
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/posts/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetPath)) {
                    $data['thumbnail'] = '/uploads/posts/' . $fileName;
                }
            }

            // Parse translations từ JSON string
            if (isset($_POST['translations'])) {
                $data['translations'] = json_decode($_POST['translations'], true);
            }
        } else {
            // Dữ liệu từ JSON raw
            $data = json_decode(file_get_contents('php://input'), true);
        }

        // Nếu có dữ liệu về translations, kiểm tra tính hợp lệ
        if (isset($data['translations'])) {
            $validTranslations = [];
            foreach ($data['translations'] as $translation) {
                // Chỉ kiểm tra và lưu những bản dịch có tiêu đề
                if (isset($translation['title']) && !empty(trim($translation['title']))) {
                    $validTranslations[] = $translation;
                }
            }

            if (!empty($validTranslations)) {
                $data['translations'] = $validTranslations;
            } else {
                unset($data['translations']); // Không cập nhật translations nếu không có bản dịch hợp lệ
            }
        }

        // Cập nhật bài viết
        try {
            $post = $this->postModel->update($id, $data);

            // Lấy tất cả bản dịch của bài viết
            $translations = $this->postModel->getAllTranslations($id);
            $post['translations'] = $translations;

            return Response::success([
                'post' => $post
            ], 'Cập nhật bài viết thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Xóa bài viết
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Xác thực người dùng
        AuthMiddleware::authenticate();

        // Kiểm tra xem bài viết có tồn tại không
        $existingPost = $this->postModel->findById($id);

        if (!$existingPost) {
            return Response::notFound('Không tìm thấy bài viết');
        }

        // Xóa bài viết
        try {
            $this->postModel->delete($id);
            return Response::success([], 'Xóa bài viết thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Lấy danh sách ngôn ngữ
    public function getLanguages()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy danh sách ngôn ngữ hoạt động
        $languages = $this->languageModel->getActive();

        return Response::success([
            'languages' => $languages
        ], 'Lấy danh sách ngôn ngữ thành công');
    }

    // Lấy danh sách danh mục
    public function getCategories()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy tham số ngôn ngữ
        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;

        // Lấy danh sách danh mục
        $categories = $this->categoryModel->getAllByLang($lang);

        return Response::success([
            'categories' => $categories
        ], 'Lấy danh sách danh mục thành công');
    }
}