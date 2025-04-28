<?php
// controllers/SectionController.php
// Controller xử lý các API liên quan đến sections và section items với đa ngôn ngữ

require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/SectionItem.php';
require_once __DIR__ . '/../models/Language.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SectionController
{
    private $sectionModel;
    private $sectionItemModel;
    private $languageModel;

    public function __construct()
    {
        $this->sectionModel = new Section();
        $this->sectionItemModel = new SectionItem();
        $this->languageModel = new LanguageModel();
    }

   // Cập nhật phương thức getById()
public function getById($id)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return Response::error('Phương thức không được hỗ trợ', 405);
    }

    // Lấy tham số ngôn ngữ
    $lang = isset($_GET['lang']) ? $_GET['lang'] : null;

    // Lấy thông tin section theo ngôn ngữ chỉ định
    $section = $this->sectionModel->findById($id, $lang);

    if (!$section) {
        return Response::notFound('Không tìm thấy section');
    }

    // Lấy tất cả bản dịch của section
    $translations = $this->sectionModel->getAllTranslations($id);
    $section['translations'] = $translations;

    // Lấy tất cả items của section
    $items = $this->sectionItemModel->getAllBySectionId($id);
    
    // Lấy tất cả bản dịch cho từng item
    foreach ($items as &$item) {
        $itemTranslations = $this->sectionItemModel->getAllTranslations($item['id']);
        $item['translations'] = $itemTranslations;
        
        // Lấy thông tin dịch của item theo ngôn ngữ chỉ định
        if ($lang) {
            $itemTranslation = $this->sectionItemModel->findById($item['id'], $lang);
            if ($itemTranslation) {
                $item['title'] = $itemTranslation['title'];
                $item['description'] = $itemTranslation['description'];
                $item['language_code'] = $itemTranslation['language_code'];
            }
        }
    }
    
    $section['items'] = $items;

    return Response::success([
        'section' => $section
    ], 'Lấy thông tin section thành công');
}

// Cập nhật phương thức getAll()
public function getAll()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return Response::error('Phương thức không được hỗ trợ', 405);
    }

    // Lấy tham số từ query string
    $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
    $is_active = isset($_GET['is_active']) ? $_GET['is_active'] : null;
    $show_on_mobile = isset($_GET['show_on_mobile']) ? $_GET['show_on_mobile'] : null;

    $filters = [];
    if ($is_active !== null) $filters['is_active'] = $is_active;
    if ($show_on_mobile !== null) $filters['show_on_mobile'] = $show_on_mobile;

    // Lấy danh sách sections
    $sections = $this->sectionModel->getAllByLang($lang, $filters);

    return Response::success([
        'sections' => $sections
    ], 'Lấy danh sách sections thành công');
}
// Tạo section mới với items
public function create()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return Response::error('Method not supported', 405);
    }

    AuthMiddleware::authenticate();

    // Get data from FormData
    $data = [];
    $data['code'] = $_POST['code'] ?? '';
    $data['template'] = $_POST['template'] ?? 'default';
    $data['is_active'] = $_POST['is_active'] ?? 1;
    $data['show_on_mobile'] = $_POST['show_on_mobile'] ?? 1;
    $data['sort_order'] = $_POST['sort_order'] ?? 0;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/sections/';
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return Response::error('Cannot create upload directory: ' . $uploadDir);
            }
        }

        if (!is_writable($uploadDir)) {
            chmod($uploadDir, 0777);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $data['image'] = '/uploads/sections/' . $fileName;
        } else {
            return Response::error('Failed to upload image: ' . error_get_last()['message']);
        }
    }

    // Parse translations
    if (isset($_POST['translations'])) {
        $data['translations'] = json_decode($_POST['translations'], true);
    }

    // Parse items
    $items = [];
    if (isset($_POST['items'])) {
        $items = json_decode($_POST['items'], true);
    }

    // Validate data
    if (!isset($data['translations']) || empty($data['translations'])) {
        return Response::error('At least one translation is required');
    }

    try {
        $section = $this->sectionModel->create($data);
        $sectionId = $section['id'];

        // Create items
        foreach ($items as $index => $itemData) {
            $itemData['section_id'] = $sectionId;
            $itemData['sort_order'] = $index;

            // Handle item image upload
            if ($itemData['type'] === 'image' && isset($_FILES["item_image_$index"])) {
                $itemFile = $_FILES["item_image_$index"];
                if ($itemFile['error'] === UPLOAD_ERR_OK) {
                    $itemUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/section-items/';
                    
                    if (!file_exists($itemUploadDir)) {
                        if (!mkdir($itemUploadDir, 0777, true)) {
                            return Response::error('Cannot create upload directory: ' . $itemUploadDir);
                        }
                    }

                    if (!is_writable($itemUploadDir)) {
                        chmod($itemUploadDir, 0777);
                    }

                    $itemFileName = time() . '_' . $index . '_' . basename($itemFile['name']);
                    $itemFileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $itemFileName);
                    $itemTargetPath = $itemUploadDir . $itemFileName;

                    if (move_uploaded_file($itemFile['tmp_name'], $itemTargetPath)) {
                        $itemData['image'] = '/uploads/section-items/' . $itemFileName;
                    } else {
                        return Response::error('Failed to upload item image: ' . error_get_last()['message']);
                    }
                }
            }

            $this->sectionItemModel->create($itemData);
        }

        return Response::success([
            'section' => $section
        ], 'Section created successfully', 201);
    } catch (Exception $e) {
        return Response::error($e->getMessage());
    }
}

// Update section
public function update($id)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return Response::error('Method not supported', 405);
    }

    AuthMiddleware::authenticate();

    // Check if section exists
    $existingSection = $this->sectionModel->findById($id);
    if (!$existingSection) {
        return Response::notFound('Section not found');
    }

    // Get data from FormData
    $data = [];
    if (isset($_POST['code'])) $data['code'] = $_POST['code'];
    if (isset($_POST['template'])) $data['template'] = $_POST['template'];
    if (isset($_POST['is_active'])) $data['is_active'] = $_POST['is_active'];
    if (isset($_POST['show_on_mobile'])) $data['show_on_mobile'] = $_POST['show_on_mobile'];
    if (isset($_POST['sort_order'])) $data['sort_order'] = $_POST['sort_order'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/sections/';
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return Response::error('Cannot create upload directory: ' . $uploadDir);
            }
        }

        if (!is_writable($uploadDir)) {
            chmod($uploadDir, 0777);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $data['image'] = '/uploads/sections/' . $fileName;
        } else {
            return Response::error('Failed to upload image: ' . error_get_last()['message']);
        }
    }

    // Parse translations
    if (isset($_POST['translations'])) {
        $data['translations'] = json_decode($_POST['translations'], true);
    }

    // Parse items
    $items = [];
    if (isset($_POST['items'])) {
        $items = json_decode($_POST['items'], true);
    }

    try {
        $section = $this->sectionModel->update($id, $data);

        // Handle items
        if (!empty($items)) {
            // Get current items
            $currentItems = $this->sectionItemModel->getAllBySectionId($id);
            $currentItemIds = array_column($currentItems, 'id');
            $requestItemIds = array_filter(array_column($items, 'id'));

            // Delete removed items
            $itemsToDelete = array_diff($currentItemIds, $requestItemIds);
            foreach ($itemsToDelete as $itemId) {
                $this->sectionItemModel->delete($itemId);
            }

            // Update or create items
            foreach ($items as $index => $itemData) {
                $itemData['section_id'] = $id;
                $itemData['sort_order'] = $index;

                // Handle item image upload
                if ($itemData['type'] === 'image' && isset($_FILES["item_image_$index"])) {
                    $itemFile = $_FILES["item_image_$index"];
                    if ($itemFile['error'] === UPLOAD_ERR_OK) {
                        $itemUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/section-items/';
                        
                        if (!file_exists($itemUploadDir)) {
                            if (!mkdir($itemUploadDir, 0777, true)) {
                                return Response::error('Cannot create upload directory: ' . $itemUploadDir);
                            }
                        }

                        if (!is_writable($itemUploadDir)) {
                            chmod($itemUploadDir, 0777);
                        }

                        $itemFileName = time() . '_' . $index . '_' . basename($itemFile['name']);
                        $itemFileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $itemFileName);
                        $itemTargetPath = $itemUploadDir . $itemFileName;

                        if (move_uploaded_file($itemFile['tmp_name'], $itemTargetPath)) {
                            $itemData['image'] = '/uploads/section-items/' . $itemFileName;
                        } else {
                            return Response::error('Failed to upload item image: ' . error_get_last()['message']);
                        }
                    }
                }

                if (!empty($itemData['id']) && in_array($itemData['id'], $currentItemIds)) {
                    // Update existing item
                    $this->sectionItemModel->update($itemData['id'], $itemData);
                } else {
                    // Create new item
                    $this->sectionItemModel->create($itemData);
                }
            }
        }

        return Response::success([
            'section' => $section
        ], 'Section updated successfully');
    } catch (Exception $e) {
        return Response::error($e->getMessage());
    }
}

    // Xóa section
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Xác thực người dùng
        AuthMiddleware::authenticate();

        // Kiểm tra xem section có tồn tại không
        $existingSection = $this->sectionModel->findById($id);

        if (!$existingSection) {
            return Response::notFound('Không tìm thấy section');
        }

        // Xóa section (items sẽ được xóa tự động do foreign key constraint)
        try {
            $this->sectionModel->delete($id);
            return Response::success([], 'Xóa section thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Cập nhật thứ tự sections
    public function updateSectionOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Xác thực người dùng
        AuthMiddleware::authenticate();

        // Lấy dữ liệu từ request
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['orders']) || !is_array($data['orders'])) {
            return Response::validationError([
                'orders' => 'Dữ liệu thứ tự không hợp lệ'
            ]);
        }

        try {
            foreach ($data['orders'] as $order) {
                if (isset($order['id']) && isset($order['sort_order'])) {
                    $this->sectionModel->updateSortOrder($order['id'], $order['sort_order']);
                }
            }

            return Response::success([], 'Cập nhật thứ tự sections thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Cập nhật thứ tự items
    public function updateItemOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Xác thực người dùng
        AuthMiddleware::authenticate();

        // Lấy dữ liệu từ request
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['orders']) || !is_array($data['orders'])) {
            return Response::validationError([
                'orders' => 'Dữ liệu thứ tự không hợp lệ'
            ]);
        }

        try {
            foreach ($data['orders'] as $order) {
                if (isset($order['id']) && isset($order['sort_order'])) {
                    $this->sectionItemModel->updateSortOrder($order['id'], $order['sort_order']);
                }
            }

            return Response::success([], 'Cập nhật thứ tự items thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
}
