<?php
// views/admin/section-edit.php
// Giao diện thêm/sửa section với quản lý items tích hợp

require_once __DIR__ . '/../../utils/Language.php';

$page_title = isset($_GET['id']) ? 'Chỉnh sửa Section - Admin CMS' : 'Thêm Section Mới - Admin CMS';
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

    .item-container {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .item-header {
        background-color: #f9fafb;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .item-content {
        padding: 1rem;
        display: none;
    }

    .item-content.active {
        display: block;
    }

    .sort-handle {
        cursor: move;
    }

    .collapse-icon {
        transition: transform 0.3s;
    }

    .item-container.collapsed .collapse-icon {
        transform: rotate(-90deg);
    }
</style>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-4">
        <a href="sections" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="fas fa-arrow-left text-gray-600"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900" id="page-title">
            <?php echo isset($_GET['id']) ? 'Chỉnh sửa Section' : 'Thêm Section Mới'; ?>
        </h1>
    </div>
</div>

<!-- Section Form -->
<form id="section-form" enctype="multipart/form-data">
    <input type="hidden" id="section-id" value="<?php echo $_GET['id'] ?? ''; ?>">

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Column - Main Content -->
        <div class="lg:w-8/12">
            <!-- Language Content for Section -->
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
                    <a href="sections" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" id="save-section-btn">
                        Lưu
                    </button>
                </div>
            </div>

            <!-- Section Items Management -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Sửa các dịch vụ khác</h3>
                </div>
                <div class="p-4">
                    <div id="section-items-container" class="sortable-items">
                        <!-- Items will be loaded dynamically -->
                    </div>
                    <button type="button" id="add-item-btn" class="mt-4 px-4 py-2 bg-white border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50">
                        <i class="fas fa-plus mr-2"></i>
                        Thêm nội dung
                    </button>
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
                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Trạng thái hiển thị</span>
                            <label class="switch">
                                <input type="checkbox" id="section-is-active" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Hiển thị trên di động</span>
                            <label class="switch">
                                <input type="checkbox" id="section-show-on-mobile" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label required">Kiểu hiển thị</label>
                        <select id="section-template" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="default">Default</option>
                            <option value="grid">Grid</option>
                            <option value="carousel">Carousel</option>
                            <option value="list">List</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Image Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-4">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Hình ảnh</h3>
                </div>
                <div class="p-4">
                    <div class="relative">
                        <img id="image-preview" src="/api/placeholder/400/300" alt="Image preview" class="w-full h-48 object-cover rounded-lg mb-3">
                        <label class="block w-full">
                            <span class="sr-only">Choose image</span>
                            <input type="file" id="section-image" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-medium
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100
                                cursor-pointer" accept="image/*">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Include CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/translations/vi.js"></script>

<script>
    $(document).ready(function() {
        const token = localStorage.getItem('admin_token');
        const sectionId = $('#section-id').val();
        let languages = [];
        let currentLang = localStorage.getItem('admin_language') || 'vi';
        let editorInstances = {};
        let itemEditorInstances = {};
        let pendingTranslations = [];
        let sectionItems = [];
        let itemCounter = 0;

        // Initialize
        fetchLanguages();

        // If editing, load section data
        if (sectionId) {
            loadSectionData();
        }

        // Event handlers
        $('#section-form').submit(saveSection);
        $('#section-image').change(handleImageChange);
        $('#add-item-btn').click(addNewItem);

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
                    <label class="form-label required">Tên mô-đun</label>
                    <input type="text" id="section-module-name-${lang.code}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập tên module">
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" id="section-title-${lang.code}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập tiêu đề">
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Mô tả chi tiết</label>
                    <div id="section-description-${lang.code}"></div>
                </div>
            </div>
        `).join('');

            $('#language-contents').html(contents);

            languages.forEach(lang => {
                initCKEditor(`section-description-${lang.code}`, lang.code);
            });

            $('#language-tabs').on('click', '.language-tab', function() {
                $('.language-tab').removeClass('active');
                $(this).addClass('active');
                $('.language-content').addClass('hidden');
                const langCode = $(this).data('language');
                $(`.language-content[data-language="${langCode}"]`).removeClass('hidden');
            });
        }

        // Initialize CKEditor 5
        function initCKEditor(elementId, langCode) {
            const config = {
                language: langCode === 'vi' ? 'vi' : 'en',
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', '|',
                        'link', 'bulletedList', 'numberedList', '|',
                        'undo', 'redo'
                    ]
                }
            };

            ClassicEditor
                .create(document.getElementById(elementId), config)
                .then(editor => {
                    editorInstances[elementId] = editor;

                    if (sectionId && pendingTranslations.length > 0) {
                        const langCode = elementId.replace('section-description-', '');
                        const translation = pendingTranslations.find(t => t.language_code === langCode);
                        if (translation && translation.description) {
                            editor.setData(translation.description);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error initializing CKEditor:', error);
                });
        }

        // Handle image change
        function handleImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', './..' + e.target.result);
                }
                reader.readAsDataURL(file);
            }
        }

        // Load section data
        function loadSectionData() {
            showLoading();

            fetch(`../api/sections/${sectionId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => response.json())
                .then(result => {
                    hideLoading();

                    if (result.status === 'success') {
                        const section = result.data.section;

                        // Set form values
                        $('#page-title').text('Chỉnh sửa Section: ' + (section.title || ''));
                        $('#section-code').val(section.code || '');
                        $('#section-template').val(section.template || 'default');
                        $('#section-icon').val(section.icon || '');
                        $('#section-is-active').prop('checked', section.is_active == 1);
                        $('#section-show-on-mobile').prop('checked', section.show_on_mobile == 1);

                        if (section.image) {
                            $('#image-preview').attr('src', '/' + section.image);
                        }

                        // Load translations for section
                        if (section.translations && section.translations.length > 0) {
                            section.translations.forEach(translation => {
                                const langCode = translation.language_code;
                                $(`#section-module-name-${langCode}`).val(translation.module_name || '');
                                $(`#section-title-${langCode}`).val(translation.title || '');

                                // Set description in editor
                                if (editorInstances[`section-description-${langCode}`]) {
                                    editorInstances[`section-description-${langCode}`].setData(translation.description || '');
                                }
                            });
                        }

                        // Load items
                        if (section.items) {
                            sectionItems = section.items;
                            renderSectionItems();
                        }
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading section:', error);
                    showToast('error', 'Lỗi kết nối: ' + error.message);
                });
        }
        // Render section items
        function renderSectionItems() {
            const container = $('#section-items-container');
            container.empty();

            sectionItems.forEach((item, index) => {
                addItemToContainer(item, index);
            });

            initializeSortable();
        }

        // Add new item
        function addNewItem() {
            const newItem = {
                id: null,
                type: 'text',
                is_active: 1,
                sort_order: sectionItems.length,
                translations: languages.map(lang => ({
                    language_code: lang.code,
                    title: '',
                    description: ''
                }))
            };

            sectionItems.push(newItem);
            addItemToContainer(newItem, sectionItems.length - 1);
            initializeSortable();
        }

        // Add item to container
        function addItemToContainer(item, index) {
            const itemId = item.id || `new-${itemCounter++}`;
            // Lấy title từ translation đầu tiên để hiển thị
            const displayTitle = item.translations && item.translations.length > 0 ?
                item.translations[0].title :
                (item.title || '(Chưa có tiêu đề)');

            const itemHtml = `
        <div class="item-container" data-index="${index}" data-id="${itemId}">
            <div class="item-header">
                <div class="flex items-center gap-3">
                    <i class="fas fa-grip-vertical sort-handle text-gray-400"></i>
                    <span class="item-number">#${index + 1}</span>
                    <span class="item-title">
                        ${displayTitle}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="text-blue-600 hover:text-blue-700" onclick="toggleItem(this)">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                    <button type="button" class="text-red-600 hover:text-red-700" onclick="removeItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="item-content">
                <div class="mb-4">
                    <nav class="nav nav-tabs flex mb-4">
                        ${languages.map((lang, langIndex) => `
                            <button type="button" class="nav-link item-language-tab ${langIndex === 0 ? 'active' : ''}" 
                                    data-item-index="${index}" data-language="${lang.code}">
                                <span class="flex items-center gap-2">
                                    <img src="/uploads/flags/${lang.flag || 'default.png'}" alt="${lang.name}" class="w-5 h-4 object-cover">
                                    ${lang.name}
                                </span>
                            </button>
                        `).join('')}
                    </nav>
                    
                    ${languages.map((lang, langIndex) => `
                        <div class="item-language-content ${langIndex !== 0 ? 'hidden' : ''}" 
                             data-item-index="${index}" data-language="${lang.code}">
                            <div class="mb-4">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       id="item-title-${index}-${lang.code}" 
                                       value="${getItemTranslation(item, lang.code, 'title')}"
                                       onchange="updateItemTitle(${index}, '${lang.code}', this.value)">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Mô tả chi tiết</label>
                                <div id="item-description-${index}-${lang.code}"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Loại</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                onchange="updateItemType(${index}, this.value)">
                            <option value="text" ${item.type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="image" ${item.type === 'image' ? 'selected' : ''}>Image</option>
                            <option value="icon" ${item.type === 'icon' ? 'selected' : ''}>Icon</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Trạng thái</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                onchange="updateItemStatus(${index}, this.value)">
                            <option value="1" ${item.is_active == 1 ? 'selected' : ''}>Kích hoạt</option>
                            <option value="0" ${item.is_active == 0 ? 'selected' : ''}>Ẩn</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4" id="item-media-${index}">
                    ${renderItemMedia(item, index)}
                </div>
            </div>
        </div>
    `;

            $('#section-items-container').append(itemHtml);

            // Initialize editors for this item
            languages.forEach(lang => {
                const editorId = `item-description-${index}-${lang.code}`;
                initItemEditor(editorId, lang.code, item, index);
            });

            // Setup language tab switching for this item
            $(`.item-language-tab[data-item-index="${index}"]`).on('click', function() {
                const itemIndex = $(this).data('item-index');
                const langCode = $(this).data('language');

                $(`.item-language-tab[data-item-index="${itemIndex}"]`).removeClass('active');
                $(this).addClass('active');

                $(`.item-language-content[data-item-index="${itemIndex}"]`).addClass('hidden');
                $(`.item-language-content[data-item-index="${itemIndex}"][data-language="${langCode}"]`).removeClass('hidden');
            });
        }

        // Get item translation
        function getItemTranslation(item, langCode, field) {
            if (item.translations) {
                const translation = item.translations.find(t => t.language_code === langCode);
                return translation ? translation[field] || '' : '';
            }
            return '';
        }

        // Initialize item editor
        function initItemEditor(editorId, langCode, item, index) {
            const config = {
                language: langCode === 'vi' ? 'vi' : 'en',
                toolbar: {
                    items: [
                        'bold', 'italic', '|',
                        'link', 'bulletedList', 'numberedList', '|',
                        'undo', 'redo'
                    ]
                }
            };

            ClassicEditor
                .create(document.getElementById(editorId), config)
                .then(editor => {
                    itemEditorInstances[editorId] = editor;

                    // Set initial content
                    const description = getItemTranslation(item, langCode, 'description');
                    if (description) {
                        editor.setData(description);
                    }

                    // Update item data on change
                    editor.model.document.on('change:data', () => {
                        const content = editor.getData();
                        updateItemDescription(index, langCode, content);
                    });
                })
                .catch(error => {
                    console.error('Error initializing item editor:', error);
                });
        }

        // Render item media
        function renderItemMedia(item, index) {
            switch (item.type) {
                case 'image':
                    return `
                <div>
                    <label class="form-label">Hình ảnh</label>
                    <input type="file" class="item-image-input" data-index="${index}" accept="image/*">
                    ${item.image ? `<img src="/${item.image}" class="mt-2 w-full h-32 object-cover rounded-lg">` : ''}
                </div>
            `;
                case 'icon':
                    return `
                <div>
                    <label class="form-label">Icon</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="${item.icon || ''}" onchange="updateItemIcon(${index}, this.value)">
                </div>
            `;
                default:
                    return '';
            }
        }

        // Toggle item
        window.toggleItem = function(button) {
            const container = $(button).closest('.item-container');
            const content = container.find('.item-content');
            const icon = $(button).find('.collapse-icon');

            content.toggleClass('active');
            icon.toggleClass('rotate-180');
        }

        // Remove item
        window.removeItem = function(index) {
            if (confirm('Bạn có chắc chắn muốn xóa item này?')) {
                sectionItems.splice(index, 1);
                renderSectionItems();
            }
        }

        // Update item functions
        window.updateItemTitle = function(index, langCode, value) {
            const item = sectionItems[index];
            if (!item.translations) {
                item.translations = [];
            }

            let translation = item.translations.find(t => t.language_code === langCode);
            if (!translation) {
                translation = {
                    language_code: langCode
                };
                item.translations.push(translation);
            }

            translation.title = value;

            if (langCode === languages[0].code) {
                $(`.item-container[data-index="${index}"] .item-title`).text(value || '(Chưa có tiêu đề)');
            }
        }

        window.updateItemDescription = function(index, langCode, value) {
            const item = sectionItems[index];
            if (!item.translations) {
                item.translations = [];
            }

            let translation = item.translations.find(t => t.language_code === langCode);
            if (!translation) {
                translation = {
                    language_code: langCode
                };
                item.translations.push(translation);
            }

            translation.description = value;
        }

        window.updateItemType = function(index, value) {
            sectionItems[index].type = value;
            $(`#item-media-${index}`).html(renderItemMedia(sectionItems[index], index));
        }

        window.updateItemStatus = function(index, value) {
            sectionItems[index].is_active = parseInt(value);
        }

        window.updateItemIcon = function(index, value) {
            sectionItems[index].icon = value;
        }

        // Initialize Sortable
        function initializeSortable() {
            if (typeof Sortable !== 'undefined') {
                new Sortable(document.getElementById('section-items-container'), {
                    animation: 150,
                    handle: '.sort-handle',
                    onEnd: function(evt) {
                        // Update indexes
                        const newOrder = [];
                        $('#section-items-container .item-container').each(function(index) {
                            const oldIndex = $(this).data('index');
                            newOrder.push(sectionItems[oldIndex]);
                            $(this).attr('data-index', index);
                            $(this).find('.item-number').text(`#${index + 1}`);
                        });
                        sectionItems = newOrder;
                    }
                });
            }
        }

        // Save section
        function saveSection(e) {
            e.preventDefault();

            let hasAtLeastOneValidTranslation = false;
            const translations = [];

            languages.forEach(lang => {
                const moduleName = $(`#section-module-name-${lang.code}`).val().trim();
                const title = $(`#section-title-${lang.code}`).val().trim();
                if (moduleName) {
                    hasAtLeastOneValidTranslation = true;
                    const description = editorInstances[`section-description-${lang.code}`].getData();

                    translations.push({
                        language_code: lang.code,
                        module_name: moduleName,
                        title: title || '',
                        description: description || ''
                    });
                }
            });

            if (!hasAtLeastOneValidTranslation) {
                showToast('error', 'Vui lòng nhập tên module cho ít nhất một ngôn ngữ');
                return;
            }

            // Prepare items data
            const itemsData = sectionItems.map((item, index) => {
                const itemTranslations = languages.map(lang => {
                    let translation = item.translations?.find(t => t.language_code === lang.code) || {};

                    const editorId = `item-description-${index}-${lang.code}`;
                    const editor = itemEditorInstances[editorId];
                    const description = editor ? editor.getData() : '';

                    return {
                        language_code: lang.code,
                        title: $(`#item-title-${index}-${lang.code}`).val() || '',
                        description: description
                    };
                });

                return {
                    id: item.id,
                    type: item.type,
                    icon: item.icon,
                    image: item.image,
                    link: item.link,
                    is_active: item.is_active,
                    sort_order: index,
                    translations: itemTranslations
                };
            });

            const formData = new FormData();

            formData.append('code', $('#section-code').val() || '');
            formData.append('template', $('#section-template').val() || 'default');
            formData.append('icon', $('#section-icon').val() || '');
            formData.append('is_active', $('#section-is-active').is(':checked') ? 1 : 0);
            formData.append('show_on_mobile', $('#section-show-on-mobile').is(':checked') ? 1 : 0);

            const imageFile = $('#section-image')[0].files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }

            formData.append('translations', JSON.stringify(translations));
            formData.append('items', JSON.stringify(itemsData));

            // Append item images
            $('.item-image-input').each(function() {
                const index = $(this).data('index');
                const file = this.files[0];
                if (file) {
                    formData.append(`item_image_${index}`, file);
                }
            });

            const url = sectionId ? `../api/sections/${sectionId}` : '../api/sections/create';

            const submitBtn = $('#save-section-btn');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...');

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        showToast('success', result.message || 'Lưu section thành công');
                        setTimeout(() => {
                            window.location.href = 'sections';
                        }, 1000);
                    } else {
                        showToast('error', result.message || 'Không thể lưu section');
                        submitBtn.prop('disabled', false).html('Lưu');
                    }
                })
                .catch(error => {
                    console.error('Error saving section:', error);
                    showToast('error', 'Lỗi kết nối: ' + error.message);
                    submitBtn.prop('disabled', false).html('Lưu');
                });
        }

        // Setup file upload handlers
        $(document).on('change', '.item-image-input', function() {
            const index = $(this).data('index');
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgElement = $(`<img src="${e.target.result}" class="mt-2 w-full h-32 object-cover rounded-lg">`);
                    $(`#item-media-${index}`).find('img').remove();
                    $(`#item-media-${index}`).append(imgElement);

                    // Update item data
                    sectionItems[index].imagePreview = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<!-- Include Sortable.js for drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>