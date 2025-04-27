<?php
// models/SiteConfig.php
// Model xử lý dữ liệu bảng site_configs

require_once __DIR__ . '/../utils/Database.php';

class SiteConfig {
    private $db;
    private $table = 'site_configs';
    
    // Khởi tạo kết nối database
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Phương thức lấy cấu hình theo khóa và ngôn ngữ
    public function get($key, $lang) {
        $result = $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE config_key = ? AND lang = ?",
            [$key, $lang]
        );
        
        if ($result) {
            // Nếu là JSON, giải mã
            if ($this->isJson($result['config_value'])) {
                return json_decode($result['config_value'], true);
            }
            
            // Trả về giá trị nguyên bản
            return $result['config_value'];
        }
        
        return null;
    }
    
    // Phương thức lấy nhiều cấu hình theo nhóm và ngôn ngữ
    // Ví dụ: getGroup('company', 'vi') sẽ lấy tất cả cấu hình có key bắt đầu bằng 'company_'
    public function getGroup($prefix, $lang) {
        $results = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE config_key LIKE ? AND lang = ?",
            [$prefix . '_%', $lang]
        );
        
        $configs = [];
        foreach ($results as $result) {
            $key = str_replace($prefix . '_', '', $result['config_key']);
            
            // Nếu là JSON, giải mã
            if ($this->isJson($result['config_value'])) {
                $configs[$key] = json_decode($result['config_value'], true);
            } else {
                $configs[$key] = $result['config_value'];
            }
        }
        
        return $configs;
    }
    
    // Phương thức lấy tất cả cấu hình theo ngôn ngữ
    public function getAllByLang($lang) {
        $results = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE lang = ?",
            [$lang]
        );
        
        $configs = [];
        foreach ($results as $result) {
            // Nếu là JSON, giải mã
            if ($this->isJson($result['config_value'])) {
                $configs[$result['config_key']] = json_decode($result['config_value'], true);
            } else {
                $configs[$result['config_key']] = $result['config_value'];
            }
        }
        
        return $configs;
    }
    
    // Phương thức lưu cấu hình
    public function set($key, $value, $lang) {
        // Kiểm tra xem cấu hình đã tồn tại chưa
        $existing = $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE config_key = ? AND lang = ?",
            [$key, $lang]
        );
        
        // Nếu là array hoặc object, chuyển thành JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        // Nếu cấu hình đã tồn tại, cập nhật
        if ($existing) {
            $this->db->update(
                $this->table,
                ['config_value' => $value],
                'config_key = ? AND lang = ?',
                [$key, $lang]
            );
            return true;
        }
        
        // Nếu cấu hình chưa tồn tại, thêm mới
        $this->db->insert($this->table, [
            'lang' => $lang,
            'config_key' => $key,
            'config_value' => $value
        ]);
        
        return true;
    }
    
    // Phương thức xóa cấu hình
    public function delete($key, $lang) {
        return $this->db->delete(
            $this->table,
            'config_key = ? AND lang = ?',
            [$key, $lang]
        );
    }
    
    // Phương thức kiểm tra xem một chuỗi có phải là JSON không
    private function isJson($string) {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
    // Phương thức lưu nhiều cấu hình cùng lúc
    public function setBulk($configs, $lang) {
        foreach ($configs as $key => $value) {
            $this->set($key, $value, $lang);
        }
        
        return true;
    }
    
    // Phương thức lưu cấu hình cho nhiều ngôn ngữ
    public function setMultiLang($key, $values) {
        foreach ($values as $lang => $value) {
            $this->set($key, $value, $lang);
        }
        
        return true;
    }
}