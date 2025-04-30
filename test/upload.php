<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload failed']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    $uploadUrl = 'uploads/'; // không có dấu / đầu

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        // Trả về đúng URL trong thư mục test/
        echo json_encode([
            'location' => 'uploads/' . $fileName // không có dấu / đầu
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
