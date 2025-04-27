<?php
// utils/Database.php
// Lớp kết nối và tương tác với cơ sở dữ liệu

require_once __DIR__ . '/../config/database.php';

class Database {
    private $conn;
    private static $instance = null;
    
    // Phương thức khởi tạo kết nối đến cơ sở dữ liệu
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DatabaseConfig::DB_HOST . 
                ";dbname=" . DatabaseConfig::DB_NAME . 
                ";charset=" . DatabaseConfig::DB_CHARSET,
                DatabaseConfig::DB_USER,
                DatabaseConfig::DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Xử lý lỗi kết nối
            die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
        }
    }
    
    // Phương thức lấy instance (Singleton pattern)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Phương thức lấy kết nối
    public function getConnection() {
        return $this->conn;
    }
    
    // Phương thức chuẩn bị và thực thi câu truy vấn
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Xử lý lỗi truy vấn
            throw new Exception("Lỗi truy vấn: " . $e->getMessage());
        }
    }
    
    // Phương thức lấy một bản ghi
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    // Phương thức lấy nhiều bản ghi
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Phương thức thêm dữ liệu
    public function insert($table, $data) {
        // Tạo các phần của câu truy vấn
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        // Câu truy vấn SQL
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        // Thực thi câu truy vấn
        $this->query($sql, array_values($data));
        
        // Trả về ID của bản ghi vừa thêm
        return $this->conn->lastInsertId();
    }
    
    // Phương thức cập nhật dữ liệu
    public function update($table, $data, $where, $whereParams = []) {
        // Tạo phần SET của câu truy vấn
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        $setClause = implode(', ', $set);
        
        // Câu truy vấn SQL
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        // Tham số cho câu truy vấn (kết hợp giữa data và whereParams)
        $params = array_merge(array_values($data), $whereParams);
        
        // Thực thi câu truy vấn
        $stmt = $this->query($sql, $params);
        
        // Trả về số hàng bị ảnh hưởng
        return $stmt->rowCount();
    }
    
    // Phương thức xóa dữ liệu
    public function delete($table, $where, $params = []) {
        // Câu truy vấn SQL
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        // Thực thi câu truy vấn
        $stmt = $this->query($sql, $params);
        
        // Trả về số hàng bị ảnh hưởng
        return $stmt->rowCount();
    }
    
    // Transaction methods
    
    // Bắt đầu transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    // Commit transaction
    public function commit() {
        return $this->conn->commit();
    }
    
    // Rollback transaction
    public function rollback() {
        return $this->conn->rollBack();
    }
    
    // Kiểm tra xem đang trong transaction không
    public function inTransaction() {
        return $this->conn->inTransaction();
    }
    
    // Thực thi nhiều câu query trong một transaction
    public function transactional(callable $callback) {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->inTransaction()) {
                $this->rollback();
            }
            throw $e;
        }
    }
    
    // Phương thức lấy ID của bản ghi vừa insert
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}