<?php
// models/Language.php
// Model xử lý dữ liệu bảng languages

require_once __DIR__ . '/../utils/Database.php';

class Language {
    private $db;
    private $table = 'languages';
    
    // Khởi tạo kết nối database
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Phương thức lấy tất cả ngôn ngữ
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY sort_order ASC";
        return $this->db->fetchAll($query);
    }
    
    // Phương thức lấy các ngôn ngữ active
    public function getActive() {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC";
        return $this->db->fetchAll($query);
    }
    
    // Phương thức lấy ngôn ngữ mặc định
    public function getDefault() {
        $query = "SELECT * FROM {$this->table} WHERE is_default = 1 LIMIT 1";
        return $this->db->fetchOne($query);
    }
    
    // Phương thức tìm ngôn ngữ theo ID
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($query, [$id]);
    }
    
    // Phương thức tìm ngôn ngữ theo code
    public function findByCode($code) {
        $query = "SELECT * FROM {$this->table} WHERE code = ?";
        return $this->db->fetchOne($query, [$code]);
    }
    
    // Phương thức tạo ngôn ngữ mới
    public function create($data) {
        $insertData = [
            'code' => $data['code'],
            'name' => $data['name'],
            'is_default' => $data['is_default'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0
        ];
        
        // Nếu set is_default = 1, cần reset các ngôn ngữ khác
        if ($insertData['is_default'] == 1) {
            $this->db->update($this->table, ['is_default' => 0], '1=1');
        }
        
        $id = $this->db->insert($this->table, $insertData);
        return $this->findById($id);
    }
    
    // Phương thức cập nhật ngôn ngữ
    public function update($id, $data) {
        $updateData = [];
        
        if (isset($data['code'])) $updateData['code'] = $data['code'];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
        if (isset($data['sort_order'])) $updateData['sort_order'] = $data['sort_order'];
        
        if (isset($data['is_default'])) {
            $updateData['is_default'] = $data['is_default'];
            
            // Nếu set is_default = 1, cần reset các ngôn ngữ khác
            if ($data['is_default'] == 1) {
                $this->db->update($this->table, ['is_default' => 0], 'id != ?', [$id]);
            }
        }
        
        if (!empty($updateData)) {
            $this->db->update($this->table, $updateData, 'id = ?', [$id]);
        }
        
        return $this->findById($id);
    }
    
    // Phương thức xóa ngôn ngữ
    public function delete($id) {
        // Kiểm tra xem có phải ngôn ngữ mặc định không
        $language = $this->findById($id);
        if ($language && $language['is_default'] == 1) {
            throw new Exception('Không thể xóa ngôn ngữ mặc định');
        }
        
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }
    
    // Phương thức đặt ngôn ngữ mặc định
    public function setDefault($id) {
        // Reset all languages to non-default
        $this->db->update($this->table, ['is_default' => 0], '1=1');
        
        // Set the specified language as default
        $this->db->update($this->table, ['is_default' => 1], 'id = ?', [$id]);
        
        return $this->findById($id);
    }
    
    // Phương thức kiểm tra xem code đã tồn tại chưa
    public function codeExists($code, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE code = ?";
        $params = [$code];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($query, $params);
        return $result['count'] > 0;
    }
    
    // Phương thức lấy tổng số ngôn ngữ
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetchOne($query);
        return $result['total'];
    }
    
    // Phương thức lấy tổng số ngôn ngữ active
    public function getActiveCount() {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1";
        $result = $this->db->fetchOne($query);
        return $result['total'];
    }
}