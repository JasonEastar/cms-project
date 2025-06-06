<?php
// views/admin/categories.php
// Giao diện quản lý danh mục sử dụng PHP trực tiếp

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

// Xử lý xóa danh mục nếu có yêu cầu
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    try {
        $categoryModel->delete($_POST['id']);
        $_SESSION['message'] = ['type' => 'success', 'text' => __('categories.delete_success')];
    } catch (Exception $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    }
    header('Location: categories');
    exit;
}

// Lấy danh sách danh mục
$categories = $categoryModel->getAllByLang($currentLang);

// Lấy danh sách ngôn ngữ hoạt động
$languages = $languageModel->getActive();

$page_title = __('categories.title') . ' - ' . __('header.admin_cms');
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo __('categories.title'); ?></h1>
            <p class="mt-2 text-gray-600"><?php echo __('categories.subtitle'); ?></p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="category-edit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                <?php echo __('categories.add_category'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900"><?php echo __('categories.filter_search'); ?></h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <label for="parent-filter" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('categories.category_parent'); ?></label>
                <select id="parent-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value=""><?php echo __('categories.all_categories'); ?></option>
                    <option value="0"><?php echo __('categories.root_categories_only'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('common.status'); ?></label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value=""><?php echo __('categories.all_statuses'); ?></option>
                    <option value="1"><?php echo __('common.active'); ?></option>
                    <option value="0"><?php echo __('common.inactive'); ?></option>
                </select>
            </div>
            <div>
                <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('common.search'); ?></label>
                <div class="relative">
                    <input type="text" id="search-filter" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('categories.search_placeholder'); ?>">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-end">
                <button id="apply-filter-btn" class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                    <i class="fas fa-filter mr-2"></i>
                    <?php echo __('common.apply'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Categories Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?php echo __('common.id'); ?></th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?php echo __('categories.category_name'); ?></th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?php echo __('categories.category_code'); ?></th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?php echo __('categories.category_parent'); ?></th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><?php echo __('common.status'); ?></th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"><?php echo __('common.actions'); ?></th>
                </tr>
            </thead>
            <tbody id="categories-list" class="bg-white divide-y divide-gray-200">
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-500 font-medium"><?php echo __('common.no_data'); ?></p>
                                <p class="text-gray-400 text-sm mt-1"><?php echo __('categories.create_first_category'); ?></p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $parentCategory = null;
                        if ($category['parent_id']) {
                            $parentCategory = $categoryModel->findById($category['parent_id'], $currentLang);
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">#<?php echo $category['id']; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="px-2 py-1 text-sm bg-gray-100 text-gray-700 rounded font-mono"><?php echo htmlspecialchars($category['code']); ?></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600"><?php echo $parentCategory ? htmlspecialchars($parentCategory['name']) : '-'; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium <?php echo $category['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                    <i class="fas fa-circle text-xs"></i>
                                    <?php echo $category['is_active'] ? __('common.active') : __('common.inactive'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <a href="category-edit?id=<?php echo $category['id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                                    <i class="fas fa-edit mr-1"></i>
                                    <?php echo __('common.edit'); ?>
                                </a>
                                <button class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150 delete-category-btn" 
                                        data-id="<?php echo $category['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                    <i class="fas fa-trash mr-1"></i>
                                    <?php echo __('common.delete'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white px-6 py-8 shadow-2xl transition-all w-full max-w-md">
                <div class="flex items-center justify-center w-14 h-14 mx-auto bg-red-100 rounded-full mb-5">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-center text-gray-900 mb-2"><?php echo __('categories.confirm_delete_title'); ?></h3>
                <p class="text-center text-gray-500 mb-6">
                    <?php echo __('messages.are_you_sure'); ?>
                    <span id="delete-category-name" class="font-semibold text-gray-900"></span>?
                    <br>
                    <span class="text-sm mt-1 block text-gray-400"><?php echo __('categories.confirm_delete_message'); ?></span>
                </p>
                <div class="flex gap-3 mt-8">
                    <button type="button" class="flex-1 px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 close-modal">
                        <?php echo __('common.cancel'); ?>
                    </button>
                    <form id="delete-form" method="POST" class="flex-1">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-category-id">
                        <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <?php echo __('common.delete'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Dữ liệu categories đã có sẵn từ PHP
    let categories = <?php echo json_encode($categories); ?>;
    
    // Event handlers
    $('.close-modal').click(closeModals);
    $('#apply-filter-btn').click(applyFilters);
    
    // Delete category button click
    $('.delete-category-btn').click(function() {
        const categoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        confirmDelete(categoryId, categoryName);
    });
    
    // Confirm delete
    function confirmDelete(categoryId, categoryName) {
        $('#delete-category-name').text(categoryName);
        $('#delete-category-id').val(categoryId);
        $('#delete-modal').removeClass('hidden');
    }
    
    // Apply filters
    function applyFilters() {
        const parentId = $('#parent-filter').val();
        const statusFilter = $('#status-filter').val();
        const searchTerm = $('#search-filter').val().toLowerCase();
        
        let filteredCategories = categories;
        
        if (parentId) {
            if (parentId === '0') {
                filteredCategories = filteredCategories.filter(c => !c.parent_id);
            } else {
                filteredCategories = filteredCategories.filter(c => c.parent_id == parentId);
            }
        }
        
        if (statusFilter !== '') {
            filteredCategories = filteredCategories.filter(c => c.is_active == statusFilter);
        }
        
        if (searchTerm) {
            filteredCategories = filteredCategories.filter(c => 
                c.name.toLowerCase().includes(searchTerm) ||
                c.code.toLowerCase().includes(searchTerm)
            );
        }
        
        renderCategories(filteredCategories);
    }
    
    // Close modals
    function closeModals() {
        $('#delete-modal').addClass('hidden');
    }
    
    // Render categories (client-side filtering)
    function renderCategories(categoriesData) {
        const tbody = $('#categories-list');
        tbody.empty();
        
        if (categoriesData.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="px-6 py-12">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium"><?php echo __('common.no_data'); ?></p>
                            <p class="text-gray-400 text-sm mt-1"><?php echo __('categories.create_first_category'); ?></p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        
        categoriesData.forEach(category => {
            const parentCategory = categories.find(c => c.id === category.parent_id);
            
            const row = `
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">#${category.id}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${category.name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <code class="px-2 py-1 text-sm bg-gray-100 text-gray-700 rounded font-mono">${category.code}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">${parentCategory ? parentCategory.name : '-'}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium ${category.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                            <i class="fas fa-circle text-xs"></i>
                            ${category.is_active ? '<?php echo __('common.active'); ?>' : '<?php echo __('common.inactive'); ?>'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                        <a href="category-edit?id=${category.id}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                            <i class="fas fa-edit mr-1"></i>
                            <?php echo __('common.edit'); ?>
                        </a>
                        <button class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150 delete-category-btn" 
                                data-id="${category.id}" 
                                data-name="${category.name}">
                            <i class="fas fa-trash mr-1"></i>
                            <?php echo __('common.delete'); ?>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Re-attach delete event handlers
        $('.delete-category-btn').click(function() {
            const categoryId = $(this).data('id');
            const categoryName = $(this).data('name');
            confirmDelete(categoryId, categoryName);
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>