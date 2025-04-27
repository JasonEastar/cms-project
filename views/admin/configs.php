<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Cấu hình website - Admin CMS'; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar.closed {
                width: 0;
                overflow: hidden;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <header class="bg-white shadow-sm">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="p-2 rounded-md text-gray-500 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ml-4 font-semibold text-xl text-gray-800">CMS Admin</div>
                </div>
                <div class="flex items-center">
                    <div class="relative" id="profile-menu">
                        <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                            <span id="user-name">Admin</span>
                            <i class="fas fa-user-circle text-2xl"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden" id="dropdown-menu">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hồ sơ</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cài đặt</a>
                            <a href="#" id="logout-btn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Đăng xuất</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <aside class="sidebar bg-gray-800 text-white w-64 md:block flex-shrink-0" id="sidebar">
            <div class="py-4">
                <nav class="mt-5 px-2">
                    <a href="/admin/dashboard" class="group flex items-center px-2 py-3 text-base font-medium rounded-md hover:bg-gray-700 text-gray-300">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="/admin/categories" class="group flex items-center px-2 py-3 text-base font-medium rounded-md hover:bg-gray-700 text-gray-300">
                        <i class="fas fa-folder mr-3"></i>
                        Quản lý danh mục
                    </a>
                    <a href="/admin/posts" class="group flex items-center px-2 py-3 text-base font-medium rounded-md hover:bg-gray-700 text-gray-300">
                        <i class="fas fa-file-alt mr-3"></i>
                        Quản lý bài viết
                    </a>
                    <a href="/admin/sections" class="group flex items-center px-2 py-3 text-base font-medium rounded-md hover:bg-gray-700 text-gray-300">
                        <i class="fas fa-file-alt mr-3"></i>
                        Quản lý section
                    </a>
                    <a href="/admin/configs" class="group flex items-center px-2 py-3 text-base font-medium rounded-md bg-gray-900 text-white">
                        <i class="fas fa-cog mr-3"></i>
                        Cấu hình website
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 relative z-0 overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-full mx-auto">
                <div class="flex justify-between items-center pb-5 border-b border-gray-200">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Cấu hình website</h1>
                        <p class="mt-2 text-sm text-gray-500">Quản lý các thiết lập và thông tin website</p>
                    </div>
                </div>

                <!-- Tab navigation -->
                <div class="mt-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <a href="#general" class="tab-link active whitespace-nowrap py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" data-tab="general">
                            Thông tin chung
                        </a>
                        <a href="#company" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="company">
                            Thông tin công ty
                        </a>
                        <a href="#contact" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="contact">
                            Thông tin liên hệ
                        </a>
                        <a href="#social" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="social">
                            Mạng xã hội
                        </a>
                        <a href="#homepage" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="homepage">
                            Trang chủ
                        </a>
                    </nav>
                </div>

                <!-- Language selection -->
                <div class="mt-4 mb-6">
                    <label for="language-selector" class="block text-sm font-medium text-gray-700">Ngôn ngữ:</label>
                    <select id="language-selector" class="mt-1 block w-48 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="vi" selected>Tiếng Việt</option>
                        <option value="en">Tiếng Anh</option>
                    </select>
                </div>

                <!-- Tab content -->
                <div class="mt-6">
                    <!-- General settings -->
                    <div id="general" class="tab-content">
                        <form id="general-form" class="space-y-6 bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-medium text-gray-900">Thông tin chung</h2>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="site_name" class="block text-sm font-medium text-gray-700">Tên website</label>
                                    <div class="mt-1">
                                        <input type="text" id="site_name" name="site_name" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="site_tagline" class="block text-sm font-medium text-gray-700">Khẩu hiệu</label>
                                    <div class="mt-1">
                                        <input type="text" id="site_tagline" name="site_tagline" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="site_description" class="block text-sm font-medium text-gray-700">Mô tả website</label>
                                    <div class="mt-1">
                                        <textarea id="site_description" name="site_description" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="site_logo" class="block text-sm font-medium text-gray-700">Logo URL</label>
                                    <div class="mt-1 flex items-center">
                                        <div id="logo-preview" class="mr-3 h-16 flex-shrink-0 hidden">
                                            <img id="logo-image" class="h-16 object-contain" src="" alt="Logo">
                                        </div>
                                        <div id="empty-logo" class="mr-3 h-16 w-40 flex-shrink-0 flex items-center justify-center bg-gray-100 rounded-md">
                                            <i class="fas fa-image text-gray-400 text-xl"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <input type="text" id="site_logo" name="site_logo" placeholder="Nhập URL của logo" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="site_favicon" class="block text-sm font-medium text-gray-700">Favicon URL</label>
                                    <div class="mt-1 flex items-center">
                                        <div id="favicon-preview" class="mr-3 h-8 w-8 flex-shrink-0 hidden">
                                            <img id="favicon-image" class="h-8 w-8 object-contain" src="" alt="Favicon">
                                        </div>
                                        <div id="empty-favicon" class="mr-3 h-8 w-8 flex-shrink-0 flex items-center justify-center bg-gray-100 rounded-md">
                                            <i class="fas fa-image text-gray-400 text-xs"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <input type="text" id="site_favicon" name="site_favicon" placeholder="Nhập URL của favicon" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                                    <div class="mt-1">
                                        <input type="text" id="meta_keywords" name="meta_keywords" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Các từ khóa cách nhau bởi dấu phẩy</p>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Lưu thông tin
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Company information -->
                    <div id="company" class="tab-content hidden">
                        <form id="company-form" class="space-y-6 bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-medium text-gray-900">Thông tin công ty</h2>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-6">
                                    <label for="company_name" class="block text-sm font-medium text-gray-700">Tên công ty</label>
                                    <div class="mt-1">
                                        <input type="text" id="company_name" name="company_name" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="company_tax_code" class="block text-sm font-medium text-gray-700">Mã số thuế</label>
                                    <div class="mt-1">
                                        <input type="text" id="company_tax_code" name="company_tax_code" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="company_founding_date" class="block text-sm font-medium text-gray-700">Ngày thành lập</label>
                                    <div class="mt-1">
                                        <input type="date" id="company_founding_date" name="company_founding_date" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="company_about" class="block text-sm font-medium text-gray-700">Giới thiệu công ty</label>
                                    <div class="mt-1">
                                        <textarea id="company_about" name="company_about" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="company_vision" class="block text-sm font-medium text-gray-700">Tầm nhìn</label>
                                    <div class="mt-1">
                                        <textarea id="company_vision" name="company_vision" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="company_mission" class="block text-sm font-medium text-gray-700">Sứ mệnh</label>
                                    <div class="mt-1">
                                        <textarea id="company_mission" name="company_mission" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Lưu thông tin
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Contact information -->
                    <div id="contact" class="tab-content hidden">
                        <form id="contact-form" class="space-y-6 bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-medium text-gray-900">Thông tin liên hệ</h2>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Email liên hệ</label>
                                    <div class="mt-1">
                                        <input type="email" id="contact_email" name="contact_email" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                    <div class="mt-1">
                                        <input type="text" id="contact_phone" name="contact_phone" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="contact_hotline" class="block text-sm font-medium text-gray-700">Hotline</label>
                                    <div class="mt-1">
                                        <input type="text" id="contact_hotline" name="contact_hotline" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="contact_fax" class="block text-sm font-medium text-gray-700">Fax</label>
                                    <div class="mt-1">
                                        <input type="text" id="contact_fax" name="contact_fax" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="contact_address" class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                                    <div class="mt-1">
                                        <textarea id="contact_address" name="contact_address" rows="2" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="contact_map" class="block text-sm font-medium text-gray-700">Bản đồ (Iframe Google Maps)</label>
                                    <div class="mt-1">
                                        <textarea id="contact_map" name="contact_map" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                                <div class="sm:col-span-6">
                                    <label for="contact_working_hours" class="block text-sm font-medium text-gray-700">Giờ làm việc</label>
                                    <div class="mt-1">
                                        <input type="text" id="contact_working_hours" name="contact_working_hours" placeholder="Ví dụ: Thứ 2 - Thứ 6: 8:00 - 17:30" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Lưu thông tin
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Social Media -->
                    <div id="social" class="tab-content hidden">
                        <form id="social-form" class="space-y-6 bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-medium text-gray-900">Mạng xã hội</h2>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="social_facebook" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook
                                    </label>
                                    <div class="mt-1">
                                        <input type="url" id="social_facebook" name="social_facebook" placeholder="https://facebook.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_youtube" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-youtube text-red-600 mr-2"></i>YouTube
                                    </label>
                                    <div class="mt-1">
                                        <input type="url" id="social_youtube" name="social_youtube" placeholder="https://youtube.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_instagram" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-instagram text-pink-600 mr-2"></i>Instagram
                                    </label>
                                    <div class="mt-1">
                                        <input type="url" id="social_instagram" name="social_instagram" placeholder="https://instagram.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_twitter" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter
                                    </label>
                                    <div class="mt-1">
                                        <input type="url" id="social_twitter" name="social_twitter" placeholder="https://twitter.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_linkedin" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-linkedin text-blue-700 mr-2"></i>LinkedIn
                                    </label>
                                    <div class="mt-1">
                                        <input type="url" id="social_linkedin" name="social_linkedin" placeholder="https://linkedin.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_tiktok" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-tiktok text-black mr-2"></i>TikTok
                                    </label>
                                    <div class="mt-1">
                                        <input type="url" id="social_tiktok" name="social_tiktok" placeholder="https://tiktok.com/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_zalo" class="block text-sm font-medium text-gray-700">
                                        Zalo
                                    </label>


                                    <div class="mt-1">
                                        <input type="text" id="social_zalo" name="social_zalo" placeholder="https://zalo.me/..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="social_skype" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-skype text-blue-500 mr-2"></i>Skype
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" id="social_skype" name="social_skype" placeholder="live:username" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Lưu thông tin
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Homepage settings -->
                    <div id="homepage" class="tab-content hidden">
                        <form id="homepage-form" class="space-y-6 bg-white p-6 rounded-lg shadow">
                            <h2 class="text-lg font-medium text-gray-900">Thiết lập trang chủ</h2>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <!-- Hero Section -->
                                <div class="sm:col-span-6">
                                    <h3 class="text-base font-medium text-gray-800 border-b pb-2">Banner chính (Hero Section)</h3>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="hero_title" class="block text-sm font-medium text-gray-700">Tiêu đề chính</label>
                                    <div class="mt-1">
                                        <input type="text" id="hero_title" name="hero_title" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="hero_subtitle" class="block text-sm font-medium text-gray-700">Tiêu đề phụ</label>
                                    <div class="mt-1">
                                        <input type="text" id="hero_subtitle" name="hero_subtitle" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="hero_image" class="block text-sm font-medium text-gray-700">Ảnh nền</label>
                                    <div class="mt-1">
                                        <input type="text" id="hero_image" name="hero_image" placeholder="URL ảnh nền" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="hero_button_text" class="block text-sm font-medium text-gray-700">Nút chính - Text</label>
                                    <div class="mt-1">
                                        <input type="text" id="hero_button_text" name="hero_button_text" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="hero_button_url" class="block text-sm font-medium text-gray-700">Nút chính - URL</label>
                                    <div class="mt-1">
                                        <input type="text" id="hero_button_url" name="hero_button_url" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <!-- Features Section -->
                                <div class="sm:col-span-6 pt-4">
                                    <h3 class="text-base font-medium text-gray-800 border-b pb-2">Mục tính năng nổi bật</h3>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="features_title" class="block text-sm font-medium text-gray-700">Tiêu đề phần tính năng</label>
                                    <div class="mt-1">
                                        <input type="text" id="features_title" name="features_title" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="features_items" class="block text-sm font-medium text-gray-700">Các tính năng (JSON)</label>
                                    <div class="mt-1">
                                        <textarea id="features_items" name="features_items" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Format: [{"title":"Tiêu đề 1","description":"Mô tả 1","icon":"fa-star"},...]
                                    </p>
                                </div>

                                <!-- About Section -->
                                <div class="sm:col-span-6 pt-4">
                                    <h3 class="text-base font-medium text-gray-800 border-b pb-2">Phần giới thiệu</h3>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="about_title" class="block text-sm font-medium text-gray-700">Tiêu đề phần giới thiệu</label>
                                    <div class="mt-1">
                                        <input type="text" id="about_title" name="about_title" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="about_content" class="block text-sm font-medium text-gray-700">Nội dung giới thiệu</label>
                                    <div class="mt-1">
                                        <textarea id="about_content" name="about_content" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="about_image" class="block text-sm font-medium text-gray-700">Ảnh minh họa</label>
                                    <div class="mt-1">
                                        <input type="text" id="about_image" name="about_image" placeholder="URL ảnh giới thiệu" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Lưu thông tin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra đăng nhập
            const token = localStorage.getItem('admin_token');

            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            // Hiển thị tên người dùng
            const userData = JSON.parse(localStorage.getItem('admin_user') || '{}');
            if (userData.name) {
                document.getElementById('user-name').textContent = userData.name;
            }

            // Toggle dropdown menu
            const profileMenu = document.getElementById('profile-menu');
            const dropdownMenu = document.getElementById('dropdown-menu');

            profileMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function() {
                dropdownMenu.classList.add('hidden');
            });

            // Toggle sidebar on mobile
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('closed');
            });

            // Đăng xuất
            document.getElementById('logout-btn').addEventListener('click', function(e) {
                e.preventDefault();

                // Xóa token và thông tin người dùng
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');

                // Xóa cookie
                document.cookie = 'admin_token=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';

                // Chuyển hướng về trang đăng nhập
                window.location.href = '/admin/login';
            });

            // Các phần tử DOM
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            const languageSelector = document.getElementById('language-selector');
            const forms = {
                general: document.getElementById('general-form'),
                company: document.getElementById('company-form'),
                contact: document.getElementById('contact-form'),
                social: document.getElementById('social-form'),
                homepage: document.getElementById('homepage-form')
            };

            // Xử lý chuyển tab
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Xóa active class từ tất cả các tab link
                    tabLinks.forEach(l => {
                        l.classList.remove('active');
                        l.classList.remove('border-blue-500');
                        l.classList.remove('text-blue-600');
                        l.classList.add('border-transparent');
                        l.classList.add('text-gray-500');
                    });

                    // Thêm active class vào tab link hiện tại
                    this.classList.add('active');
                    this.classList.add('border-blue-500');
                    this.classList.add('text-blue-600');
                    this.classList.remove('border-transparent');
                    this.classList.remove('text-gray-500');

                    // Ẩn tất cả tab content
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Hiển thị tab content tương ứng
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                });
            });

            // Lấy tất cả cấu hình theo ngôn ngữ
            function fetchConfigs(lang) {
                fetch(`../api/configs?lang=${lang}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const configs = data.data.configs || {};
                            fillFormData(configs);
                        } else {
                            showError(data.message || 'Không thể lấy dữ liệu cấu hình');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching configs:', error);
                        showError('Lỗi kết nối: ' + error.message);
                    });
            }

            // Điền dữ liệu vào form
            function fillFormData(configs) {
                // Form thông tin chung
                fillFormFields(forms.general, {
                    'site_name': configs.site_name || '',
                    'site_tagline': configs.site_tagline || '',
                    'site_description': configs.site_description || '',
                    'site_logo': configs.site_logo || '',
                    'site_favicon': configs.site_favicon || '',
                    'meta_keywords': configs.meta_keywords || ''
                });

                // Cập nhật preview logo và favicon
                updateImagePreview('site_logo', 'logo-image', 'logo-preview', 'empty-logo');
                updateImagePreview('site_favicon', 'favicon-image', 'favicon-preview', 'empty-favicon');

                // Form thông tin công ty
                fillFormFields(forms.company, {
                    'company_name': configs.company_name || '',
                    'company_tax_code': configs.company_tax_code || '',
                    'company_founding_date': configs.company_founding_date || '',
                    'company_about': configs.company_about || '',
                    'company_vision': configs.company_vision || '',
                    'company_mission': configs.company_mission || ''
                });

                // Form thông tin liên hệ
                fillFormFields(forms.contact, {
                    'contact_email': configs.contact_email || '',
                    'contact_phone': configs.contact_phone || '',
                    'contact_hotline': configs.contact_hotline || '',
                    'contact_fax': configs.contact_fax || '',
                    'contact_address': configs.contact_address || '',
                    'contact_map': configs.contact_map || '',
                    'contact_working_hours': configs.contact_working_hours || ''
                });

                // Form mạng xã hội
                fillFormFields(forms.social, {
                    'social_facebook': configs.social_facebook || '',
                    'social_youtube': configs.social_youtube || '',
                    'social_instagram': configs.social_instagram || '',
                    'social_twitter': configs.social_twitter || '',
                    'social_linkedin': configs.social_linkedin || '',
                    'social_tiktok': configs.social_tiktok || '',
                    'social_zalo': configs.social_zalo || '',
                    'social_skype': configs.social_skype || ''
                });

                // Form trang chủ
                fillFormFields(forms.homepage, {
                    'hero_title': configs.hero_title || '',
                    'hero_subtitle': configs.hero_subtitle || '',
                    'hero_image': configs.hero_image || '',
                    'hero_button_text': configs.hero_button_text || '',
                    'hero_button_url': configs.hero_button_url || '',
                    'features_title': configs.features_title || '',
                    'features_items': configs.features_items || '',
                    'about_title': configs.about_title || '',
                    'about_content': configs.about_content || '',
                    'about_image': configs.about_image || ''
                });
            }

            // Điền dữ liệu vào các trường input trong form
            function fillFormFields(form, data) {
                if (!form) return;

                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`#${key}`);
                    if (input) {
                        input.value = data[key];
                    }
                });
            }

            // Cập nhật preview ảnh
            function updateImagePreview(inputId, imageId, previewId, emptyId) {
                const input = document.getElementById(inputId);
                const imagePreview = document.getElementById(previewId);
                const emptyPreview = document.getElementById(emptyId);
                const imageElement = document.getElementById(imageId);

                if (input && input.value) {
                    imageElement.src = input.value;
                    imagePreview.classList.remove('hidden');
                    emptyPreview.classList.add('hidden');
                } else {
                    imagePreview.classList.add('hidden');
                    emptyPreview.classList.remove('hidden');
                }

                // Thêm sự kiện input để cập nhật preview khi thay đổi
                input.addEventListener('input', function() {
                    if (this.value) {
                        imageElement.src = this.value;
                        imagePreview.classList.remove('hidden');
                        emptyPreview.classList.add('hidden');
                    } else {
                        imagePreview.classList.add('hidden');
                        emptyPreview.classList.remove('hidden');
                    }
                });
            }

            // Xử lý submit form
            Object.keys(forms).forEach(formKey => {
                const form = forms[formKey];

                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Thu thập dữ liệu từ form
                    const formData = {};
                    const formInputs = form.querySelectorAll('input, textarea, select');

                    formInputs.forEach(input => {
                        formData[input.id] = input.value;
                    });

                    // Gửi dữ liệu lên server
                    saveConfigs(formData);
                });
            });

            // Lưu cấu hình
            function saveConfigs(data) {
                fetch('../api/configs/set-bulk', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            configs: data,
                            lang: languageSelector.value
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            alert('Lưu cấu hình thành công!');
                            // Cập nhật lại configs
                            fetchConfigs(languageSelector.value);
                        } else {
                            showError(result.message || 'Không thể lưu cấu hình');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving configs:', error);
                        showError('Lỗi kết nối: ' + error.message);
                    });
            }

            // Hiển thị lỗi
            function showError(message) {
                alert(message);
            }

            // Xử lý thay đổi ngôn ngữ
            languageSelector.addEventListener('change', function() {
                fetchConfigs(this.value);
            });

            // Khởi tạo
            fetchConfigs(languageSelector.value);
        });
    </script>
</body>

</html>