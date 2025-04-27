<?php
// config/database.php
// Tệp cấu hình kết nối cơ sở dữ liệu MySQL

class DatabaseConfig {
    // Các thông số kết nối cơ sở dữ liệu
    const DB_HOST = 'localhost';      // Địa chỉ máy chủ MySQL
    const DB_NAME = 'dev';     // Tên cơ sở dữ liệu
    const DB_USER = 'root';           // Tên người dùng MySQL
    const DB_PASS = '';               // Mật khẩu MySQL
    const DB_CHARSET = 'utf8mb4';     // Charset hỗ trợ tiếng Việt và Unicode
}