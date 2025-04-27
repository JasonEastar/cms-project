<?php
// views/admin/pages.php

$page_title = 'Quản lý Trang - Admin CMS';
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Quản lý Trang</h1>
            <p class="mt-2 text-gray-600">Quản lý các trang của website</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="page-edit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Thêm Trang Mới
            </a>
        </div>
    </div>
</div>

<!-- Pages List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
    <div id="pages-list" class="divide-y divide-gray-200">
        <!-- Pages will be loaded here -->
    </div>
</div>

<script>
$(document).ready(function() {
    const token = localStorage.getItem('admin_token');
    
    fetchPages();
    
    function fetchPages() {
        fetch('../api/pages', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderPages(data.data.pages);
            }
        });
    }
    
    function renderPages(pages) {
        if (pages.length === 0) {
            $('#pages-list').html(`
                <div class="p-12 text-center">
                    <p class="text-gray-500">Chưa có trang nào</p>
                </div>
            `);
            return;
        }
        
        const rows = pages.map(page => `
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">${page.title}</h3>
                        <p class="text-sm text-gray-500">${page.slug}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="page-edit?id=${page.id}" class="text-blue-600 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="text-red-600 hover:text-red-700" onclick="deletePage(${page.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#pages-list').html(rows);
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>