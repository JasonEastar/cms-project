<?php
$page_title = 'Dashboard - Admin CMS';

// Bắt đầu output buffering
ob_start();
?>

<div class="pb-5 border-b border-gray-200">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="mt-2 text-sm text-gray-500">Xem tổng quan về hệ thống</p>
</div>

<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Card 1 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <i class="fas fa-folder text-white"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Tổng danh mục</dt>
                        <dd>
                            <div class="text-lg font-medium text-gray-900" id="category-count">--</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="categories" class="font-medium text-blue-600 hover:text-blue-500">Quản lý danh mục</a>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <i class="fas fa-file-alt text-white"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Tổng bài viết</dt>
                        <dd>
                            <div class="text-lg font-medium text-gray-900" id="post-count">--</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="posts" class="font-medium text-green-600 hover:text-green-500">Quản lý bài viết</a>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <i class="fas fa-globe text-white"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Ngôn ngữ</dt>
                        <dd>
                            <div class="text-lg font-medium text-gray-900" id="language-count">--</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="configs" class="font-medium text-purple-600 hover:text-purple-500">Cấu hình</a>
            </div>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                    <i class="fas fa-cog text-white"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Cấu hình</dt>
                        <dd>
                            <div class="text-lg font-medium text-gray-900" id="config-count">--</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="configs" class="font-medium text-red-600 hover:text-red-500">Quản lý cấu hình</a>
            </div>
        </div>
    </div>
</div>

<!-- Bài viết gần đây -->
<div class="mt-8">
    <h2 class="text-lg font-medium text-gray-900">Bài viết gần đây</h2>
    <div class="mt-4">
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Danh mục</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngôn ngữ</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Hành động</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="recent-posts">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500" colspan="5">
                                        Đang tải dữ liệu...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('admin_token');
    
    // Nếu không có token, chuyển về trang đăng nhập
    if (!token) {
        window.location.href = 'login';
        return;
    }
    
    // Lấy số lượng danh mục
    fetch('../api/categories?lang=vi', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('category-count').textContent = data.data.categories ? data.data.categories.length : 0;
        }
    })
    .catch(error => console.error('Error fetching categories:', error));
    
    // Lấy số lượng bài viết
    fetch('../api/posts?lang=vi', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('post-count').textContent = data.data.total || 0;
            
            // Hiển thị bài viết gần đây
            const recentPostsContainer = document.getElementById('recent-posts');
            
            if (data.data.posts && data.data.posts.length > 0) {
                recentPostsContainer.innerHTML = '';
                
                data.data.posts.slice(0, 5).forEach(post => {
                    const row = document.createElement('tr');
                    
                    // Format date
                    const date = new Date(post.created_at);
                    const formattedDate = date.toLocaleDateString('vi-VN');
                    
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${post.title}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">${post.category_name || 'N/A'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">${post.lang}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formattedDate}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="posts-edit?id=${post.id}" class="text-blue-600 hover:text-blue-900">Sửa</a>
                        </td>
                    `;
                    
                    recentPostsContainer.appendChild(row);
                });
            } else {
                recentPostsContainer.innerHTML = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500" colspan="5">
                            Không có bài viết nào
                        </td>
                    </tr>
                `;
            }
        }
    })
    .catch(error => console.error('Error fetching posts:', error));
    
    // Lấy số lượng ngôn ngữ - mặc định là 2 (vi, en)
    document.getElementById('language-count').textContent = '2';
    
    // Lấy số lượng cấu hình
    fetch('../api/configs?lang=vi', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const configCount = data.data.configs ? Object.keys(data.data.configs).length : 0;
            document.getElementById('config-count').textContent = configCount;
        }
    })
    .catch(error => console.error('Error fetching configs:', error));
});
</script>

<?php
// Lấy nội dung đã buffer
$content = ob_get_clean();

// Include layout
include 'includes/layout.php';
?>