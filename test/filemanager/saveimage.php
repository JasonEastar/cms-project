<?php
// Tránh lỗi imagecreatefrom... nếu file không hợp lệ
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Nếu có file được gửi lên
if (!isset($_FILES['file'])) {
    echo json_encode([
        'error' => 'No file received in saveimage.php'
    ]);
    exit;
}

$tmp = $_FILES['file']['tmp_name'];
$type = mime_content_type($tmp);

// Thử tạo resource ảnh an toàn theo loại file
switch ($type) {
    case 'image/jpeg':
        $img = @imagecreatefromjpeg($tmp);
        break;
    case 'image/png':
        $img = @imagecreatefrompng($tmp);
        break;
    case 'image/gif':
        $img = @imagecreatefromgif($tmp);
        break;
    default:
        $img = false;
}

if (!$img) {
    echo json_encode([
        'error' => 'saveImage: This is not a resource.'
    ]);
    exit;
}

// Nếu bạn thực sự muốn xử lý ảnh resize ở đây, có thể thêm sau.
// Nhưng vì bạn không cần => chỉ trả thành công giả:

echo json_encode([
    'status' => 'success',
    'message' => 'Fake thumbnail processed (resource created).'
]);
exit;
