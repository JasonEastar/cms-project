<?php
// models/SectionItem.php
// Model xử lý dữ liệu bảng section_items với đa ngôn ngữ

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/Language.php';

class SectionItem
{
    private $db;
    private $table = 'section_items';
    private $translationTable = 'section_item_translations';
    private $languageModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->languageModel = new Language();
    }

    // Tìm section item theo ID với thông tin ngôn ngữ
    // Cập nhật phương thức findById()
    public function findById($id, $languageCode = null)
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $query = "SELECT si.*, t.title, t.description,
                     l.code as language_code
              FROM {$this->table} si
              LEFT JOIN {$this->translationTable} t ON si.id = t.item_id
              LEFT JOIN languages l ON t.language_id = l.id
              WHERE si.id = ? AND l.code = ?";

        $result = $this->db->fetchOne($query, [$id, $languageCode]);

        // Nếu không tìm thấy với ngôn ngữ được chỉ định, lấy thông tin cơ bản của item
        if (!$result) {
            $query = "SELECT si.* FROM {$this->table} si WHERE si.id = ?";
            $result = $this->db->fetchOne($query, [$id]);

            if ($result) {
                $result['title'] = '';
                $result['description'] = '';
                $result['language_code'] = $languageCode;
            }
        }

        return $result;
    }

    // Cập nhật phương thức getAllBySectionId()
    public function getAllBySectionId($sectionId, $languageCode = null, $filters = [])
    {
        // Nếu không có language code, lấy tất cả items không phân biệt ngôn ngữ
        if (!$languageCode) {
            $query = "SELECT si.* FROM {$this->table} si 
                  WHERE si.section_id = ? 
                  ORDER BY si.sort_order ASC";

            $items = $this->db->fetchAll($query, [$sectionId]);

            // Lấy ngôn ngữ mặc định
            $defaultLang = $this->languageModel->getDefault();
            $defaultLangCode = $defaultLang['code'];

            // Thêm thông tin dịch mặc định cho mỗi item
            foreach ($items as &$item) {
                $translation = $this->findById($item['id'], $defaultLangCode);
                if ($translation) {
                    $item['title'] = $translation['title'];
                    $item['description'] = $translation['description'];
                    $item['language_code'] = $translation['language_code'];
                }
            }

            return $items;
        }

        // Code cho trường hợp có language code
        $params = [$sectionId, $languageCode];
        $conditions = ["si.section_id = ?", "l.code = ?"];

        if (isset($filters['is_active'])) {
            $conditions[] = "si.is_active = ?";
            $params[] = $filters['is_active'];
        }

        $where = implode(' AND ', $conditions);

        $query = "SELECT si.*, t.title, t.description,
                     l.code as language_code
              FROM {$this->table} si
              LEFT JOIN {$this->translationTable} t ON si.id = t.item_id
              LEFT JOIN languages l ON t.language_id = l.id
              WHERE {$where}
              ORDER BY si.sort_order ASC";

        return $this->db->fetchAll($query, $params);
    }

    // Tạo section item mới
    public function create($data)
    {
        $this->db->beginTransaction();

        try {
            // Dữ liệu section item
            $itemData = [
                'section_id' => $data['section_id'],
                'type' => $data['type'] ?? 'text', // text, image, icon
                'image' => $data['image'] ?? null,
                'icon' => $data['icon'] ?? null,
                'link' => $data['link'] ?? null,
                'is_active' => $data['is_active'] ?? 1,
                'sort_order' => $data['sort_order'] ?? 0
            ];

            // Thêm section item vào database
            $itemId = $this->db->insert($this->table, $itemData);

            // Thêm các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }

                    $translationData = [
                        'item_id' => $itemId,
                        'language_id' => $language['id'],
                        'title' => $translation['title'],
                        'description' => $translation['description'] ?? null
                    ];

                    $this->db->insert($this->translationTable, $translationData);
                }
            }

            $this->db->commit();
            return $this->findById($itemId);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Cập nhật section item
    public function update($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Cập nhật thông tin section item
            $itemData = [];
            if (isset($data['section_id'])) $itemData['section_id'] = $data['section_id'];
            if (isset($data['type'])) $itemData['type'] = $data['type'];
            if (isset($data['image'])) $itemData['image'] = $data['image'];
            if (isset($data['icon'])) $itemData['icon'] = $data['icon'];
            if (isset($data['link'])) $itemData['link'] = $data['link'];
            if (isset($data['is_active'])) $itemData['is_active'] = $data['is_active'];
            if (isset($data['sort_order'])) $itemData['sort_order'] = $data['sort_order'];

            if (!empty($itemData)) {
                $this->db->update($this->table, $itemData, 'id = ?', [$id]);
            }

            // Cập nhật các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }

                    // Kiểm tra xem bản dịch đã tồn tại chưa
                    $existingTranslation = $this->db->fetchOne(
                        "SELECT id FROM {$this->translationTable} 
                         WHERE item_id = ? AND language_id = ?",
                        [$id, $language['id']]
                    );

                    $translationData = [
                        'title' => $translation['title'],
                        'description' => $translation['description'] ?? null
                    ];

                    if ($existingTranslation) {
                        // Cập nhật
                        $this->db->update(
                            $this->translationTable,
                            $translationData,
                            'item_id = ? AND language_id = ?',
                            [$id, $language['id']]
                        );
                    } else {
                        // Thêm mới
                        $translationData['item_id'] = $id;
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

    // Xóa section item
    public function delete($id)
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    // Lấy tất cả bản dịch của một section item
    public function getAllTranslations($id)
    {
        $query = "SELECT t.*, l.code as language_code, l.name as language_name
                  FROM {$this->translationTable} t
                  JOIN languages l ON t.language_id = l.id
                  WHERE t.item_id = ?";

        return $this->db->fetchAll($query, [$id]);
    }

    // Cập nhật thứ tự
    public function updateSortOrder($id, $sortOrder)
    {
        return $this->db->update($this->table, ['sort_order' => $sortOrder], 'id = ?', [$id]);
    }

    // Di chuyển item sang section khác
    public function moveToSection($id, $newSectionId)
    {
        return $this->db->update($this->table, ['section_id' => $newSectionId], 'id = ?', [$id]);
    }
}
