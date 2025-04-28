<?php
// models/Section.php
// Model xử lý dữ liệu bảng sections với đa ngôn ngữ

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/Language.php';

class Section
{
    private $db;
    private $table = 'sections';
    private $translationTable = 'section_translations';
    private $languageModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->languageModel = new LanguageModel();
    }

    // Tìm section theo ID với thông tin ngôn ngữ
    // Cập nhật phương thức findById()
    public function findById($id, $languageCode = null)
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $query = "SELECT s.*, t.title, t.description, t.module_name,
                     l.code as language_code
              FROM {$this->table} s
              LEFT JOIN {$this->translationTable} t ON s.id = t.section_id
              LEFT JOIN languages l ON t.language_id = l.id
              WHERE s.id = ? AND l.code = ?";

        $result = $this->db->fetchOne($query, [$id, $languageCode]);

        // Nếu không tìm thấy với ngôn ngữ được chỉ định, lấy thông tin cơ bản của section
        if (!$result) {
            $query = "SELECT s.* FROM {$this->table} s WHERE s.id = ?";
            $result = $this->db->fetchOne($query, [$id]);

            if ($result) {
                $result['title'] = '';
                $result['description'] = '';
                $result['module_name'] = '';
                $result['language_code'] = $languageCode;
            }
        }

        return $result;
    }

    // Cập nhật phương thức getAllByLang()
    public function getAllByLang($languageCode = null, $filters = [])
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $params = [$languageCode];
        $conditions = ["l.code = ?"];

        if (isset($filters['is_active'])) {
            $conditions[] = "s.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (isset($filters['show_on_mobile'])) {
            $conditions[] = "s.show_on_mobile = ?";
            $params[] = $filters['show_on_mobile'];
        }

        $where = implode(' AND ', $conditions);

        $query = "SELECT s.*, t.title, t.description, t.module_name,
                     l.code as language_code
              FROM {$this->table} s
              LEFT JOIN {$this->translationTable} t ON s.id = t.section_id
              LEFT JOIN languages l ON t.language_id = l.id
              WHERE {$where}
              ORDER BY s.sort_order ASC";

        $results = $this->db->fetchAll($query, $params);

        // Nếu không có kết quả với ngôn ngữ được chỉ định, lấy tất cả sections
        if (empty($results)) {
            $query = "SELECT s.* FROM {$this->table} s";
            if (isset($filters['is_active'])) {
                $query .= " WHERE s.is_active = ?";
                $results = $this->db->fetchAll($query, [$filters['is_active']]);
            } else {
                $results = $this->db->fetchAll($query);
            }

            // Thêm thông tin ngôn ngữ mặc định
            foreach ($results as &$result) {
                $result['title'] = '';
                $result['description'] = '';
                $result['module_name'] = '';
                $result['language_code'] = $languageCode;
            }
        }

        return $results;
    }

    // Tạo section mới
    public function create($data)
    {
        $this->db->beginTransaction();

        try {
            // Tạo code nếu chưa có hoặc rỗng
            if (empty($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['translations'][0]['module_name'] ?? 'section');
            }

            // Dữ liệu section
            $sectionData = [
                'code' => $data['code'],
                'template' => $data['template'] ?? 'default',
                'image' => $data['image'] ?? null,
                'icon' => $data['icon'] ?? null,
                'is_active' => $data['is_active'] ?? 1,
                'show_on_mobile' => $data['show_on_mobile'] ?? 1,
                'sort_order' => $data['sort_order'] ?? 0
            ];

            // Thêm section vào database
            $sectionId = $this->db->insert($this->table, $sectionData);

            // Thêm các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }

                    $translationData = [
                        'section_id' => $sectionId,
                        'language_id' => $language['id'],
                        'module_name' => $translation['module_name'],
                        'title' => $translation['title'],
                        'description' => $translation['description'] ?? null
                    ];

                    $this->db->insert($this->translationTable, $translationData);
                }
            }

            $this->db->commit();
            return $this->findById($sectionId);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Cập nhật section
    public function update($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Cập nhật thông tin section
            $sectionData = [];
            if (isset($data['code']) && !empty($data['code'])) {
                $sectionData['code'] = $data['code'];
            }
            if (isset($data['template'])) $sectionData['template'] = $data['template'];
            if (isset($data['image'])) $sectionData['image'] = $data['image'];
            if (isset($data['icon'])) $sectionData['icon'] = $data['icon'];
            if (isset($data['is_active'])) $sectionData['is_active'] = $data['is_active'];
            if (isset($data['show_on_mobile'])) $sectionData['show_on_mobile'] = $data['show_on_mobile'];
            if (isset($data['sort_order'])) $sectionData['sort_order'] = $data['sort_order'];

            if (!empty($sectionData)) {
                $this->db->update($this->table, $sectionData, 'id = ?', [$id]);
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
                         WHERE section_id = ? AND language_id = ?",
                        [$id, $language['id']]
                    );

                    $translationData = [
                        'module_name' => $translation['module_name'],
                        'title' => $translation['title'],
                        'description' => $translation['description'] ?? null
                    ];

                    if ($existingTranslation) {
                        // Cập nhật
                        $this->db->update(
                            $this->translationTable,
                            $translationData,
                            'section_id = ? AND language_id = ?',
                            [$id, $language['id']]
                        );
                    } else {
                        // Thêm mới
                        $translationData['section_id'] = $id;
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

    // Xóa section
    public function delete($id)
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    // Lấy tất cả bản dịch của một section
    public function getAllTranslations($id)
    {
        $query = "SELECT t.*, l.code as language_code, l.name as language_name
                  FROM {$this->translationTable} t
                  JOIN languages l ON t.language_id = l.id
                  WHERE t.section_id = ?";

        return $this->db->fetchAll($query, [$id]);
    }

    // Tạo code unique
    private function generateUniqueCode($title)
    {
        // Chuyển về chữ thường
        $code = strtolower($title);

        // Chuyển ký tự có dấu thành không dấu
        $vietnameseMap = [
            'à' => 'a',
            'á' => 'a',
            'ạ' => 'a',
            'ả' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'ầ' => 'a',
            'ấ' => 'a',
            'ậ' => 'a',
            'ẩ' => 'a',
            'ẫ' => 'a',
            'ă' => 'a',
            'ằ' => 'a',
            'ắ' => 'a',
            'ặ' => 'a',
            'ẳ' => 'a',
            'ẵ' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ẹ' => 'e',
            'ẻ' => 'e',
            'ẽ' => 'e',
            'ê' => 'e',
            'ề' => 'e',
            'ế' => 'e',
            'ệ' => 'e',
            'ể' => 'e',
            'ễ' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'ị' => 'i',
            'ỉ' => 'i',
            'ĩ' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ọ' => 'o',
            'ỏ' => 'o',
            'õ' => 'o',
            'ô' => 'o',
            'ồ' => 'o',
            'ố' => 'o',
            'ộ' => 'o',
            'ổ' => 'o',
            'ỗ' => 'o',
            'ơ' => 'o',
            'ờ' => 'o',
            'ớ' => 'o',
            'ợ' => 'o',
            'ở' => 'o',
            'ỡ' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'ụ' => 'u',
            'ủ' => 'u',
            'ũ' => 'u',
            'ư' => 'u',
            'ừ' => 'u',
            'ứ' => 'u',
            'ự' => 'u',
            'ử' => 'u',
            'ữ' => 'u',
            'ỳ' => 'y',
            'ý' => 'y',
            'ỵ' => 'y',
            'ỷ' => 'y',
            'ỹ' => 'y',
            'đ' => 'd',
        ];

        foreach ($vietnameseMap as $vietnamese => $latin) {
            $code = str_replace($vietnamese, $latin, $code);
        }

        // Chỉ giữ lại ký tự chữ cái và số
        $code = preg_replace('/[^a-z0-9]+/', '-', $code);

        // Loại bỏ dấu - ở đầu và cuối
        $code = trim($code, '-');

        // Nếu code rỗng, tạo code mặc định
        if (empty($code)) {
            $code = 'section-' . time();
        }

        // Kiểm tra code đã tồn tại chưa
        $existingSection = $this->db->fetchOne(
            "SELECT id FROM {$this->table} WHERE code = ?",
            [$code]
        );

        // Nếu code đã tồn tại, thêm số vào cuối
        if ($existingSection) {
            $i = 1;
            do {
                $newCode = $code . '-' . $i;
                $existingSection = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} WHERE code = ?",
                    [$newCode]
                );
                $i++;
            } while ($existingSection);

            $code = $newCode;
        }

        return $code;
    }

    // Cập nhật thứ tự
    public function updateSortOrder($id, $sortOrder)
    {
        return $this->db->update($this->table, ['sort_order' => $sortOrder], 'id = ?', [$id]);
    }
}
