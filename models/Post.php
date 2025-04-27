<?php
// models/Post.php
// Model xử lý dữ liệu bảng posts với đa ngôn ngữ

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/Language.php';

class Post
{
    private $db;
    private $table = 'posts';
    private $translationTable = 'post_translations';
    private $languageModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->languageModel = new Language();
    }

    // Tìm bài viết theo ID với thông tin ngôn ngữ
    public function findById($id, $languageCode = null)
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $query = "SELECT p.*, t.title, t.slug, t.summary, t.content, t.seo_title, t.seo_description, 
                        l.code as language_code, ct.name as category_name, ct.slug as category_slug
                  FROM {$this->table} p
                  LEFT JOIN {$this->translationTable} t ON p.id = t.post_id
                  LEFT JOIN languages l ON t.language_id = l.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN category_translations ct ON c.id = ct.category_id AND ct.language_id = l.id
                  WHERE p.id = ? AND l.code = ?";

        return $this->db->fetchOne($query, [$id, $languageCode]);
    }

    // Tìm bài viết theo slug và ngôn ngữ
    public function findBySlug($slug, $languageCode = null)
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $query = "SELECT p.*, t.title, t.slug, t.summary, t.content, t.seo_title, t.seo_description,
                        l.code as language_code, ct.name as category_name, ct.slug as category_slug
                  FROM {$this->table} p
                  JOIN {$this->translationTable} t ON p.id = t.post_id
                  JOIN languages l ON t.language_id = l.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN category_translations ct ON c.id = ct.category_id AND ct.language_id = l.id
                  WHERE t.slug = ? AND l.code = ?";

        return $this->db->fetchOne($query, [$slug, $languageCode]);
    }

    // Lấy tất cả bài viết theo ngôn ngữ
    public function getAllByLang($languageCode = null, $filters = [])
    {
        if (!$languageCode) {
            $defaultLang = $this->languageModel->getDefault();
            $languageCode = $defaultLang['code'];
        }

        $params = [$languageCode];
        $conditions = ["l.code = ?"];

        // Xử lý filters
        if (isset($filters['category_id'])) {
            $conditions[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (isset($filters['is_active'])) {
            $conditions[] = "p.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (isset($filters['is_featured'])) {
            $conditions[] = "p.is_featured = ?";
            $params[] = $filters['is_featured'];
        }

        $where = implode(' AND ', $conditions);

        $query = "SELECT p.*, t.title, t.slug, t.summary, t.content, t.seo_title, t.seo_description,
                        l.code as language_code, ct.name as category_name, ct.slug as category_slug
                  FROM {$this->table} p
                  LEFT JOIN {$this->translationTable} t ON p.id = t.post_id
                  LEFT JOIN languages l ON t.language_id = l.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN category_translations ct ON c.id = ct.category_id AND ct.language_id = l.id
                  WHERE {$where}
                  ORDER BY p.created_at DESC";

        return $this->db->fetchAll($query, $params);
    }

    // Tạo bài viết mới
    public function create($data)
    {
        $this->db->beginTransaction();

        try {
            // Tạo code nếu chưa có
            if (!isset($data['code']) || empty($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['translations'][0]['title'] ?? 'post');
            }

            // Dữ liệu bài viết
            $postData = [
                'code' => $data['code'],
                'category_id' => $data['category_id'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
                'is_featured' => $data['is_featured'] ?? 0,
                'is_active' => $data['is_active'] ?? 1,
                'published_at' => $data['published_at'] ?? null,
                'views' => 0
            ];

            // Thêm bài viết vào database
            $postId = $this->db->insert($this->table, $postData);

            // Thêm các bản dịch
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $language = $this->languageModel->findByCode($translation['language_code']);
                    if (!$language) {
                        throw new Exception("Ngôn ngữ không tồn tại: " . $translation['language_code']);
                    }

                    // Tạo slug nếu chưa có
                    if (!isset($translation['slug']) || empty($translation['slug'])) {
                        $translation['slug'] = $this->createSlug($translation['title'], $language['id']);
                    }

                    $translationData = [
                        'post_id' => $postId,
                        'language_id' => $language['id'],
                        'title' => $translation['title'],
                        'slug' => $translation['slug'],
                        'summary' => $translation['summary'] ?? null,
                        'content' => $translation['content'],
                        'seo_title' => $translation['seo_title'] ?? null,
                        'seo_description' => $translation['seo_description'] ?? null
                    ];

                    $this->db->insert($this->translationTable, $translationData);
                }
            }

            $this->db->commit();
            return $this->findById($postId);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Cập nhật bài viết
    public function update($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Cập nhật thông tin bài viết
            $postData = [];
            if (isset($data['category_id'])) $postData['category_id'] = $data['category_id'];
            if (isset($data['thumbnail'])) $postData['thumbnail'] = $data['thumbnail'];
            if (isset($data['is_featured'])) $postData['is_featured'] = $data['is_featured'];
            if (isset($data['is_active'])) $postData['is_active'] = $data['is_active'];
            if (isset($data['published_at'])) $postData['published_at'] = $data['published_at'];

            if (!empty($postData)) {
                $this->db->update($this->table, $postData, 'id = ?', [$id]);
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
                         WHERE post_id = ? AND language_id = ?",
                        [$id, $language['id']]
                    );

                    $translationData = [
                        'title' => $translation['title'],
                        'slug' => $translation['slug'] ?? $this->createSlug($translation['title'], $language['id']),
                        'summary' => $translation['summary'] ?? null,
                        'content' => $translation['content'],
                        'seo_title' => $translation['seo_title'] ?? null,
                        'seo_description' => $translation['seo_description'] ?? null
                    ];

                    if ($existingTranslation) {
                        // Cập nhật
                        $this->db->update(
                            $this->translationTable,
                            $translationData,
                            'post_id = ? AND language_id = ?',
                            [$id, $language['id']]
                        );
                    } else {
                        // Thêm mới
                        $translationData['post_id'] = $id;
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

    // Xóa bài viết
    public function delete($id)
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    // Tạo slug từ tiêu đề
    private function createSlug($title, $languageId)
    {
        $slug = strtolower($title);

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

        // Kiểm tra slug đã tồn tại chưa
        $existingPost = $this->db->fetchOne(
            "SELECT id FROM {$this->translationTable} 
             WHERE slug = ? AND language_id = ?",
            [$slug, $languageId]
        );

        // Nếu slug đã tồn tại, thêm số vào cuối
        if ($existingPost) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                $existingPost = $this->db->fetchOne(
                    "SELECT id FROM {$this->translationTable} 
                     WHERE slug = ? AND language_id = ?",
                    [$newSlug, $languageId]
                );
                $i++;
            } while ($existingPost);

            $slug = $newSlug;
        }

        return $slug;
    }

    // Tạo code unique
    private function generateUniqueCode($title)
    {
        $code = strtolower($title);
        $code = preg_replace('/[^a-z0-9]+/', '-', $code);
        $code = trim($code, '-');

        // Kiểm tra code đã tồn tại chưa
        $existingPost = $this->db->fetchOne(
            "SELECT id FROM {$this->table} WHERE code = ?",
            [$code]
        );

        if ($existingPost) {
            $i = 1;
            do {
                $newCode = $code . '-' . $i;
                $existingPost = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} WHERE code = ?",
                    [$newCode]
                );
                $i++;
            } while ($existingPost);

            $code = $newCode;
        }

        return $code;
    }

    // Lấy tất cả bản dịch của một bài viết
    public function getAllTranslations($id)
    {
        $query = "SELECT t.*, l.code as language_code, l.name as language_name
                  FROM {$this->translationTable} t
                  JOIN languages l ON t.language_id = l.id
                  WHERE t.post_id = ?";

        return $this->db->fetchAll($query, [$id]);
    }

    // Tăng lượt xem
    public function incrementViews($id) {
       
            // Hoặc nếu không có getConnection(), thử với fetch
            $query = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
            return $this->db->query($query, [$id]);
    }
}
