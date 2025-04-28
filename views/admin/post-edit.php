<?php
// views/admin/post-edit.php
require_once __DIR__ . '/../../utils/Language.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/Language.php';

// Kiểm tra xác thực đăng nhập
if (!isset($_COOKIE['admin_token'])) {
    header('Location: login');
    exit;
}

// Khởi tạo models
$postModel = new Post();
$categoryModel = new Category();
$languageModel = new LanguageModel();

// Lấy ngôn ngữ hiện tại
$currentLang = isset($_COOKIE['admin_language']) ? $_COOKIE['admin_language'] : 'vi';

// Biến lưu thông tin bài viết
$post = null;
$translations = [];

// Nếu đang chỉnh sửa, lấy thông tin bài viết
if (isset($_GET['id'])) {
    $post = $postModel->findById($_GET['id'], $currentLang);
    if ($post) {
        $translations = $postModel->getAllTranslations($_GET['id']);
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Bài viết không tồn tại'];
        header('Location: posts');
        exit;
    }
}

// Lấy danh sách danh mục và ngôn ngữ
$categories = $categoryModel->getAllByLang($currentLang);
$languages = $languageModel->getActive();

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'code' => $_POST['code'] ?? '',
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'published_at' => !empty($_POST['published_at']) ? $_POST['published_at'] : null,
            'translations' => []
        ];

        // Xử lý ảnh đại diện
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = rtrim(UPLOAD_DIR, '/') . '/post/'; // Thư mục: /Applications/XAMPP/xamppfiles/htdocs/public/uploads/post/

            // Debug đường dẫn
            error_log("UPLOAD_DIR: " . UPLOAD_DIR);
            error_log("uploadDir: " . $uploadDir);
            error_log("is_dir(UPLOAD_DIR): " . (is_dir(UPLOAD_DIR) ? 'true' : 'false'));
            error_log("is_writable(UPLOAD_DIR): " . (is_writable(UPLOAD_DIR) ? 'true' : 'false'));

            // Kiểm tra thư mục cha
            if (!is_dir(UPLOAD_DIR)) {
                error_log("Thư mục cha không tồn tại: " . UPLOAD_DIR);
                throw new Exception('Thư mục public/uploads không tồn tại.');
            }
            if (!is_writable(UPLOAD_DIR)) {
                error_log("Thư mục cha không ghi được: " . UPLOAD_DIR);
                throw new Exception('Thư mục public/uploads không có quyền ghi.');
            }

            // Đảm bảo thư mục post/ tồn tại
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $error = error_get_last();
                    error_log("Không thể tạo thư mục: $uploadDir. Lỗi: " . ($error['message'] ?? 'Không xác định'));
                    throw new Exception('Không thể tạo thư mục uploads/post. Kiểm tra quyền thư mục public/uploads.');
                }
                if (!chmod($uploadDir, 0755)) {
                    error_log("Không thể đặt quyền cho thư mục: $uploadDir");
                    throw new Exception('Không thể đặt quyền cho thư mục uploads/post.');
                }
                error_log("Thư mục được tạo: $uploadDir");
            }

            // Kiểm tra quyền ghi
            if (!is_writable($uploadDir)) {
                error_log("Thư mục không ghi được: $uploadDir");
                throw new Exception('Thư mục uploads/post không có quyền ghi.');
            }

            // Kiểm tra loại file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            $fileType = mime_content_type($_FILES['thumbnail']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Chỉ chấp nhận file ảnh JPG, PNG, GIF hoặc SVG');
            }

            // Kiểm tra kích thước file
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['thumbnail']['size'] > $maxSize) {
                throw new Exception('Kích thước file không được vượt quá 5MB');
            }

            $fileName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadPath)) {
                $data['thumbnail'] = rtrim(UPLOAD_URL, '/') . '/post/' . $fileName; // URL: http://localhost/uploads/post/xxx.jpg
            } else {
                error_log("Không thể lưu file vào: $uploadPath");
                throw new Exception('Không thể lưu file ảnh. Kiểm tra quyền thư mục uploads/post hoặc cấu hình PHP.');
            }
        }

        // Xử lý translations
        foreach ($languages as $lang) {
            if (!empty($_POST["title_{$lang['code']}"])) {
                $data['translations'][] = [
                    'language_code' => $lang['code'],
                    'title' => $_POST["title_{$lang['code']}"],
                    'slug' => $_POST["slug_{$lang['code']}"] ?? '',
                    'summary' => $_POST["summary_{$lang['code']}"] ?? '',
                    'content' => $_POST["content_{$lang['code']}"] ?? '',
                    'seo_title' => $_POST["seo_title_{$lang['code']}"] ?? '',
                    'seo_description' => $_POST["seo_description_{$lang['code']}"] ?? ''
                ];
            }
        }

        if (empty($data['translations'])) {
            throw new Exception('Vui lòng nhập tiêu đề cho ít nhất một ngôn ngữ');
        }

        if (isset($_GET['id'])) {
            // Cập nhật
            $postModel->update($_GET['id'], $data);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Cập nhật bài viết thành công'];
        } else {
            // Tạo mới
            $postModel->create($data);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Tạo bài viết thành công'];
        }

        header('Location: posts');
        exit;
    } catch (Exception $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    }
}

$page_title = isset($_GET['id']) ? 'Chỉnh sửa bài viết - Admin CMS' : 'Thêm bài viết mới - Admin CMS';
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-4">
        <a href="posts" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="fas fa-arrow-left text-gray-600"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <?php echo isset($_GET['id']) ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới'; ?>
            </h1>
            <p class="text-gray-500 mt-1">
                <?php echo isset($_GET['id']) ? 'Cập nhật thông tin bài viết' : 'Tạo bài viết mới cho website'; ?>
            </p>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo $_SESSION['message']['text']; ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Post Form -->
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" id="post-id" value="<?php echo $_GET['id'] ?? ''; ?>">

    <!-- Basic Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Thông tin cơ bản</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="post-code" class="block text-sm font-medium text-gray-700 mb-1">Mã bài viết</label>
                    <input type="text" id="post-code" name="code" value="<?php echo $post ? htmlspecialchars($post['code']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Nhập mã bài viết">
                    <p class="mt-1.5 text-sm text-gray-500">Để trống sẽ tự động tạo từ tiêu đề</p>
                </div>
                <div>
                    <label for="post-category" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                    <select id="post-category" name="category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition duration-150">
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($post && $post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="post-published-at" class="block text-sm font-medium text-gray-700 mb-1">Ngày xuất bản</label>
                    <input type="datetime-local" id="post-published-at" name="published_at" value="<?php echo $post && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện</label>
                    <div class="mt-1 flex items-center gap-4">
                        <img id="thumbnail-preview" src="<?php echo $post && $post['thumbnail'] ? $post['thumbnail'] : '/api/placeholder/400/300'; ?>" alt="Thumbnail preview" class="w-32 h-24 object-cover rounded-lg">
                        <label class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <i class="fas fa-upload mr-2"></i>
                            Chọn ảnh
                            <input type="file" id="post-thumbnail" name="thumbnail" class="hidden" accept="image/*">
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6 mt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="post-is-active" name="is_active" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition duration-150" <?php echo (!$post || $post['is_active']) ? 'checked' : ''; ?>>
                    <span class="text-gray-700 font-medium">Xuất bản</span>
                </label>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="post-is-featured" name="is_featured" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition duration-150" <?php echo ($post && $post['is_featured']) ? 'checked' : ''; ?>>
                    <span class="text-gray-700 font-medium">Nổi bật</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Multilingual Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Nội dung đa ngôn ngữ</h2>
        </div>

        <!-- Language Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex overflow-x-auto px-6" id="language-tabs">
                <?php foreach ($languages as $index => $lang): ?>
                    <button type="button" class="language-tab px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200 <?php echo $index === 0 ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>" data-language="<?php echo $lang['code']; ?>">
                        <?php echo $lang['name']; ?> <?php echo $lang['is_default'] ? '<span class="text-xs text-blue-500">(Mặc định)</span>' : ''; ?>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Language Contents -->
        <div class="p-6" id="language-contents">
            <?php foreach ($languages as $index => $lang): ?>
                <?php
                $translation = null;
                if ($translations) {
                    foreach ($translations as $trans) {
                        if ($trans['language_code'] === $lang['code']) {
                            $translation = $trans;
                            break;
                        }
                    }
                }
                ?>
                <div class="language-content <?php echo $index !== 0 ? 'hidden' : ''; ?>" data-language="<?php echo $lang['code']; ?>">
                    <div class="space-y-6">
                        <div>
                            <label for="post-title-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                Tiêu đề (<?php echo $lang['name']; ?>)
                                <?php if ($lang['is_default']): ?><span class="text-red-500">*</span><?php endif; ?>
                            </label>
                            <input type="text" id="post-title-<?php echo $lang['code']; ?>" name="title_<?php echo $lang['code']; ?>" value="<?php echo $translation ? htmlspecialchars($translation['title']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Nhập tiêu đề bài viết" <?php echo $lang['is_default'] ? 'required' : ''; ?>>
                        </div>

                        <div>
                            <label for="post-slug-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                Đường dẫn URL (<?php echo $lang['name']; ?>)
                            </label>
                            <input type="text" id="post-slug-<?php echo $lang['code']; ?>" name="slug_<?php echo $lang['code']; ?>" value="<?php echo $translation ? htmlspecialchars($translation['slug']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Để trống sẽ tự động tạo từ tiêu đề">
                            <p class="mt-1.5 text-sm text-gray-500">Đường dẫn thân thiện SEO cho bài viết này</p>
                        </div>

                        <div>
                            <label for="post-summary-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                Tóm tắt (<?php echo $lang['name']; ?>)
                            </label>
                            <textarea id="post-summary-<?php echo $lang['code']; ?>" name="summary_<?php echo $lang['code']; ?>" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Nhập tóm tắt ngắn gọn về bài viết"><?php echo $translation ? htmlspecialchars($translation['summary']) : ''; ?></textarea>
                        </div>

                        <div>
                            <label for="post-content-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                Nội dung (<?php echo $lang['name']; ?>)
                            </label>
                            <div id="post-content-<?php echo $lang['code']; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm">
                                <?php echo $translation ? htmlspecialchars($translation['content']) : ''; ?>
                            </div>
                            <textarea id="post-content-textarea-<?php echo $lang['code']; ?>" name="content_<?php echo $lang['code']; ?>" class="hidden"><?php echo $translation ? htmlspecialchars($translation['content']) : ''; ?></textarea>
                        </div>

                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tối ưu SEO</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="post-seo-title-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                        Tiêu đề SEO (<?php echo $lang['name']; ?>)
                                    </label>
                                    <input type="text" id="post-seo-title-<?php echo $lang['code']; ?>" name="seo_title_<?php echo $lang['code']; ?>" value="<?php echo $translation ? htmlspecialchars($translation['seo_title']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Tiêu đề hiển thị trên kết quả tìm kiếm">
                                    <p class="mt-1.5 text-sm text-gray-500">Nên dài 50-60 ký tự</p>
                                </div>
                                <div>
                                    <label for="post-seo-description-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                        Mô tả SEO (<?php echo $lang['name']; ?>)
                                    </label>
                                    <textarea id="post-seo-description-<?php echo $lang['code']; ?>" name="seo_description_<?php echo $lang['code']; ?>" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Mô tả hiển thị trên kết quả tìm kiếm"><?php echo $translation ? htmlspecialchars($translation['seo_description']) : ''; ?></textarea>
                                    <p class="mt-1.5 text-sm text-gray-500">Nên dài 120-160 ký tự</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="mt-8 flex items-center justify-end gap-4">
        <a href="posts" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
            <i class="fas fa-times mr-2"></i>
            Hủy
        </a>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
            <i class="fas fa-save mr-2"></i>
            Lưu bài viết
        </button>
    </div>
</form>

<!-- Include CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/translations/vi.js"></script>

<style>
    .border-red-500 {
        border-color: #ef4444 !important;
    }

    .border-red-500:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
    }
</style>

<script>
    $(document).ready(function() {
        let editorInstances = {};

        // Initialize CKEditor for content editors
        <?php foreach ($languages as $lang): ?>
            ClassicEditor
                .create(document.getElementById('post-content-<?php echo $lang['code']; ?>'), {
                    language: '<?php echo $lang['code'] === 'vi' ? 'vi' : 'en'; ?>',
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'link', 'blockQuote', 'insertTable', '|',
                            'bulletedList', 'numberedList', '|',
                            'outdent', 'indent', '|',
                            'undo', 'redo'
                        ]
                    },
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                    },
                    link: {
                        addTargetToExternalLinks: true,
                        defaultProtocol: 'https://'
                    },
                    removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed']
                })
                .then(editor => {
                    editorInstances['post-content-<?php echo $lang['code']; ?>'] = editor;
                    editor.model.document.on('change:data', () => {
                        document.getElementById('post-content-textarea-<?php echo $lang['code']; ?>').value = editor.getData();
                    });
                })
                .catch(error => console.error('Error initializing CKEditor:', error));
        <?php endforeach; ?>

        // Language tab switching
        $('#language-tabs').on('click', '.language-tab', function() {
            $('.language-tab').removeClass('border-blue-500 text-blue-600').addClass('border-transparent text-gray-500');
            $(this).removeClass('border-transparent text-gray-500').addClass('border-blue-500 text-blue-600');
            $('.language-content').addClass('hidden');
            const langCode = $(this).data('language');
            $(`.language-content[data-language="${langCode}"]`).removeClass('hidden');
        });

        // Thumbnail preview
        $('#post-thumbnail').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#thumbnail-preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Set default datetime
        if (!$('#post-published-at').val()) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            $('#post-published-at').val(now.toISOString().slice(0, 16));
        }
    });
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>