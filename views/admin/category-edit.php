<?php
// views/admin/category-edit.php
// Giao diện thêm/sửa danh mục sử dụng PHP trực tiếp

require_once __DIR__ . '/../../utils/Language.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/Language.php';

// Kiểm tra xác thực đăng nhập
if (!isset($_COOKIE['admin_token'])) {
    header('Location: login');
    exit;
}

// Khởi tạo các models
$categoryModel = new Category();
$languageModel = new LanguageModel();

// Lấy ngôn ngữ hiện tại
$currentLang = isset($_COOKIE['admin_language']) ? $_COOKIE['admin_language'] : 'vi';

// Biến để lưu thông tin danh mục khi edit
$category = null;
$translations = [];

// Nếu đang edit, lấy thông tin danh mục
if (isset($_GET['id'])) {
    $category = $categoryModel->findById($_GET['id'], $currentLang);
    if ($category) {
        $translations = $categoryModel->getAllTranslations($_GET['id']);
    } else {
        header('Location: categories');
        exit;
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'code' => $_POST['code'] ?? '',
            'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'translations' => []
        ];
        
        // Xử lý translations
        $languages = $languageModel->getActive();
        foreach ($languages as $lang) {
            if (!empty($_POST["name_{$lang['code']}"])) {
                $data['translations'][] = [
                    'language_code' => $lang['code'],
                    'name' => $_POST["name_{$lang['code']}"],
                    'slug' => $_POST["slug_{$lang['code']}"] ?? '',
                    'description' => $_POST["description_{$lang['code']}"] ?? '',
                    'seo_title' => $_POST["seo_title_{$lang['code']}"] ?? '',
                    'seo_description' => $_POST["seo_description_{$lang['code']}"] ?? ''
                ];
            }
        }
        
        if (empty($data['translations'])) {
            throw new Exception(__('categories.translations_required'));
        }
        
        if (isset($_GET['id'])) {
            // Update
            $categoryModel->update($_GET['id'], $data);
            $_SESSION['message'] = ['type' => 'success', 'text' => __('categories.save_success')];
        } else {
            // Create
            $categoryModel->create($data);
            $_SESSION['message'] = ['type' => 'success', 'text' => __('categories.save_success')];
        }
        
        header('Location: categories');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    }
}

// Lấy danh sách ngôn ngữ hoạt động
$languages = $languageModel->getActive();

// Lấy danh sách danh mục cho dropdown parent
$categories = $categoryModel->getAllByLang($currentLang);

$page_title = isset($_GET['id']) ? __('categories.edit_category') : __('categories.add_category');
$page_title .= ' - ' . __('header.admin_cms');
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-4">
        <a href="categories" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="fas fa-arrow-left text-gray-600"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?php echo isset($_GET['id']) ? __('categories.edit_category') : __('categories.add_category'); ?></h1>
            <p class="text-gray-500 mt-1"><?php echo isset($_GET['id']) ? __('categories.edit_category') : __('categories.add_category'); ?></p>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo $_SESSION['message']['text']; ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Category Form -->
<form method="POST" action="">
    <!-- Basic Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900"><?php echo __('categories.basic_info'); ?></h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category-code" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('categories.category_code'); ?></label>
                    <input type="text" id="category-code" name="code" value="<?php echo $category ? htmlspecialchars($category['code']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.code_placeholder'); ?>">
                    <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.code_auto_generate'); ?></p>
                </div>
                <div>
                    <label for="category-parent" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('categories.category_parent'); ?></label>
                    <select id="category-parent" name="parent_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition duration-150">
                        <option value=""><?php echo __('categories.no_parent'); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!isset($_GET['id']) || $cat['id'] != $_GET['id']): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category && $category['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="category-is-active" name="is_active" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition duration-150" <?php echo (!$category || $category['is_active']) ? 'checked' : ''; ?>>
                    <span class="text-gray-700 font-medium"><?php echo __('categories.category_active'); ?></span>
                </label>
                <p class="mt-1.5 text-sm text-gray-500 ml-8"><?php echo __('categories.category_inactive_warning'); ?></p>
            </div>
        </div>
    </div>

    <!-- Multilingual Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900"><?php echo __('categories.multilingual_content'); ?></h2>
        </div>

        <!-- Language Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex overflow-x-auto px-6" id="language-tabs">
                <?php foreach ($languages as $index => $lang): ?>
                    <button type="button" class="language-tab px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200 <?php echo $index === 0 ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>" data-language="<?php echo $lang['code']; ?>">
                        <?php echo $lang['name']; ?> <?php echo $lang['is_default'] ? '<span class="text-xs text-blue-500">' . __('languages.is_default') . '</span>' : ''; ?>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Language Contents -->
        <div class="p-6" id="language-contents">
            <?php foreach ($languages as $index => $lang): ?>
                <?php
                // Lấy dữ liệu translation cho ngôn ngữ này
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
                            <label for="category-name-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo __('categories.category_name'); ?> (<?php echo $lang['name']; ?>)
                                <?php if ($lang['is_default']): ?><span class="text-red-500">*</span><?php endif; ?>
                            </label>
                            <input type="text" id="category-name-<?php echo $lang['code']; ?>" name="name_<?php echo $lang['code']; ?>" value="<?php echo $translation ? htmlspecialchars($translation['name']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.name_placeholder'); ?>" <?php echo $lang['is_default'] ? 'required' : ''; ?>>
                        </div>
                        
                        <div>
                            <label for="category-slug-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo __('categories.slug'); ?> (<?php echo $lang['name']; ?>)
                            </label>
                            <input type="text" id="category-slug-<?php echo $lang['code']; ?>" name="slug_<?php echo $lang['code']; ?>" value="<?php echo $translation ? htmlspecialchars($translation['slug']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.slug_placeholder'); ?>">
                            <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.slug_hint'); ?></p>
                        </div>
                        
                        <div>
                            <label for="category-description-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo __('common.description'); ?> (<?php echo $lang['name']; ?>)
                            </label>
                            <textarea id="category-description-<?php echo $lang['code']; ?>" name="description_<?php echo $lang['code']; ?>" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.description_placeholder'); ?>"><?php echo $translation ? htmlspecialchars($translation['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo __('categories.seo_optimization'); ?></h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="category-seo-title-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php echo __('categories.seo_title'); ?> (<?php echo $lang['name']; ?>)
                                    </label>
                                    <input type="text" id="category-seo-title-<?php echo $lang['code']; ?>" name="seo_title_<?php echo $lang['code']; ?>" value="<?php echo $translation ? htmlspecialchars($translation['seo_title']) : ''; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.seo_title_placeholder'); ?>">
                                    <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.seo_title_hint'); ?></p>
                                </div>
                                
                                <div>
                                    <label for="category-seo-description-<?php echo $lang['code']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php echo __('categories.seo_description'); ?> (<?php echo $lang['name']; ?>)
                                    </label>
                                    <textarea id="category-seo-description-<?php echo $lang['code']; ?>" name="seo_description_<?php echo $lang['code']; ?>" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.seo_description_placeholder'); ?>"><?php echo $translation ? htmlspecialchars($translation['seo_description']) : ''; ?></textarea>
                                    <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.seo_description_hint'); ?></p>
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
        <a href="categories" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
            <i class="fas fa-times mr-2"></i>
            <?php echo __('common.cancel'); ?>
        </a>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
            <i class="fas fa-save mr-2"></i>
            <?php echo __('common.save'); ?>
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Language tab switching
    $('#language-tabs').on('click', '.language-tab', function() {
        // Remove active class from all tabs
        $('.language-tab').removeClass('border-blue-500 text-blue-600').addClass('border-transparent text-gray-500');
        
        // Add active class to clicked tab
        $(this).removeClass('border-transparent text-gray-500').addClass('border-blue-500 text-blue-600');
        
        // Hide all content
        $('.language-content').addClass('hidden');
        
        // Show selected content
        const langCode = $(this).data('language');
        $(`.language-content[data-language="${langCode}"]`).removeClass('hidden');
    });
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>