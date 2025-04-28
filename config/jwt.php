<?php
// config/jwt.php
// Tệp cấu hình cho JWT (JSON Web Tokens)

class JWTConfig {
    // Khóa bí mật để ký và xác thực JWT
    const SECRET_KEY = 'your_secret_key_here';  // Thay đổi giá trị này trong môi trường production
    
    // Thời gian hết hạn cho token (tính bằng giây)
    const EXPIRATION_TIME = 86400;  // 1 giờ (3600 giây)
    
    // Thuật toán mã hóa sử dụng cho JWT
    const ALGORITHM = 'HS256';  // HMAC với SHA-256
}