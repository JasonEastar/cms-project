<?php
// controllers/PageController.php

require_once __DIR__ . '/../models/Page.php';
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PageController
{
    private $pageModel;
    private $sectionModel;

    public function __construct()
    {
        $this->pageModel = new Page();
        $this->sectionModel = new Section();
    }

    // Lấy tất cả pages
    public function getAll()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        $pages = $this->pageModel->getAllByLang($lang);

        return Response::success([
            'pages' => $pages
        ], 'Lấy danh sách pages thành công');
    }

    // Lấy chi tiết page theo ID
    public function getById($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        $page = $this->pageModel->findById($id, $lang);

        if (!$page) {
            return Response::notFound('Không tìm thấy page');
        }

        // Lấy tất cả bản dịch của page
        $translations = $this->pageModel->getAllTranslations($id);
        $page['translations'] = $translations;

        // Lấy danh sách sections của page
        $sections = $this->pageModel->getSections($id, $lang);
        $page['sections'] = $sections;

        return Response::success([
            'page' => $page
        ], 'Lấy thông tin page thành công');
    }

    // Tạo page mới
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Method not supported', 405);
        }

        AuthMiddleware::authenticate();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (!isset($data['translations']) || empty($data['translations'])) {
            return Response::error('Cần ít nhất một bản dịch');
        }

        try {
            $page = $this->pageModel->create($data);
            return Response::success([
                'page' => $page
            ], 'Tạo page thành công', 201);
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Cập nhật page
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        AuthMiddleware::authenticate();

        // Kiểm tra page tồn tại
        $existingPage = $this->pageModel->findById($id);
        if (!$existingPage) {
            return Response::notFound('Không tìm thấy page');
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $page = $this->pageModel->update($id, $data);
            return Response::success([
                'page' => $page
            ], 'Cập nhật page thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Xóa page
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        AuthMiddleware::authenticate();

        // Kiểm tra page tồn tại
        $existingPage = $this->pageModel->findById($id);
        if (!$existingPage) {
            return Response::notFound('Không tìm thấy page');
        }

        try {
            $this->pageModel->delete($id);
            return Response::success([], 'Xóa page thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Thêm section vào page
    public function addSection($pageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Method not supported', 405);
        }

        AuthMiddleware::authenticate();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['section_id'])) {
            return Response::error('section_id là bắt buộc');
        }

        try {
            $this->pageModel->addSection(
                $pageId,
                $data['section_id'],
                $data['sort_order'] ?? 0,
                $data['is_active'] ?? 1
            );

            return Response::success([], 'Thêm section vào page thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Xóa section khỏi page
    public function removeSection($pageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        AuthMiddleware::authenticate();

        $sectionId = $_GET['section_id'] ?? null;
        if (!$sectionId) {
            return Response::error('section_id là bắt buộc');
        }

        try {
            $this->pageModel->removeSection($pageId, $sectionId);
            return Response::success([], 'Xóa section khỏi page thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Lấy danh sách sections của page
    public function getSections($pageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        $lang = isset($_GET['lang']) ? $_GET['lang'] : null;
        $sections = $this->pageModel->getSections($pageId, $lang);

        return Response::success([
            'sections' => $sections
        ], 'Lấy danh sách sections thành công');
    }

    // Cập nhật thứ tự sections trong page
    public function updateSectionOrder($pageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        AuthMiddleware::authenticate();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['orders']) || !is_array($data['orders'])) {
            return Response::error('Dữ liệu không hợp lệ');
        }

        try {
            foreach ($data['orders'] as $order) {
                if (isset($order['section_id']) && isset($order['sort_order'])) {
                    $this->pageModel->updateSectionOrder($pageId, $order['section_id'], $order['sort_order']);
                }
            }

            return Response::success([], 'Cập nhật thứ tự sections thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // Xóa tất cả sections của một page
    public function clearSections($pageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        AuthMiddleware::authenticate();

        try {
            $this->pageModel->clearSections($pageId);
            return Response::success([], 'Xóa tất cả sections thành công');
        } catch (Exception $e) {
            return Response::error($e->getMessage());
        }
    }
}
