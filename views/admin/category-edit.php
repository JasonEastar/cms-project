<?php
// views/admin/category-edit.php
// Giao diện thêm/sửa danh mục với hỗ trợ đa ngôn ngữ

require_once __DIR__ . '/../../utils/Language.php';

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
            <h1 class="text-2xl font-bold text-gray-900" id="page-title"><?php echo isset($_GET['id']) ? __('categories.edit_category') : __('categories.add_category'); ?></h1>
            <p class="text-gray-500 mt-1"><?php echo isset($_GET['id']) ? __('categories.edit_category') : __('categories.add_category'); ?></p>
        </div>
    </div>
</div>

<!-- Category Form -->
<form id="category-form">
    <input type="hidden" id="category-id" value="<?php echo $_GET['id'] ?? ''; ?>">

    <!-- Basic Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900"><?php echo __('categories.basic_info'); ?></h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category-code" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('categories.category_code'); ?></label>
                    <input type="text" id="category-code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.code_placeholder'); ?>">
                    <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.code_auto_generate'); ?></p>
                </div>
                <div>

                    <label for="category-parent" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('categories.category_parent'); ?></label>
                    <select id="category-parent" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition duration-150">
                        <option value=""><?php echo __('categories.no_parent'); ?></option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="category-is-active" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition duration-150" checked>
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
                <!-- Language tabs will be loaded dynamically -->
            </nav>
        </div>

        <!-- Language Contents -->
        <div class="p-6" id="language-contents">
            <!-- Language content will be loaded dynamically -->
        </div>
    </div>

    <!-- Form Actions -->
    <div class="mt-8 flex items-center justify-end gap-4">
        <a href="categories" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
            <i class="fas fa-times mr-2"></i>
            <?php echo __('common.cancel'); ?>
        </a>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" id="save-category-btn">
            <i class="fas fa-save mr-2"></i>
            <?php echo __('common.save'); ?>
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        const token = localStorage.getItem('admin_token');
        const categoryId = $('#category-id').val();
        let languages = [];
        let currentLang = localStorage.getItem('admin_language') || 'vi';

        // Initialize
        fetchLanguages();

        // If editing, load category data
        if (categoryId) {
            loadCategoryData();
        }

        // Event handlers
        $('#category-form').submit(saveCategory);

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
                        loadParentCategories();
                    }
                })
                .catch(error => {
                    console.error('Error fetching languages:', error);
                    showToast('error', '<?php echo __('languages.cannot_load'); ?>');
                });
        }

        // Create language tabs
        function createLanguageTabs() {
            // Find active language index
            const activeIndex = languages.findIndex(lang => lang.code === currentLang);
            const defaultActiveIndex = activeIndex >= 0 ? activeIndex : 0;

            // Create tabs
            const tabs = languages.map((lang, index) => `
                <button type="button" class="language-tab px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200 ${index === defaultActiveIndex ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}" data-language="${lang.code}">
                    ${lang.name} ${lang.is_default ? '<span class="text-xs text-blue-500"><?php echo __('languages.is_default'); ?></span>' : ''}
                </button>
            `).join('');

            $('#language-tabs').html(tabs);

            // Create tab contents
            const contents = languages.map((lang, index) => `
                <div class="language-content ${index !== defaultActiveIndex ? 'hidden' : ''}" data-language="${lang.code}">
                    <div class="space-y-6">
                        <div>
                            <label for="category-name-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo __('categories.category_name'); ?> (${lang.name})
                                ${lang.is_default ? '<span class="text-red-500">*</span>' : ''}
                            </label>
                            <input type="text" id="category-name-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.name_placeholder'); ?>" ${lang.is_default ? 'required' : ''}>
                        </div>
                        
                        <div>
                            <label for="category-slug-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo __('categories.slug'); ?> (${lang.name})
                            </label>
                            <input type="text" id="category-slug-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.slug_placeholder'); ?>">
                            <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.slug_hint'); ?></p>
                        </div>
                        
                        <div>
                            <label for="category-description-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo __('common.description'); ?> (${lang.name})
                            </label>
                            <textarea id="category-description-${lang.code}" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.description_placeholder'); ?>"></textarea>
                        </div>
                        
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo __('categories.seo_optimization'); ?></h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="category-seo-title-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php echo __('categories.seo_title'); ?> (${lang.name})
                                    </label>
                                    <input type="text" id="category-seo-title-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.seo_title_placeholder'); ?>">
                                    <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.seo_title_hint'); ?></p>
                                </div>
                                
                                <div>
                                    <label for="category-seo-description-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php echo __('categories.seo_description'); ?> (${lang.name})
                                    </label>
                                    <textarea id="category-seo-description-${lang.code}" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="<?php echo __('categories.seo_description_placeholder'); ?>"></textarea>
                                    <p class="mt-1.5 text-sm text-gray-500"><?php echo __('categories.seo_description_hint'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            $('#language-contents').html(contents);

            // Add click event for tabs
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
        }

        // Load parent categories
        function loadParentCategories() {
            fetch(`../api/categories?lang=${currentLang}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const categories = data.data.categories || [];
                        const options = categories
                            .filter(cat => cat.id != categoryId) // Exclude current category to prevent self-parent
                            .map(cat => `<option value="${cat.id}">${cat.name}</option>`)
                            .join('');

                        $('#category-parent').html('<option value=""><?php echo __('categories.no_parent'); ?></option>' + options);
                    }
                })
                .catch(error => {
                    console.error('Error loading parent categories:', error);
                });
        }

        // Load category data for editing
        function loadCategoryData() {
            showLoading();

            fetch(`../api/categories/${categoryId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => response.json())
                .then(result => {
                    hideLoading();

                    if (result.status === 'success') {
                        const category = result.data.category;

                        // Update page title
                        $('#page-title').text('<?php echo __('categories.edit_category'); ?>: ' + category.name);

                        // Fill basic info
                        $('#category-code').val(category.code);
                        $('#category-parent').val(category.parent_id || '');
                        $('#category-is-active').prop('checked', category.is_active == 1);

                        // Fill translations
                        if (category.translations) {
                            category.translations.forEach(translation => {
                                const langCode = translation.language_code;
                                $(`#category-name-${langCode}`).val(translation.name || '');
                                $(`#category-slug-${langCode}`).val(translation.slug || '');
                                $(`#category-description-${langCode}`).val(translation.description || '');
                                $(`#category-seo-title-${langCode}`).val(translation.seo_title || '');
                                $(`#category-seo-description-${langCode}`).val(translation.seo_description || '');
                            });
                        }
                    } else {
                        showToast('error', result.message || '<?php echo __('categories.load_error'); ?>');
                        setTimeout(() => {
                            window.location.href = 'categories';
                        }, 2000);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading category:', error);
                    showToast('error', '<?php echo __('categories.connection_error'); ?>: ' + error.message);
                });
        }

        // Save category
        function saveCategory(e) {
            e.preventDefault();

            const data = {
                code: $('#category-code').val() || null,
                parent_id: $('#category-parent').val() || null,
                is_active: $('#category-is-active').is(':checked') ? 1 : 0,
                translations: []
            };

            // Collect translations
            languages.forEach(lang => {
                const name = $(`#category-name-${lang.code}`).val();
                if (name) {
                    data.translations.push({
                        language_code: lang.code,
                        name: name,
                        slug: $(`#category-slug-${lang.code}`).val() || null,
                        description: $(`#category-description-${lang.code}`).val() || null,
                        seo_title: $(`#category-seo-title-${lang.code}`).val() || null,
                        seo_description: $(`#category-seo-description-${lang.code}`).val() || null
                    });
                }
            });

            if (data.translations.length === 0) {
                showToast('error', '<?php echo __('categories.translations_required'); ?>');
                return;
            }

            const url = categoryId ? `../api/categories/${categoryId}` : '../api/categories/create';
            const method = categoryId ? 'PUT' : 'POST';

            const submitBtn = $('#save-category-btn');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php echo __('common.processing'); ?>');

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
                        showToast('success', result.message || '<?php echo __('categories.save_success'); ?>');
                        setTimeout(() => {
                            window.location.href = 'categories';
                        }, 1000);
                    } else {
                        showToast('error', result.message || '<?php echo __('categories.save_error'); ?>');
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i><?php echo __('common.save'); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error saving category:', error);
                    showToast('error', '<?php echo __('categories.connection_error'); ?>: ' + error.message);
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i><?php echo __('common.save'); ?>');
                });
        }
    });
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>