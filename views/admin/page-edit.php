<?php
// views/admin/page-edit.php

$page_title = isset($_GET['id']) ? 'Chỉnh sửa Trang - Admin CMS' : 'Thêm Trang Mới - Admin CMS';
ob_start();
?>

<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
    }

    .nav-tabs .nav-link.active {
        color: #212529;
        border-bottom-color: #007bff;
    }

    .nav-tabs .nav-link:hover {
        border-bottom-color: #dee2e6;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .required::after {
        content: '*';
        color: #ef4444;
        margin-left: 0.25rem;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }

    .section-item {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        background: #f9fafb;
    }

    .section-item.active {
        background: #f0f9ff;
        border-color: #3b82f6;
    }
</style>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-4">
        <a href="pages" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="fas fa-arrow-left text-gray-600"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900" id="page-title">
            <?php echo isset($_GET['id']) ? 'Chỉnh sửa Trang' : 'Thêm Trang Mới'; ?>
        </h1>
    </div>
</div>

<!-- Page Form -->
<form id="page-form">
    <input type="hidden" id="page-id" value="<?php echo $_GET['id'] ?? ''; ?>">

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Column - Main Content -->
        <div class="lg:w-8/12">
            <!-- Language Content -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Language Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="nav nav-tabs flex px-4 pt-2" id="language-tabs">
                        <!-- Language tabs will be loaded dynamically -->
                    </nav>
                </div>

                <!-- Language Contents -->
                <div class="p-4" id="language-contents">
                    <!-- Language content will be loaded dynamically -->
                </div>

                <!-- Save button -->
                <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <a href="pages" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" id="save-page-btn">
                        Lưu
                    </button>
                </div>
            </div>

            <!-- Sections Management -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Quản lý Sections</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Available Sections -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Sections có sẵn</h4>
                            <div id="available-sections" class="space-y-2 max-h-96 overflow-y-auto">
                                <!-- Available sections will be loaded here -->
                            </div>
                        </div>

                        <!-- Selected Sections -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Sections đã chọn</h4>
                            <div id="selected-sections" class="space-y-2 max-h-96 overflow-y-auto sortable-sections">
                                <!-- Selected sections will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Settings -->
        <div class="lg:w-4/12">
            <!-- Settings Panel -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Thông tin chung</h3>
                </div>
                <div class="p-4">
                    <!-- <div class="mb-4">
                        <label class="form-label required">URL Slug</label>
                        <input type="text" id="page-slug" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="url-cua-trang">
                    </div> -->

                    <div class="mb-4">
                        <label class="form-label">Template</label>
                        <select id="page-template" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="default">Default</option>
                            <option value="homepage">Homepage</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Trạng thái hiển thị</span>
                            <label class="switch">
                                <input type="checkbox" id="page-is-active" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Trang chủ</span>
                            <label class="switch">
                                <input type="checkbox" id="page-is-home">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Include Sortable.js for drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    $(document).ready(function() {
        const token = localStorage.getItem('admin_token');
        const pageId = $('#page-id').val();
        let languages = [];
        let currentLang = localStorage.getItem('admin_language') || 'vi';
        let availableSections = [];
        let selectedSections = [];

        // Initialize
        fetchLanguages();
        fetchAvailableSections();

        // If editing, load page data
        if (pageId) {
            loadPageData();
        }

        // Event handlers
        $('#page-form').submit(savePage);

        // Fetch languages
        function fetchLanguages() {
            fetch('../api/languages?active_only=true', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        languages = data.data.languages || [];
                        createLanguageTabs();
                    }
                })
                .catch(error => {
                    console.error('Error fetching languages:', error);
                    showToast('error', 'Không thể tải danh sách ngôn ngữ');
                });
        }

        // Create language tabs
        // Create language tabs
        function createLanguageTabs() {
            const activeIndex = languages.findIndex(lang => lang.code === currentLang);
            const defaultActiveIndex = activeIndex >= 0 ? activeIndex : 0;

            const tabs = languages.map((lang, index) => `
        <button type="button" class="nav-link language-tab ${index === defaultActiveIndex ? 'active' : ''}" data-language="${lang.code}">
            <span class="flex items-center gap-2">
                <img src="/uploads/flags/${lang.flag || 'default.png'}" alt="${lang.name}" class="w-5 h-4 object-cover">
                ${lang.name}
            </span>
        </button>
    `).join('');

            $('#language-tabs').html(tabs);

            const contents = languages.map((lang, index) => `
        <div class="language-content ${index !== defaultActiveIndex ? 'hidden' : ''}" data-language="${lang.code}">
            <div class="mb-4">
                <label class="form-label required">Tiêu đề</label>
                <input type="text" id="page-title-${lang.code}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Nhập tiêu đề trang"
                       onchange="updateSlug('${lang.code}')">
            </div>
            
            <div class="mb-4">
                <label class="form-label required">URL Slug</label>
                <input type="text" id="page-slug-${lang.code}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="url-cua-trang">
            </div>
            
            <div class="mb-4">
                <label class="form-label">Meta Title</label>
                <input type="text" id="page-meta-title-${lang.code}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Meta title cho SEO">
            </div>
            
            <div class="mb-4">
                <label class="form-label">Meta Description</label>
                <textarea id="page-meta-description-${lang.code}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Meta description cho SEO"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Meta Keywords</label>
                <input type="text" id="page-meta-keywords-${lang.code}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Keyword 1, keyword 2, ...">
            </div>
        </div>
    `).join('');

            $('#language-contents').html(contents);

            // Language tab switching
            $('#language-tabs').on('click', '.language-tab', function() {
                $('.language-tab').removeClass('active');
                $(this).addClass('active');
                $('.language-content').addClass('hidden');
                const langCode = $(this).data('language');
                $(`.language-content[data-language="${langCode}"]`).removeClass('hidden');
            });
        }


        // Tự động tạo slug từ title
        function updateSlug(langCode) {
            const title = $(`#page-title-${langCode}`).val();
            const currentSlug = $(`#page-slug-${langCode}`).val();

            // Chỉ tự động tạo slug nếu chưa có slug hoặc slug hiện tại rỗng
            if (!currentSlug) {
                let slug = title.toLowerCase();

                // Xử lý tiếng Việt
                if (langCode === 'vi') {
                    slug = removeVietnameseTones(slug);
                }

                // Thay thế các ký tự đặc biệt thành dấu gạch ngang
                slug = slug.replace(/[^a-z0-9]+/g, '-');
                slug = slug.replace(/^-+|-+$/g, ''); // Loại bỏ dấu gạch đầu và cuối

                $(`#page-slug-${langCode}`).val(slug);
            }
        }

        // Hàm loại bỏ dấu tiếng Việt
        function removeVietnameseTones(str) {
            str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
            str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
            str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
            str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
            str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
            str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
            str = str.replace(/đ/g, "d");
            str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
            str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
            str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
            str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
            str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
            str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
            str = str.replace(/Đ/g, "D");
            return str;
        }

        // Fetch available sections
        function fetchAvailableSections() {
            fetch('../api/sections', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        availableSections = data.data.sections || [];
                        renderAvailableSections();
                    }
                })
                .catch(error => {
                    console.error('Error fetching sections:', error);
                    showToast('error', 'Không thể tải danh sách sections');
                });
        }

        // Render available sections
        function renderAvailableSections() {
            const html = availableSections.map(section => `
            <div class="section-item p-3 cursor-pointer hover:bg-gray-50" data-id="${section.id}">
                <div class="flex items-center justify-between">
                    <div>
                        <h5 class="text-sm font-medium text-gray-900">${section.title || 'Không có tiêu đề'}</h5>
                        <p class="text-xs text-gray-500">${section.module_name}</p>
                    </div>
                    <button type="button" class="add-section-btn text-blue-600 hover:text-blue-700" data-id="${section.id}">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        `).join('');

            $('#available-sections').html(html);

            // Add section click handler
            $('.add-section-btn').click(function(e) {
                e.stopPropagation();
                const sectionId = $(this).data('id');
                addSectionToPage(sectionId);
            });
        }

        // Add section to page
        function addSectionToPage(sectionId) {
            const section = availableSections.find(s => s.id === sectionId);
            if (!section || selectedSections.some(s => s.id === sectionId)) return;

            selectedSections.push(section);
            renderSelectedSections();
        }

        // Remove section from page
        function removeSectionFromPage(sectionId) {
            selectedSections = selectedSections.filter(s => s.id !== sectionId);
            renderSelectedSections();
        }

        // Render selected sections
        function renderSelectedSections() {
            const html = selectedSections.map((section, index) => `
            <div class="section-item p-3" data-id="${section.id}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-grip-vertical sort-handle text-gray-400"></i>
                        <div>
                            <h5 class="text-sm font-medium text-gray-900">${section.title || 'Không có tiêu đề'}</h5>
                            <p class="text-xs text-gray-500">${section.module_name}</p>
                        </div>
                    </div>
                    <button type="button" class="remove-section-btn text-red-600 hover:text-red-700" data-id="${section.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');

            $('#selected-sections').html(html);

            // Remove section click handler
            $('.remove-section-btn').click(function(e) {
                e.stopPropagation();
                const sectionId = $(this).data('id');
                removeSectionFromPage(sectionId);
            });

            // Initialize sortable
            initializeSortable();
        }

        // Initialize Sortable
        function initializeSortable() {
            if (typeof Sortable !== 'undefined') {
                new Sortable(document.getElementById('selected-sections'), {
                    animation: 150,
                    handle: '.sort-handle',
                    onEnd: function(evt) {
                        // Update order in selectedSections array
                        const newOrder = [];
                        $('#selected-sections .section-item').each(function() {
                            const id = $(this).data('id');
                            const section = selectedSections.find(s => s.id === id);
                            if (section) newOrder.push(section);
                        });
                        selectedSections = newOrder;
                    }
                });
            }
        }

        // Save page với logic cập nhật
        function savePage(e) {
            e.preventDefault();

            // Collect translations
            const translations = [];
            let hasAtLeastOneValidTranslation = false;

            languages.forEach(lang => {
                const title = $(`#page-title-${lang.code}`).val().trim();
                if (title) {
                    hasAtLeastOneValidTranslation = true;
                    translations.push({
                        language_code: lang.code,
                        title: title,
                        slug: $(`#page-slug-${lang.code}`).val().trim(),
                        meta_title: $(`#page-meta-title-${lang.code}`).val().trim(),
                        meta_description: $(`#page-meta-description-${lang.code}`).val().trim(),
                        meta_keywords: $(`#page-meta-keywords-${lang.code}`).val().trim()
                    });
                }
            });

            if (!hasAtLeastOneValidTranslation) {
                showToast('error', 'Vui lòng nhập tiêu đề cho ít nhất một ngôn ngữ');
                return;
            }

            // Prepare data
            const data = {
                template: $('#page-template').val(),
                is_active: $('#page-is-active').is(':checked') ? 1 : 0,
                is_home: $('#page-is-home').is(':checked') ? 1 : 0,
                translations: translations
            };


            const url = pageId ? `../api/pages/${pageId}` : '../api/pages/create';
            const method = pageId ? 'PUT' : 'POST';

            const submitBtn = $('#save-page-btn');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...');

            fetch(url, {
                    method: method,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const savedPageId = result.data.page.id;

                        // Lưu sections
                        saveSections(savedPageId);
                    } else {
                        showToast('error', result.message || 'Không thể lưu trang');
                        submitBtn.prop('disabled', false).html('Lưu');
                    }
                })
                .catch(error => {
                    console.error('Error saving page:', error);
                    showToast('error', 'Lỗi kết nối: ' + error.message);
                    submitBtn.prop('disabled', false).html('Lưu');
                });
        }

        // Save sections với logic xóa sections cũ trước
        function saveSections(pageId) {
            // Nếu đang edit page, xóa tất cả sections cũ trước
            if (pageId) {
                fetch(`../api/pages/${pageId}/sections/clear`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            // Sau khi xóa xong, thêm sections mới
                            addSectionsToPage(pageId);
                        } else {
                            throw new Error('Không thể xóa sections cũ');
                        }
                    })
                    .catch(error => {
                        console.error('Error clearing sections:', error);
                        showToast('error', 'Lỗi khi xóa sections cũ');
                    });
            } else {
                // Nếu tạo mới, chỉ cần thêm sections
                addSectionsToPage(pageId);
            }
        }

        // Thêm sections vào page
        function addSectionsToPage(pageId) {
            if (selectedSections.length === 0) {
                showToast('success', 'Lưu trang thành công');
                setTimeout(() => {
                    window.location.href = 'pages';
                }, 1000);
                return;
            }

            const sectionPromises = selectedSections.map((section, index) => {
                return fetch(`../api/pages/${pageId}/sections`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        section_id: section.id,
                        sort_order: index,
                        is_active: 1
                    })
                });
            });

            Promise.all(sectionPromises)
                .then(() => {
                    showToast('success', 'Lưu trang và sections thành công');
                    setTimeout(() => {
                        window.location.href = 'pages';
                    }, 1000);
                })
                .catch(error => {
                    console.error('Error saving sections:', error);
                    showToast('error', 'Lỗi khi lưu sections');
                });
        }

        // Load page data với việc tải sections đúng cách
        function loadPageData() {
            showLoading();

            fetch(`../api/pages/${pageId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => response.json())
                .then(result => {
                    hideLoading();

                    if (result.status === 'success') {
                        const page = result.data.page;

                        // Set form values
                        $('#page-title').text('Chỉnh sửa Trang: ' + (page.title || ''));
                        $('#page-template').val(page.template || 'default');
                        $('#page-is-active').prop('checked', page.is_active == 1);
                        $('#page-is-home').prop('checked', page.is_home == 1);

                        // Load translations
                        if (page.translations && page.translations.length > 0) {
                            page.translations.forEach(translation => {
                                const langCode = translation.language_code;
                                $(`#page-title-${langCode}`).val(translation.title || '');
                                $(`#page-slug-${langCode}`).val(translation.slug || '');
                                $(`#page-meta-title-${langCode}`).val(translation.meta_title || '');
                                $(`#page-meta-description-${langCode}`).val(translation.meta_description || '');
                                $(`#page-meta-keywords-${langCode}`).val(translation.meta_keywords || '');
                            });
                        }

                        // Load sections đã chọn
                        if (page.sections && page.sections.length > 0) {
                            // Đảm bảo lấy đầy đủ thông tin section
                            selectedSections = page.sections.map(ps => {
                                // Tìm thông tin đầy đủ của section từ availableSections
                                const fullSection = availableSections.find(as => as.id === ps.section_id);
                                return fullSection || ps;
                            });
                            renderSelectedSections();
                        }
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading page:', error);
                    showToast('error', 'Lỗi kết nối: ' + error.message);
                });
        }
    });
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>