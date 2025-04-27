<?php
// utils/View.php
// Lớp xử lý hiển thị view (template)

class View {
    /**
     * Đường dẫn đến thư mục chứa các view
     * @var string
     */
    private static $viewPath = __DIR__ . '/../views/';
    
    /**
     * Render một view với dữ liệu được truyền vào
     * 
     * @param string $view Đường dẫn đến view (không bao gồm .php)
     * @param array $data Dữ liệu cần truyền vào view
     * @return void
     */
    public static function render($view, $data = []) {
        // Kiểm tra xem view có tồn tại không
        $viewFile = self::$viewPath . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View '{$view}' không tồn tại.");
        }
        
        // Extract dữ liệu để sử dụng trong view
        extract($data);
        
        // Bắt đầu output buffering
        ob_start();
        
        // Include view
        include $viewFile;
        
        // Lấy nội dung từ buffer và hiển thị
        echo ob_get_clean();
    }
    
    /**
     * Kiểm tra xem view có tồn tại không
     * 
     * @param string $view Đường dẫn đến view (không bao gồm .php)
     * @return boolean
     */
    public static function exists($view) {
        return file_exists(self::$viewPath . $view . '.php');
    }
    
    /**
     * Lấy URL đầy đủ (có chứa basePath) của một trang
     * 
     * @param string $path Đường dẫn tương đối
     * @return string URL đầy đủ
     */
    public static function url($path = '') {
        $basePath = '';
        return $basePath . '/' . ltrim($path, '/');
    }
    
    /**
     * Lấy URL đầy đủ (có chứa basePath) của một file tài nguyên (CSS, JS, image)
     * 
     * @param string $path Đường dẫn tương đối đến file
     * @return string URL đầy đủ đến file
     */
    public static function asset($path) {
        return self::url('assets/' . ltrim($path, '/'));
    }
    
    /**
     * Bao bọc nội dung view trong một layout
     * 
     * @param string $layout Tên layout
     * @param string $view Tên view
     * @param array $data Dữ liệu cần truyền vào view và layout
     * @return void
     */
    public static function renderWithLayout($layout, $view, $data = []) {
        // Render view thành một biến content
        ob_start();
        self::render($view, $data);
        $content = ob_get_clean();
        
        // Thêm biến content vào dữ liệu
        $data['content'] = $content;
        
        // Render layout với dữ liệu đã cập nhật
        self::render('layouts/' . $layout, $data);
    }
}