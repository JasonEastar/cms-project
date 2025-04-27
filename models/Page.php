<?php
// models/Page.php

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/Language.php';

class Page
{
    private $db;
    private $table = 'pages';
    private $translationTable = 'page_translations';
    private $pageSectionTable = 'page_sections';
    private $languageModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->languageModel = new Language();
    }

    // Tìm page theo ID với thông tin ngôn ngữ
    public function findById($id, $languageCode = null)
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $query = "SELECT p.*, t.title, t.meta_title, t.meta_description, t.meta_keywords,
                        l.code as language_code
                  FROM {$this->table} p
                  LEFT JOIN {$this->translationTable} t ON p.id = t.page_id
                  LEFT JOIN languages l ON t.language_id = l.id
                  WHERE p.id = ? AND l.code = ?";

        return $this->db->fetchOne($query, [$id, $languageCode]);
    }

    // Lấy tất cả pages theo ngôn ngữ
    public function getAllByLang($languageCode = null, $filters = [])
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $params = [$languageCode];
        $conditions = ["l.code = ?"];

        if (isset($filters['is_active'])) {
            $conditions[] = "p.is_active = ?";
            $params[] = $filters['is_active'];
        }

        $where = implode(' AND ', $conditions);

        $query = "SELECT p.*, t.title, t.meta_title, t.meta_description, t.meta_keywords,
                        l.code as language_code
                  FROM {$this->table} p
                  LEFT JOIN {$this->translationTable} t ON p.id = t.page_id
                  LEFT JOIN languages l ON t.language_id = l.id
                  WHERE {$where}
                  ORDER BY p.id DESC";

        return $this->db->fetchAll($query, $params);
    }

    // Tạo page mới
    public function create($data)
    {
        $this->db->beginTransaction();

        try {
            $pageData = [
                'template' => $data['template'] ?? 'default',
                'is_active' => $data['is_active'] ?? 1,
                'is_home' => $data['is_home'] ?? 0
            ];

            $pageId = $this->db->insert($this->table, $pageData);

            // Thêm các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }

                    // Tạo slug từ title nếu không được cung cấp
                    $slug = isset($translation['slug']) && !empty($translation['slug'])
                        ? $translation['slug']
                        : $this->generateSlug($translation['title'], $translation['language_code']);

                    $translationData = [
                        'page_id' => $pageId,
                        'language_id' => $language['id'],
                        'title' => $translation['title'],
                        'slug' => $slug,
                        'meta_title' => $translation['meta_title'] ?? null,
                        'meta_description' => $translation['meta_description'] ?? null,
                        'meta_keywords' => $translation['meta_keywords'] ?? null
                    ];

                    $this->db->insert($this->translationTable, $translationData);
                }
            }

            $this->db->commit();
            return $this->findById($pageId);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Cập nhật page
    public function update($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Cập nhật thông tin page
            $pageData = [];
            if (isset($data['template'])) $pageData['template'] = $data['template'];
            if (isset($data['is_active'])) $pageData['is_active'] = $data['is_active'];
            if (isset($data['is_home'])) $pageData['is_home'] = $data['is_home'];

            if (!empty($pageData)) {
                $this->db->update($this->table, $pageData, 'id = ?', [$id]);
            }

            // Cập nhật các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }

                    // Tạo slug từ title nếu không được cung cấp
                    $slug = isset($translation['slug']) && !empty($translation['slug'])
                        ? $translation['slug']
                        : $this->generateSlug($translation['title'], $translation['language_code'], $id);

                    // Kiểm tra xem bản dịch đã tồn tại chưa
                    $existingTranslation = $this->db->fetchOne(
                        "SELECT id FROM {$this->translationTable} 
                          WHERE page_id = ? AND language_id = ?",
                        [$id, $language['id']]
                    );

                    $translationData = [
                        'title' => $translation['title'],
                        'slug' => $slug,
                        'meta_title' => $translation['meta_title'] ?? null,
                        'meta_description' => $translation['meta_description'] ?? null,
                        'meta_keywords' => $translation['meta_keywords'] ?? null
                    ];

                    if ($existingTranslation) {
                        // Cập nhật
                        $this->db->update(
                            $this->translationTable,
                            $translationData,
                            'page_id = ? AND language_id = ?',
                            [$id, $language['id']]
                        );
                    } else {
                        // Thêm mới
                        $translationData['page_id'] = $id;
                        $translationData['language_id'] = $language['id'];
                        $this->db->insert($this->translationTable, $translationData);
                    }
                }
            }

            $this->db->commit();
            return $this->findById($id);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Tạo slug từ title
    private function generateSlug($title, $languageCode, $excludeId = null)
    {
        // Chuyển về chữ thường
        $slug = strtolower($title);

        // Loại bỏ dấu tiếng Việt nếu là tiếng Việt
        if ($languageCode === 'vi') {
            $slug = $this->removeVietnameseTones($slug);
        }

        // Thay thế các ký tự không phải chữ cái hoặc số bằng dấu gạch ngang
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Loại bỏ dấu gạch ngang ở đầu và cuối
        $slug = trim($slug, '-');

        // Lấy language_id
        $language = $this->languageModel->findByCode($languageCode);
        $languageId = $language['id'];

        // Kiểm tra slug đã tồn tại chưa
        $params = [$slug, $languageId];
        $excludeCondition = "";

        if ($excludeId) {
            $excludeCondition = " AND page_id != ?";
            $params[] = $excludeId;
        }

        $existingPage = $this->db->fetchOne(
            "SELECT id FROM {$this->translationTable} 
              WHERE slug = ? AND language_id = ?" . $excludeCondition,
            $params
        );

        if ($existingPage) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                $params[0] = $newSlug;
                $existingPage = $this->db->fetchOne(
                    "SELECT id FROM {$this->translationTable} 
                      WHERE slug = ? AND language_id = ?" . $excludeCondition,
                    $params
                );
                $i++;
            } while ($existingPage);

            $slug = $newSlug;
        }

        return $slug;
    }
    // Xóa page
    public function delete($id)
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    // Loại bỏ dấu tiếng Việt
    private function removeVietnameseTones($str)
    {
        $patterns = [
            '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/' => 'a',
            '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/' => 'e',
            '/ì|í|ị|ỉ|ĩ/' => 'i',
            '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/' => 'o',
            '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/' => 'u',
            '/ỳ|ý|ỵ|ỷ|ỹ/' => 'y',
            '/đ/' => 'd',
            '/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/' => 'A',
            '/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/' => 'E',
            '/Ì|Í|Ị|Ỉ|Ĩ/' => 'I',
            '/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/' => 'O',
            '/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/' => 'U',
            '/Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'Y',
            '/Đ/' => 'D'
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $str);
    }

    // Thêm section vào page
    public function addSection($pageId, $sectionId, $sortOrder = 0, $isActive = 1)
    {
        try {
            $data = [
                'page_id' => $pageId,
                'section_id' => $sectionId,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ];

            return $this->db->insert($this->pageSectionTable, $data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Xóa section khỏi page
    public function removeSection($pageId, $sectionId)
    {
        return $this->db->delete($this->pageSectionTable, 'page_id = ? AND section_id = ?', [$pageId, $sectionId]);
    }

    // Lấy danh sách sections của một page
    public function getSections($pageId, $languageCode = null)
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $query = "SELECT s.*, st.title, st.description, st.module_name,
                        ps.sort_order as page_sort_order, ps.is_active as page_section_active
                  FROM page_sections ps
                  JOIN sections s ON ps.section_id = s.id
                  LEFT JOIN section_translations st ON s.id = st.section_id
                  LEFT JOIN languages l ON st.language_id = l.id
                  WHERE ps.page_id = ? AND l.code = ?
                  ORDER BY ps.sort_order ASC";

        return $this->db->fetchAll($query, [$pageId, $languageCode]);
    }

    // Cập nhật thứ tự sections trong page
    public function updateSectionOrder($pageId, $sectionId, $sortOrder)
    {
        return $this->db->update(
            $this->pageSectionTable,
            ['sort_order' => $sortOrder],
            'page_id = ? AND section_id = ?',
            [$pageId, $sectionId]
        );
    }

    // Lấy tất cả translations của một page
    public function getAllTranslations($id)
    {
        $query = "SELECT t.*, l.code as language_code, l.name as language_name
                  FROM {$this->translationTable} t
                  JOIN languages l ON t.language_id = l.id
                  WHERE t.page_id = ?";

        return $this->db->fetchAll($query, [$id]);
    }
    // Xóa tất cả sections của một page
    public function clearSections($pageId)
    {
        return $this->db->delete($this->pageSectionTable, 'page_id = ?', [$pageId]);
    }
}
