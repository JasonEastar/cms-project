<?php
// config.php
// Định nghĩa BASE_URL cho môi trường local
define('BASE_URL', 'http://localhost'); // Thay bằng URL local của bạn

// Định nghĩa đường dẫn upload
define('UPLOAD_DIR', '/Applications/XAMPP/xamppfiles/htdocs/public/uploads/'); // Đường dẫn tuyệt đối
define('UPLOAD_URL', BASE_URL . '/public/uploads/'); // URL cha