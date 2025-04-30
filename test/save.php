<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';

    // Ví dụ: lưu vào file tạm, bạn có thể lưu vào CSDL sau
    file_put_contents(__DIR__ . '/saved_content.html', $content);

    echo "<h2>Đã lưu bài viết!</h2>";
    echo "<div style='border:1px solid #ccc; padding:10px;'>" . $content . "</div>";
} else {
    echo "Truy cập không hợp lệ.";
}
?>
