<?php
// models/User.php
// Model xử lý dữ liệu bảng users - Sử dụng MD5 thay cho bcrypt

require_once __DIR__ . '/../utils/Database.php';

class User {
    private $db;
    private $table = 'users';
    
    // Khởi tạo kết nối database
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Phương thức tìm người dùng theo ID
    public function findById($id) {
        return $this->db->fetchOne(
            "SELECT id, name, email, created_at FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }
    
    // Phương thức tìm người dùng theo email
    public function findByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }
    
    // Phương thức xác thực người dùng
    public function authenticate($email, $password) {
        // Tìm người dùng theo email
        $user = $this->findByEmail($email);
        
        // Nếu không tìm thấy người dùng hoặc mật khẩu không khớp
        if (!$user || md5($password) !== $user['password']) {
            return false;
        }
        
        // Loại bỏ mật khẩu trước khi trả về thông tin người dùng
        unset($user['password']);
        return $user;
    }
    
    // Phương thức tạo người dùng mới
    public function create($data) {
        // Mã hóa mật khẩu sử dụng MD5
        $data['password'] = md5($data['password']);
        
        // Thêm người dùng vào database
        $userId = $this->db->insert($this->table, $data);
        
        // Trả về thông tin người dùng
        return $this->findById($userId);
    }
    
    // Phương thức cập nhật thông tin người dùng
    public function update($id, $data) {
        // Nếu có cập nhật mật khẩu, mã hóa mật khẩu bằng MD5
        if (isset($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        
        // Cập nhật thông tin người dùng
        $this->db->update($this->table, $data, 'id = ?', [$id]);
        
        // Trả về thông tin người dùng đã cập nhật
        return $this->findById($id);
    }
    
    // Phương thức xóa người dùng
    public function delete($id) {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }
    
    // Phương thức lấy danh sách người dùng
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT id, name, email, created_at FROM {$this->table}"
        );
    }
}