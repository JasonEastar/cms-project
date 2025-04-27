<?php
// create-uploads-dir.php
// File tạo thư mục uploads với quyền phù hợp

// Đường dẫn gốc của thư mục uploads
$baseDir = __DIR__ . '/uploads';

// Các thư mục con cần tạo
$directories = [
    $baseDir,
    $baseDir . '/sections',
    $baseDir . '/section-items',
    $baseDir . '/posts',
    $baseDir . '/categories',
    $baseDir . '/flags'
];

// Tạo các thư mục
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        $oldumask = umask(0);
        if (mkdir($dir, 0777, true)) {
            echo "Created directory: " . $dir . "\n";
        } else {
            echo "Failed to create directory: " . $dir . "\n";
        }
        umask($oldumask);
    } else {
        echo "Directory already exists: " . $dir . "\n";
    }
    
    // Đảm bảo quyền ghi
    chmod($dir, 0777);
}

// Tạo file .htaccess để bảo vệ thư mục
$htaccessContent = <<<EOT
# Prevent directory listing
Options -Indexes

# Allow access to files
<FilesMatch "\.(jpg|jpeg|png|gif|svg|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOT;

file_put_contents($baseDir . '/.htaccess', $htaccessContent);

echo "Setup completed!\n";