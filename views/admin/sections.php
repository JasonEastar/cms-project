<?php
// views/admin/sections.php
// Giao diện quản lý sections với hỗ trợ đa ngôn ngữ

require_once __DIR__ . '/../../utils/Language.php';

$page_title = 'Quản lý Sections - Admin CMS';
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Quản lý Sections</h1>
            <p class="mt-2 text-gray-600">Quản lý sections đa ngôn ngữ cho website</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="section-edit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Thêm Section Mới
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
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1">Đã kích hoạt</option>
                    <option value="0">Chưa kích hoạt</option>
                </select>
            </div>
            <div>
                <label for="mobile-filter" class="block text-sm font-medium text-gray-700 mb-1">Hiển thị trên mobile</label>
                <select id="mobile-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả</option>
                    <option value="1">Hiển thị</option>
                    <option value="0">Ẩn</option>
                </select>
            </div>
            <div>
                <label for="template-filter" class="block text-sm font-medium text-gray-700 mb-1">Template</label>
                <select id="template-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">Tất cả template</option>
                    <option value="default">Default</option>
                    <option value="grid">Grid</option>
                    <option value="carousel">Carousel</option>
                    <option value="list">List</option>
                </select>
            </div>
            <div>
                <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <div class="relative">
                    <input type="text" id="search-filter" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nhập tiêu đề hoặc mã section...">
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

<!-- Sections List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
    <div id="sections-list" class="divide-y divide-gray-200">
        <!-- Sections will be loaded here -->
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg w-full max-w-md p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Xác nhận xóa section</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Bạn có chắc chắn muốn xóa section <span id="delete-section-title" class="font-medium"></span>?
                    <br>
                    <span class="text-xs text-gray-400">Hành động này sẽ xóa tất cả items và bản dịch, không thể hoàn tác.</span>
                </p>
            </div>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 close-modal">
                    Hủy
                </button>
                <button type="button" id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Xóa Section
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .section-card {
        transition: all 0.2s ease;
    }
    .section-card:hover {
        background-color: #f9fafb;
    }
    .section-card .action-buttons {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .section-card:hover .action-buttons {
        opacity: 1;
    }
</style>

<script>
$(document).ready(function() {
    const token = localStorage.getItem('admin_token');
    let sections = [];
    let currentLang = localStorage.getItem('admin_language') || 'vi';
    
    // Initialize
    fetchSections(currentLang);
    
    // Event handlers
    $('.close-modal').click(closeModals);
    $('#confirm-delete-btn').click(deleteSection);
    $('#apply-filter-btn').click(applyFilters);
    
    // Fetch sections
    function fetchSections(lang, filters = {}) {
        showLoadingState();
        
        const queryParams = new URLSearchParams({
            lang: lang,
            ...filters
        });
        
        fetch(`../api/sections?${queryParams}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                sections = data.data.sections || [];
                renderSections(sections);
            } else {
                showErrorState(data.message || 'Không thể tải danh sách sections');
            }
        })
        .catch(error => {
            console.error('Error fetching sections:', error);
            showErrorState('Lỗi kết nối: ' + error.message);
        });
    }
    
    // Render sections list
    function renderSections(sectionsData) {
        if (sectionsData.length === 0) {
            $('#sections-list').html(`
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Không có section nào</p>
                    <p class="text-gray-400 text-sm mt-1">Nhấn nút "Thêm Section Mới" để tạo section đầu tiên</p>
                </div>
            `);
            return;
        }
        
        const rows = sectionsData.map(section => {
            const imageUrl = '../'+section.image || '/api/placeholder/400/300';
            
            return `
                <div class="section-card p-4" data-id="${section.id}">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <img src="${imageUrl}" alt="" class="w-24 h-16 object-cover rounded-lg">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">${section.title || 'Không có tiêu đề'}</h3>
                                    <p class="text-sm text-gray-500 mt-1">${section.module_name}</p>
                                </div>
                                <div class="action-buttons flex items-center gap-2">
                                    <button class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-md" onclick="window.location.href='section-edit?id=${section.id}'">
                                        <i class="fas fa-edit mr-1"></i>
                                        Sửa
                                    </button>
                                    <button class="px-3 py-1.5 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md delete-section-btn" data-id="${section.id}" data-title="${section.title}">
                                        <i class="fas fa-trash mr-1"></i>
                                        Xóa
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center gap-4">
                                <span class="text-xs px-2 py-1 rounded-full ${section.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                                    ${section.is_active ? 'Kích hoạt' : 'Chưa kích hoạt'}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full ${section.show_on_mobile ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'}">
                                    ${section.show_on_mobile ? 'Hiển thị mobile' : 'Ẩn mobile'}
                                </span>
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-code mr-1"></i>
                                    ${section.code}
                                </span>
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-palette mr-1"></i>
                                    ${section.template}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        $('#sections-list').html(rows);
        attachEventHandlers();
        
        // Initialize Sortable for drag & drop sorting
        if (typeof Sortable !== 'undefined') {
            new Sortable(document.getElementById('sections-list'), {
                animation: 150,
                handle: '.section-card',
                ghostClass: 'bg-blue-50',
                onEnd: function(evt) {
                    const updates = [];
                    $('#sections-list .section-card').each(function(index) {
                        updates.push({
                            id: $(this).data('id'),
                            sort_order: index
                        });
                    });
                    
                    fetch('../api/sections/update-order', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ orders: updates })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            showToast('success', 'Cập nhật thứ tự thành công');
                        }
                    });
                }
            });
        }
    }
    
    // Attach event handlers
    function attachEventHandlers() {
        $('.delete-section-btn').click(function() {
            const sectionId = $(this).data('id');
            const sectionTitle = $(this).data('title');
            confirmDelete(sectionId, sectionTitle);
        });
    }
    
    // Confirm delete
    function confirmDelete(sectionId, sectionTitle) {
        $('#delete-section-title').text(sectionTitle);
        $('#confirm-delete-btn').data('id', sectionId);
        $('#delete-modal').removeClass('hidden');
    }
    
    // Delete section
    function deleteSection() {
        const sectionId = $(this).data('id');
        const deleteBtn = $(this);
        
        deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang xóa...');
        
        fetch(`../api/sections/${sectionId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                showToast('success', result.message || 'Xóa section thành công');
                closeModals();
                fetchSections(currentLang);
            } else {
                showToast('error', result.message || 'Không thể xóa section');
                deleteBtn.prop('disabled', false).html('Xóa Section');
            }
        })
        .catch(error => {
            console.error('Error deleting section:', error);
            showToast('error', 'Lỗi kết nối: ' + error.message);
            deleteBtn.prop('disabled', false).html('Xóa Section');
        });
    }
    
    // Apply filters
    function applyFilters() {
        const filters = {
            is_active: $('#status-filter').val(),
            show_on_mobile: $('#mobile-filter').val(),
            template: $('#template-filter').val()
        };
        
        const searchTerm = $('#search-filter').val().toLowerCase();
        if (searchTerm) {
            filters.search = searchTerm;
        }
        
        fetchSections(currentLang, filters);
    }
    
    // Close modals
    function closeModals() {
        $('#delete-modal').addClass('hidden');
    }
    
    // Show loading state
    function showLoadingState() {
        $('#sections-list').html(`
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                </div>
                <p class="text-gray-500 font-medium">Đang tải dữ liệu...</p>
            </div>
        `);
    }
    
    // Show error state
    function showErrorState(message) {
        $('#sections-list').html(`
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <p class="text-gray-900 font-medium">Đã xảy ra lỗi</p>
                <p class="text-gray-500 text-sm mt-1">${message}</p>
                <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-redo mr-2"></i>
                    Thử lại
                </button>
            </div>
        `);
    }
});
</script>

<!-- Include Sortable.js for drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>