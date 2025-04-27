<?php
// views/admin/posts.php
// Giao diện quản lý bài viết với hỗ trợ đa ngôn ngữ

require_once __DIR__ . '/../../utils/Language.php';

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

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Bộ lọc tìm kiếm</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả danh mục</option>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1">Đã xuất bản</option>
                    <option value="0">Nháp</option>
                </select>
            </div>
            <div>
                <label for="featured-filter" class="block text-sm font-medium text-gray-700 mb-1">Loại bài viết</label>
                <select id="featured-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả</option>
                    <option value="1">Nổi bật</option>
                    <option value="0">Thường</option>
                </select>
            </div>
            <div>
                <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <div class="relative">
                    <input type="text" id="search-filter" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nhập tiêu đề bài viết...">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button id="apply-filter-btn" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                <i class="fas fa-filter mr-2"></i>
                Áp dụng bộ lọc
            </button>
        </div>
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
            <tbody id="posts-list" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="7" class="px-6 py-12">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Đang tải dữ liệu...</p>
                        </div>
                    </td>
                </tr>
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
                    <span class="text-sm mt-1 block text-gray-400">Hành động này sẽ xóa tất cả bản dịch và không thể hoàn tác.</span>
                </p>
                <div class="flex gap-3 mt-8">
                    <button type="button" class="flex-1 px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 close-modal">
                        Hủy
                    </button>
                    <button type="button" id="confirm-delete-btn" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        Xóa bài viết
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const token = localStorage.getItem('admin_token');
    let posts = [];
    let categories = [];
    let currentLang = localStorage.getItem('admin_language') || 'vi';
    
    // Initialize
    fetchCategories();
    fetchPosts(currentLang);
    
    // Event handlers
    $('.close-modal').click(closeModals);
    $('#confirm-delete-btn').click(deletePost);
    $('#apply-filter-btn').click(applyFilters);
    
    // Fetch categories for filter
    function fetchCategories() {
        fetch(`../api/posts/categories?lang=${currentLang}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                categories = data.data.categories || [];
                updateCategoryFilter();
            }
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
        });
    }
    
    // Update category filter dropdown
    function updateCategoryFilter() {
        const categoryFilter = $('#category-filter');
        const options = categories.map(category => `
            <option value="${category.id}">${category.name}</option>
        `).join('');
        
        categoryFilter.html(`
            <option value="">Tất cả danh mục</option>
            ${options}
        `);
    }
    
    // Fetch posts
    function fetchPosts(lang, filters = {}) {
        showLoadingState();
        
        const queryParams = new URLSearchParams({
            lang: lang,
            ...filters
        });
        
        fetch(`../api/posts?${queryParams}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                posts = data.data.posts || [];
                renderPosts(posts);
            } else {
                showErrorState(data.message || 'Không thể tải danh sách bài viết');
            }
        })
        .catch(error => {
            console.error('Error fetching posts:', error);
            showErrorState('Lỗi kết nối: ' + error.message);
        });
    }
    
    // Render posts table
    function renderPosts(postsData) {
        if (postsData.length === 0) {
            $('#posts-list').html(`
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
            `);
            return;
        }
        
        const rows = postsData.map(post => {
            const publishedDate = post.published_at ? new Date(post.published_at).toLocaleDateString('vi-VN') : 'Chưa xuất bản';
            const thumbnailUrl = post.thumbnail || '/api/placeholder/400/300';
            
            return `
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <img src="${thumbnailUrl}" alt="" class="w-16 h-12 object-cover rounded">
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${post.title}</div>
                        <div class="text-sm text-gray-500 mt-1">${post.code}</div>
                        ${post.is_featured ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">Nổi bật</span>' : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">${post.category_name || 'Chưa phân loại'}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">${publishedDate}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">${post.views} lượt xem</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium ${post.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                            <i class="fas fa-circle text-xs"></i>
                            ${post.is_active ? 'Đã xuất bản' : 'Nháp'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                        <a href="post-edit?id=${post.id}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                            <i class="fas fa-edit mr-1"></i>
                            Sửa
                        </a>
                        <button class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors duration-150 delete-post-btn" 
                                data-id="${post.id}" 
                                data-title="${post.title}">
                            <i class="fas fa-trash mr-1"></i>
                            Xóa
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        $('#posts-list').html(rows);
        attachEventHandlers();
    }
    
    // Attach event handlers
    function attachEventHandlers() {
        $('.delete-post-btn').click(function() {
            const postId = $(this).data('id');
            const postTitle = $(this).data('title');
            confirmDelete(postId, postTitle);
        });
    }
    
    // Confirm delete
    function confirmDelete(postId, postTitle) {
        $('#delete-post-title').text(postTitle);
        $('#confirm-delete-btn').data('id', postId);
        $('#delete-modal').removeClass('hidden');
    }
    
    // Delete post
    function deletePost() {
        const postId = $(this).data('id');
        const deleteBtn = $(this);
        
        deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang xóa...');
        
        fetch(`../api/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                showToast('success', result.message || 'Xóa bài viết thành công');
                closeModals();
                fetchPosts(currentLang);
            } else {
                showToast('error', result.message || 'Không thể xóa bài viết');
                deleteBtn.prop('disabled', false).html('Xóa bài viết');
            }
        })
        .catch(error => {
            console.error('Error deleting post:', error);
            showToast('error', 'Lỗi kết nối: ' + error.message);
            deleteBtn.prop('disabled', false).html('Xóa bài viết');
        });
    }
    
    // Apply filters
    function applyFilters() {
        const filters = {
            category_id: $('#category-filter').val(),
            is_active: $('#status-filter').val(),
            is_featured: $('#featured-filter').val()
        };
        
        const searchTerm = $('#search-filter').val().toLowerCase();
        if (searchTerm) {
            filters.search = searchTerm;
        }
        
        fetchPosts(currentLang, filters);
    }
    
    // Close modals
    function closeModals() {
        $('#delete-modal').addClass('hidden');
    }
    
    // Show loading state
    function showLoadingState() {
        $('#posts-list').html(`
            <tr>
                <td colspan="7" class="px-6 py-12">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
                            <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">Đang tải dữ liệu...</p>
                    </div>
                </td>
            </tr>
        `);
    }
    
    // Show error state
    function showErrorState(message) {
        $('#posts-list').html(`
            <tr>
                <td colspan="7" class="px-6 py-12">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <p class="text-gray-900 font-medium">Đã xảy ra lỗi</p>
                        <p class="text-gray-500 text-sm mt-1">${message}</p>
                        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-redo mr-2"></i>
                            Thử lại
                        </button>
                    </div>
                </td>
            </tr>
        `);
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>