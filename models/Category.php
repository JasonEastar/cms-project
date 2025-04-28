<?php
// models/Category.php
// Model xử lý dữ liệu bảng categories với đa ngôn ngữ

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/Language.php';

class Category {
    private $db;
    private $table = 'categories';
    private $translationTable = 'category_translations';
    private $languageModel;
    
    // Khởi tạo kết nối database
    public function __construct() {
        $this->db = Database::getInstance();
        $this->languageModel = new LanguageModel();
    }
    
    // Phương thức tìm danh mục theo ID với thông tin ngôn ngữ
    public function findById($id, $languageCode = null) {
        // Nếu không có code, lấy ngôn ngữ mặc định
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }
        
        $query = "SELECT c.*, t.name, t.slug, t.description, t.seo_title, t.seo_description, l.code as language_code
                  FROM {$this->table} c
                  LEFT JOIN {$this->translationTable} t ON c.id = t.category_id
                  LEFT JOIN languages l ON t.language_id = l.id
                  WHERE c.id = ? AND l.code = ?";
        
        return $this->db->fetchOne($query, [$id, $languageCode]);
    }
    
    // Phương thức tìm danh mục theo slug và ngôn ngữ
    public function findBySlug($slug, $languageCode = null) {
        // Nếu không có code, lấy ngôn ngữ mặc định
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }
        
        $query = "SELECT c.*, t.name, t.slug, t.description, t.seo_title, t.seo_description, l.code as language_code
                  FROM {$this->table} c
                  JOIN {$this->translationTable} t ON c.id = t.category_id
                  JOIN languages l ON t.language_id = l.id
                  WHERE t.slug = ? AND l.code = ?";
        
        return $this->db->fetchOne($query, [$slug, $languageCode]);
    }
    
    // Phương thức lấy tất cả danh mục theo ngôn ngữ
    public function getAllByLang($languageCode = null) {
        // Nếu không có code, lấy ngôn ngữ mặc định
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }
        
        $query = "SELECT c.*, t.name, t.slug, t.description, t.seo_title, t.seo_description, l.code as language_code
                  FROM {$this->table} c
                  LEFT JOIN {$this->translationTable} t ON c.id = t.category_id
                  LEFT JOIN languages l ON t.language_id = l.id
                  WHERE l.code = ? AND c.is_active = 1
                  ORDER BY t.name ASC";
        
        return $this->db->fetchAll($query, [$languageCode]);
    }
    
    // Phương thức lấy danh mục cha và con theo ngôn ngữ
    public function getHierarchicalCategories($languageCode = null) {
        // Lấy tất cả danh mục
        $categories = $this->getAllByLang($languageCode);
        
        // Tạo cây danh mục
        $tree = [];
        $children = [];
        
        // Nhóm các danh mục con theo parent_id
        foreach ($categories as $category) {
            $parentId = $category['parent_id'] ?? 0;
            if (!isset($children[$parentId])) {
                $children[$parentId] = [];
            }
            $children[$parentId][] = $category;
        }
        
        // Tạo cây danh mục đệ quy
        $buildTree = function ($parentId = 0) use (&$buildTree, &$children) {
            $branch = [];
            
            if (isset($children[$parentId])) {
                foreach ($children[$parentId] as $child) {
                    $child['children'] = $buildTree($child['id']);
                    $branch[] = $child;
                }
            }
            
            return $branch;
        };
        
        // Xây dựng cây danh mục từ gốc (parent_id = 0 hoặc NULL)
        $tree = $buildTree(0);
        
        return $tree;
    }
    
    // Phương thức tạo danh mục mới
    public function create($data) {
        // Bắt đầu transaction
        $this->db->beginTransaction();
        
        try {
            // Tạo code nếu chưa có
            if (!isset($data['code']) || empty($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['translations'][0]['name'] ?? 'category');
            }
            
            // Tách dữ liệu danh mục và dữ liệu dịch
            $categoryData = [
                'code' => $data['code'],
                'parent_id' => $data['parent_id'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ];
            
            // Thêm danh mục vào database
            $categoryId = $this->db->insert($this->table, $categoryData);
            
            // Thêm các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    // Lấy language_id từ language_code
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }
                    
                    // Tạo slug nếu chưa có
                    if (!isset($translation['slug']) || empty($translation['slug'])) {
                        $translation['slug'] = $this->createSlug($translation['name'], $language['id']);
                    }
                    
                    $translationData = [
                        'category_id' => $categoryId,
                        'language_id' => $language['id'],
                        'name' => $translation['name'],
                        'slug' => $translation['slug'],
                        'description' => $translation['description'] ?? null,
                        'seo_title' => $translation['seo_title'] ?? null,
                        'seo_description' => $translation['seo_description'] ?? null
                    ];
                    
                    $this->db->insert($this->translationTable, $translationData);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Trả về thông tin danh mục
            return $this->findById($categoryId);
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->db->rollback();
            throw $e;
        }
    }
    
    // Phương thức cập nhật danh mục
    public function update($id, $data) {
        // Bắt đầu transaction
        $this->db->beginTransaction();
        
        try {
            // Cập nhật thông tin danh mục
            $categoryData = [];
            if (isset($data['parent_id'])) $categoryData['parent_id'] = $data['parent_id'];
            if (isset($data['is_active'])) $categoryData['is_active'] = $data['is_active'];
            
            if (!empty($categoryData)) {
                $this->db->update($this->table, $categoryData, 'id = ?', [$id]);
            }
            
            // Cập nhật các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    // Lấy language_id từ language_code
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }
                    
                    // Kiểm tra xem bản dịch đã tồn tại chưa
                    $existingTranslation = $this->db->fetchOne(
                        "SELECT id FROM {$this->translationTable} 
                         WHERE category_id = ? AND language_id = ?",
                        [$id, $language['id']]
                    );
                    
                    $translationData = [
                        'name' => $translation['name'],
                        'slug' => $translation['slug'] ?? $this->createSlug($translation['name'], $language['id']),
                        'description' => $translation['description'] ?? null,
                        'seo_title' => $translation['seo_title'] ?? null,
                        'seo_description' => $translation['seo_description'] ?? null
                    ];
                    
                    if ($existingTranslation) {
                        // Cập nhật
                        $this->db->update(
                            $this->translationTable,
                            $translationData,
                            'category_id = ? AND language_id = ?',
                            [$id, $language['id']]
                        );
                    } else {
                        // Thêm mới
                        $translationData['category_id'] = $id;
                        $translationData['language_id'] = $language['id'];
                        $this->db->insert($this->translationTable, $translationData);
                    }
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Trả về thông tin danh mục đã cập nhật
            return $this->findById($id);
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->db->rollback();
            throw $e;
        }
    }
    
    // Phương thức xóa danh mục
    public function delete($id) {
        // Xóa danh mục (các bản dịch sẽ tự động xóa theo do có ON DELETE CASCADE)
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }
    
    // Phương thức tạo slug từ tên danh mục
    private function createSlug($name, $languageId) {
        // Chuyển đổi tên thành slug
        $slug = strtolower($name);
        
        // Xử lý tiếng Việt
        $slug = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $slug);
        $slug = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $slug);
        $slug = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $slug);
        $slug = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $slug);
        $slug = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $slug);
        $slug = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $slug);
        $slug = preg_replace('/(đ)/', 'd', $slug);
        
        // Loại bỏ các ký tự đặc biệt
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        
        // Thay thế khoảng trắng bằng dấu gạch ngang
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        
        // Loại bỏ dấu gạch ngang ở đầu và cuối
        $slug = trim($slug, '-');
        
        // Kiểm tra xem slug đã tồn tại chưa
        $existingCategory = $this->db->fetchOne(
            "SELECT id FROM {$this->translationTable} 
             WHERE slug = ? AND language_id = ?",
            [$slug, $languageId]
        );
        
        // Nếu slug đã tồn tại, thêm số vào cuối
        if ($existingCategory) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                $existingCategory = $this->db->fetchOne(
                    "SELECT id FROM {$this->translationTable} 
                     WHERE slug = ? AND language_id = ?",
                    [$newSlug, $languageId]
                );
                $i++;
            } while ($existingCategory);
            
            $slug = $newSlug;
        }
        
        return $slug;
    }
    
    // Phương thức tạo code unique
    private function generateUniqueCode($name) {
        $code = strtolower($name);
        $code = preg_replace('/[^a-z0-9]+/', '-', $code);
        $code = trim($code, '-');
        
        // Kiểm tra xem code đã tồn tại chưa
        $existingCategory = $this->db->fetchOne(
            "SELECT id FROM {$this->table} WHERE code = ?",
            [$code]
        );
        
        if ($existingCategory) {
            $i = 1;
            do {
                $newCode = $code . '-' . $i;
                $existingCategory = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} WHERE code = ?",
                    [$newCode]
                );
                $i++;
            } while ($existingCategory);
            
            $code = $newCode;
        }
        
        return $code;
    }
    
    // Phương thức lấy tất cả bản dịch của một danh mục
    public function getAllTranslations($id) {
        $query = "SELECT t.*, l.code as language_code, l.name as language_name
                  FROM {$this->translationTable} t
                  JOIN languages l ON t.language_id = l.id
                  WHERE t.category_id = ?";
        
        return $this->db->fetchAll($query, [$id]);
    }
}