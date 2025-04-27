<?php
class Language {
    private static $translations = [];
    private static $currentLanguage = 'vi';
    private static $fallbackLanguage = 'vi';
    
    // Initialize the language system
    public static function init() {
        // Get current language from localStorage via JavaScript
        self::$currentLanguage = isset($_COOKIE['admin_language']) ? $_COOKIE['admin_language'] : 'vi';
        
        // Load translations
        self::loadTranslations(self::$currentLanguage);
        
        // Load fallback language if needed
        if (self::$currentLanguage !== self::$fallbackLanguage) {
            self::loadTranslations(self::$fallbackLanguage, true);
        }
    }
    
    // Load translations from language file
    private static function loadTranslations($language, $isFallback = false) {
        $filePath = __DIR__ . "/../languages/{$language}.php";
        // (Giữ lại mã gỡ lỗi nếu bạn muốn xác nhận lại, hoặc xóa đi)

        if (file_exists($filePath)) {
            $translations = include $filePath;
            // (Giữ lại mã gỡ lỗi nếu bạn muốn xác nhận lại, hoặc xóa đi)

            if ($isFallback) {
                // --- SỬA DÒNG NÀY ---
                // Dòng gốc gây lỗi:
                // self::$translations = array_merge_recursive($translations, self::$translations);

                // Dòng sửa lỗi sử dụng array_replace_recursive:
                // Lưu ý thứ tự: mảng ngôn ngữ chính ($self::$translations) đặt sau
                // để giá trị của nó ghi đè lên giá trị của mảng dự phòng ($translations).
                self::$translations = array_replace_recursive($translations, self::$translations);
                // --------------------

                // (Giữ lại mã gỡ lỗi nếu bạn muốn xác nhận lại, hoặc xóa đi)
            } else {
                self::$translations = $translations;
            }
        } else {
            // (Giữ lại mã gỡ lỗi nếu bạn muốn xác nhận lại, hoặc xóa đi)
        }
         // (Giữ lại mã gỡ lỗi nếu bạn muốn xác nhận lại, hoặc xóa đi)
    }
    
    // Get translated text
    public static function trans($key, $params = []) {
        $keys = explode('.', $key);
        $translation = self::$translations;
        
        foreach ($keys as $k) {
            if (isset($translation[$k])) {
                $translation = $translation[$k];
            } else {
                // Return the key itself if translation not found
                return $key;
            }
        }
        
        // Replace parameters if any
        if (is_string($translation) && !empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace(":{$param}", $value, $translation);
            }
        }
        
        return $translation;
    }
    
    // Set current language
    public static function setLanguage($language) {
        self::$currentLanguage = $language;
        self::loadTranslations($language);
    }
    
    // Get current language
    public static function getCurrentLanguage() {
        return self::$currentLanguage;
    }
    
    // Helper function for use in templates
    public static function t($key, $params = []) {
        return self::trans($key, $params);
    }
}

// Initialize language system
Language::init();

// Global helper function
function __($key, $params = []) {
    return Language::trans($key, $params);
}