<?php
// utils/JWT.php
// Lớp xử lý JSON Web Token

require_once __DIR__ . '/../config/jwt.php';

class JWT {
    // Phương thức tạo JWT
    public static function generate($payload) {
        // Tạo phần header
        $header = [
            'alg' => JWTConfig::ALGORITHM,
            'typ' => 'JWT'
        ];
        
        // Thêm thời gian hết hạn vào payload
        $payload['exp'] = time() + JWTConfig::EXPIRATION_TIME;
        $payload['iat'] = time(); // Thời gian tạo token
        
        // Mã hóa header và payload
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        // Tạo chữ ký
        $signature = hash_hmac(
            'sha256', 
            $headerEncoded . '.' . $payloadEncoded, 
            JWTConfig::SECRET_KEY, 
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);
        
        // Kết hợp 3 phần để tạo JWT
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    // Phương thức xác thực JWT
    public static function verify($token) {
        // Phân tách JWT thành 3 phần
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureProvided) = $parts;
        
        // Tạo lại chữ ký từ header và payload
        $signatureCalculated = hash_hmac(
            'sha256', 
            $headerEncoded . '.' . $payloadEncoded, 
            JWTConfig::SECRET_KEY, 
            true
        );
        $signatureCalculatedEncoded = self::base64UrlEncode($signatureCalculated);
        
        // So sánh chữ ký
        if ($signatureProvided !== $signatureCalculatedEncoded) {
            return false;
        }
        
        // Giải mã payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Kiểm tra thời gian hết hạn
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token đã hết hạn
        }
        
        // Trả về payload nếu xác thực thành công
        return $payload;
    }
    
    // Phương thức mã hóa base64url
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    // Phương thức giải mã base64url
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}