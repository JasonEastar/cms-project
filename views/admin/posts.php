<?php
// views/admin/posts.php
require_once __DIR__ . '/../../utils/Language.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/Language.php';

// Kiểm tra xác thực đăng nhập
if (!isset($_COOKIE['admin_token'])) {
    header('Location: login');
    exit;
}

// Khởi tạo các models
$postModel = new Post();
$categoryModel = new Category();
$languageModel = new LanguageModel();

// Lấy ngôn ngữ hiện tại
$currentLang = isset($_COOKIE['admin_language']) ? $_COOKIE['admin_language'] : 'vi';

// Xử lý xóa bài viết
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    try {
        $postModel->delete($_POST['id']);
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Xóa bài viết thành công'];
    } catch (Exception $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    }
    header('Location: posts');
    exit;
}

// Lấy các tham số lọc
$filters = [];
if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
    $filters['category_id'] = $_GET['category_id'];
}
if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
    $filters['is_active'] = $_GET['is_active'];
}
if (isset($_GET['is_featured']) && $_GET['is_featured'] !== '') {
    $filters['is_featured'] = $_GET['is_featured'];
}

// Lấy danh sách bài viết và danh mục
$posts = $postModel->getAllByLang($currentLang, $filters);
$categories = $categoryModel->getAllByLang($currentLang);

$page_title = 'Quản lý bài viết - Admin CMS';
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Quản lý bài viết</h1>
            <p class="mt-2 text-gray-600">Quản lý bài viết đa ngôn ngữ cho website</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="post-edit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Thêm bài viết mới
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo $_SESSION['message']['text']; ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Bộ lọc tìm kiếm</h3>
    </div>
    <div class="p-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                <select id="category-filter" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select id="status-filter" name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" <?php echo (isset($_GET['is_active']) && $_GET['is_active'] == '1') ? 'selected' : ''; ?>>Đã xuất bản</option>
                    <option value="0" <?php echo (isset($_GET['is_active']) && $_GET['is_active'] == '0') ? 'selected' : ''; ?>>Nháp</option>
                </select>
            </div>
            <div>
                <label for="featured-filter" class="block text-sm font-medium text-gray-700 mb-1">Loại bài viết</label>
                <select id="featured-filter" name="is_featured" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả</option>
                    <option value="1" <?php echo (isset($_GET['is_featured']) && $_GET['is_featured'] == '1') ? 'selected' : ''; ?>>Nổi bật</option>
                    <option value="0" <?php echo (isset($_GET['is_featured']) && $_GET['is_featured'] == '0') ? 'selected' : ''; ?>>Thường</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                    <i class="fas fa-filter mr-2"></i>
                    Áp dụng bộ lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hình ảnh</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tiêu đề</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Danh mục</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ngày xuất bản</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lượt xem</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Trạng thái</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Không có bài viết nào</p>
                                <p class="text-gray-400 text-sm mt-1">Nhấn nút "Thêm bài viết mới" để tạo bài viết đầu tiên</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="<?php echo $post['thumbnail'] ? $post['thumbnail'] : '/api/placeholder/400/300'; ?>" alt="" class="w-16 h-12 object-cover rounded">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($post['title']); ?></div>
                                <div class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($post['code']); ?></div>
                                <?php if ($post['is_featured']): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">Nổi bật</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600"><?php echo $post['category_name'] ?? 'Chưa phân loại'; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">
                                    <?php echo $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : 'Chưa xuất bản'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600"><?php echo $post['views']; ?> lượt xem</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium <?php echo $post['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <i class="fas fa-circle text-xs"></i>
                                    <?php echo $post['is_active'] ? 'Đã xuất bản' : 'Nháp'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <a href="post-edit?id=<?php echo $post['id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                                    <i class="fas fa-edit mr-1"></i>
                                    Sửa
                                </a>
                                <button class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150 delete-post-btn" 
                                        data-id="<?php echo $post['id']; ?>" 
                                        data-title="<?php echo htmlspecialchars($post['title']); ?>">
                                    <i class="fas fa-trash mr-1"></i>
                                    Xóa
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
                <h3 class="text-xl font-semibold text-center text-gray-900 mb-2">Xác nhận xóa bài viết</h3>
                <p class="text-center text-gray-500 mb-6">
                    Bạn có chắc chắn muốn xóa bài viết 
                    <span id="delete-post-title" class="font-semibold text-gray-900"></span>?
                    <br>
                    <span class="text-sm mt-1 block text-gray-400">Hành động Akismet này sẽ xóa tất cả bản dịch và không thể hoàn tác.</span>
                </p>
                <div class="flex gap-3 mt-8">
                    <button type="button" class="flex-1 px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 close-modal">
                        Hủy
                    </button>
                    <form id="delete-form" method="POST" class="flex-1">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-post-id">
                        <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            Xóa bài viết
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.delete-post-btn').click(function() {
        const postId = $(this).data('id');
        const postTitle = $(this).data('title');
        $('#delete-post-title').text(postTitle);
        $('#delete-post-id').val(postId);
        $('#delete-modal').removeClass('hidden');
    });

    $('.close-modal').click(function() {
        $('#delete-modal').addClass('hidden');
    });
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>