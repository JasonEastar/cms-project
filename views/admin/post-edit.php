<?php
// views/admin/post-edit.php
// Giao diện thêm/sửa bài viết với hỗ trợ đa ngôn ngữ

require_once __DIR__ . '/../../utils/Language.php';

$page_title = isset($_GET['id']) ? 'Chỉnh sửa bài viết - Admin CMS' : 'Thêm bài viết mới - Admin CMS';
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-4">
        <a href="posts" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <i class="fas fa-arrow-left text-gray-600"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900" id="page-title">
                <?php echo isset($_GET['id']) ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới'; ?>
            </h1>
            <p class="text-gray-500 mt-1">
                <?php echo isset($_GET['id']) ? 'Cập nhật thông tin bài viết' : 'Tạo bài viết mới cho website'; ?>
            </p>
        </div>
    </div>
</div>

<!-- Post Form -->
<form id="post-form" enctype="multipart/form-data">
    <input type="hidden" id="post-id" value="<?php echo $_GET['id'] ?? ''; ?>">
    
    <!-- Basic Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Thông tin cơ bản</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="post-code" class="block text-sm font-medium text-gray-700 mb-1">Mã bài viết</label>
                    <input type="text" id="post-code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Nhập mã bài viết">
                    <p class="mt-1.5 text-sm text-gray-500">Để trống sẽ tự động tạo từ tiêu đề</p>
                </div>
                <div>
                    <label for="post-category" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                    <select id="post-category" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition duration-150">
                        <option value="">Chọn danh mục</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="post-published-at" class="block text-sm font-medium text-gray-700 mb-1">Ngày xuất bản</label>
                    <input type="datetime-local" id="post-published-at" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện</label>
                    <div class="mt-1 flex items-center gap-4">
                        <img id="thumbnail-preview" src="/api/placeholder/400/300" alt="Thumbnail preview" class="w-32 h-24 object-cover rounded-lg">
                        <label class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <i class="fas fa-upload mr-2"></i>
                            Chọn ảnh
                            <input type="file" id="post-thumbnail" class="hidden" accept="image/*">
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6 mt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="post-is-active" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition duration-150" checked>
                    <span class="text-gray-700 font-medium">Xuất bản</span>
                </label>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" id="post-is-featured" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition duration-150">
                    <span class="text-gray-700 font-medium">Nổi bật</span>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Multilingual Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Nội dung đa ngôn ngữ</h2>
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
        <a href="posts" class="px-4 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
            <i class="fas fa-times mr-2"></i>
            Hủy
        </a>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" id="save-post-btn">
            <i class="fas fa-save mr-2"></i>
            Lưu bài viết
        </button>
    </div>
</form>

<!-- Include CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/translations/vi.js"></script>

<style>
    .border-red-500 {
        border-color: #ef4444 !important;
    }
    .border-red-500:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
    }
</style>

<script>
$(document).ready(function() {
    const token = localStorage.getItem('admin_token');
    const postId = $('#post-id').val();
    let languages = [];
    let currentLang = localStorage.getItem('admin_language') || 'vi';
    let editorInstances = {};
    let pendingTranslations = [];
    
    // Initialize
    fetchLanguages();
    fetchCategories();
    
    // Set default datetime to now
    if (!postId) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#post-published-at').val(now.toISOString().slice(0, 16));
    }
    
    // If editing, load post data
    if (postId) {
        loadPostData();
    }
    
    // Event handlers
    $('#post-form').submit(savePost);
    $('#post-thumbnail').change(handleThumbnailChange);
    
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
    
    // Fetch categories
    function fetchCategories() {
        fetch(`../api/posts/categories?lang=${currentLang}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const categories = data.data.categories || [];
                const options = categories.map(category => `
                    <option value="${category.id}">${category.name}</option>
                `).join('');
                
                $('#post-category').html('<option value="">Chọn danh mục</option>' + options);
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
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
                ${lang.name} ${lang.is_default ? '<span class="text-xs text-blue-500">(Mặc định)</span>' : ''}
            </button>
        `).join('');
        
        $('#language-tabs').html(tabs);
        
        // Create tab contents
        const contents = languages.map((lang, index) => `
            <div class="language-content ${index !== defaultActiveIndex ? 'hidden' : ''}" data-language="${lang.code}">
                <div class="space-y-6">
                    <div>
                        <label for="post-title-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                            Tiêu đề (${lang.name})
                        </label>
                        <input type="text" id="post-title-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Nhập tiêu đề bài viết">
                    </div>
                    
                    <div>
                        <label for="post-slug-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                            Đường dẫn URL (${lang.name})
                        </label>
                        <input type="text" id="post-slug-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Để trống sẽ tự động tạo từ tiêu đề">
                        <p class="mt-1.5 text-sm text-gray-500">Đường dẫn thân thiện SEO cho bài viết này</p>
                    </div>
                    
                    <div>
                        <label for="post-summary-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                            Tóm tắt (${lang.name})
                        </label>
                        <textarea id="post-summary-${lang.code}" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Nhập tóm tắt ngắn gọn về bài viết"></textarea>
                    </div>
                    
                    <div>
                        <label for="post-content-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                            Nội dung (${lang.name})
                        </label>
                        <div id="post-content-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm"></div>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tối ưu SEO</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="post-seo-title-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tiêu đề SEO (${lang.name})
                                </label>
                                <input type="text" id="post-seo-title-${lang.code}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Tiêu đề hiển thị trên kết quả tìm kiếm">
                                <p class="mt-1.5 text-sm text-gray-500">Nên dài 50-60 ký tự</p>
                            </div>
                            
                            <div>
                                <label for="post-seo-description-${lang.code}" class="block text-sm font-medium text-gray-700 mb-1">
                                    Mô tả SEO (${lang.name})
                                </label>
                                <textarea id="post-seo-description-${lang.code}" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="Mô tả hiển thị trên kết quả tìm kiếm"></textarea>
                                <p class="mt-1.5 text-sm text-gray-500">Nên dài 120-160 ký tự</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        $('#language-contents').html(contents);
        
        // Initialize CKEditor for content editors
        languages.forEach(lang => {
            initCKEditor(`post-content-${lang.code}`, lang.code);
        });
        
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
    
    // Initialize CKEditor 5
    function initCKEditor(elementId, langCode) {
        ClassicEditor
            .create(document.getElementById(elementId), {
                language: langCode === 'vi' ? 'vi' : 'en',
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'link', 'blockQuote', 'insertTable', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'undo', 'redo'
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells'
                    ]
                },
                link: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://'
                },
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed']
            })
            .then(editor => {
                editorInstances[elementId] = editor;
                
                // Nếu đang edit và có dữ liệu, set ngay content
                if (postId && pendingTranslations.length > 0) {
                    const langCode = elementId.replace('post-content-', '');
                    const translation = pendingTranslations.find(t => t.language_code === langCode);
                    if (translation && translation.content) {
                        editor.setData(translation.content);
                    }
                }
            })
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    }
    
    // Handle thumbnail change
    function handleThumbnailChange(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#thumbnail-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    }
    
    // Load post data for editing
    function loadPostData() {
        showLoading();
        
        fetch(`../api/posts/${postId}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(result => {
            hideLoading();
            
            if (result.status === 'success') {
                const post = result.data.post;
                
                // Update page title
                $('#page-title').text('Chỉnh sửa bài viết: ' + (post.title || ''));
                
                // Fill basic info
                $('#post-code').val(post.code || '');
                $('#post-category').val(post.category_id || '');
                $('#post-is-active').prop('checked', post.is_active == 1);
                $('#post-is-featured').prop('checked', post.is_featured == 1);
                
                if (post.published_at) {
                    const publishedDate = new Date(post.published_at);
                    publishedDate.setMinutes(publishedDate.getMinutes() - publishedDate.getTimezoneOffset());
                    $('#post-published-at').val(publishedDate.toISOString().slice(0, 16));
                }
                
                if (post.thumbnail) {
                    $('#thumbnail-preview').attr('src', post.thumbnail);
                }
                
                // Lưu translations để dùng sau
                pendingTranslations = post.translations || [];
                
                // Fill translations
                if (post.translations && post.translations.length > 0) {
                    post.translations.forEach(translation => {
                        const langCode = translation.language_code;
                        $(`#post-title-${langCode}`).val(translation.title || '');
                        $(`#post-slug-${langCode}`).val(translation.slug || '');
                        $(`#post-summary-${langCode}`).val(translation.summary || '');
                        $(`#post-seo-title-${langCode}`).val(translation.seo_title || '');
                        $(`#post-seo-description-${langCode}`).val(translation.seo_description || '');
                        
                        // Content sẽ được set trong initCKEditor
                    });
                }
            } else {
                showToast('error', result.message || 'Không thể tải thông tin bài viết');
                setTimeout(() => {
                    window.location.href = 'posts';
                }, 2000);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error loading post:', error);
            showToast('error', 'Lỗi kết nối: ' + error.message);
        });
    }
    
    // Save post
    function savePost(e) {
        e.preventDefault();
        
        // Kiểm tra có ít nhất một tiêu đề
        let hasAtLeastOneTitle = false;
        const translations = [];
        
        languages.forEach(lang => {
            const title = $(`#post-title-${lang.code}`).val().trim();
            if (title) {
                hasAtLeastOneTitle = true;
                const content = editorInstances[`post-content-${lang.code}`].getData();
                
                translations.push({
                    language_code: lang.code,
                    title: title,
                    slug: $(`#post-slug-${lang.code}`).val() || '',
                    summary: $(`#post-summary-${lang.code}`).val() || '',
                    content: content || '',
                    seo_title: $(`#post-seo-title-${lang.code}`).val() || '',
                    seo_description: $(`#post-seo-description-${lang.code}`).val() || ''
                });
            }
        });
        
        if (!hasAtLeastOneTitle) {
            showToast('error', 'Vui lòng nhập tiêu đề cho ít nhất một ngôn ngữ');
            return;
        }
        
        const formData = new FormData();
        
        // Basic info
        formData.append('code', $('#post-code').val() || '');
        formData.append('category_id', $('#post-category').val() || '');
        formData.append('is_active', $('#post-is-active').is(':checked') ? 1 : 0);
        formData.append('is_featured', $('#post-is-featured').is(':checked') ? 1 : 0);
        formData.append('published_at', $('#post-published-at').val() || '');
        
        // Thumbnail
        const thumbnailFile = $('#post-thumbnail')[0].files[0];
        if (thumbnailFile) {
            formData.append('thumbnail', thumbnailFile);
        }
        
        // Translations
        formData.append('translations', JSON.stringify(translations));
        
        const url = postId ? `../api/posts/${postId}` : '../api/posts/create';
        
        const submitBtn = $('#save-post-btn');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...');
        
        fetch(url, {
            method: 'POST', // Luôn dùng POST vì FormData
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                showToast('success', result.message || 'Lưu bài viết thành công');
                setTimeout(() => {
                    window.location.href = 'posts';
                }, 1000);
            } else {
                showToast('error', result.message || 'Không thể lưu bài viết');
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Lưu bài viết');
            }
        })
        .catch(error => {
            console.error('Error saving post:', error);
            showToast('error', 'Lỗi kết nối: ' + error.message);
            submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Lưu bài viết');
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>