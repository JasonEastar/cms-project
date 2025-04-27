<?php
// views/admin/includes/layout.php
// Layout chung cho admin với hỗ trợ đa ngôn ngữ

require_once __DIR__ . '/../../../utils/Language.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');

function isLoggedIn()
{
    return isset($_COOKIE['admin_token']);
}

if ($current_page != 'login' && !isLoggedIn()) {
    header('Location: login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo Language::getCurrentLanguage(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? __('header.admin_cms'); ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Custom styles -->
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar.closed {
                transform: translateX(-100%);
            }
        }

        .sidebar-overlay {
            transition: opacity 0.3s ease-in-out;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen overflow-x-hidden">
    <?php if ($current_page != 'login'): ?>

        <!-- Top Navigation -->
        <nav class="fixed top-0 left-0 right-0 bg-white shadow-md z-50">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <button id="sidebar-toggle" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="ml-4 flex items-center">
                            <div class="text-xl font-bold text-blue-600"><?php echo __('header.admin_cms'); ?></div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Language Switcher -->
                        <div class="relative">
                            <button id="language-dropdown" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <i class="fas fa-globe text-gray-500"></i>
                                <span class="text-sm font-medium text-gray-700" id="current-language">Loading...</span>
                                <i class="fas fa-chevron-down text-gray-500 text-sm"></i>
                            </button>

                            <div id="language-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 hidden">
                                <!-- Languages will be loaded dynamically -->
                            </div>
                        </div>

                        <!-- Notification -->
                        <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button id="profile-dropdown" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <span class="text-gray-700 font-medium hidden sm:block" id="user-name">Admin</span>
                                <i class="fas fa-chevron-down text-gray-500 text-sm"></i>
                            </button>

                            <div id="profile-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 hidden">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-circle mr-2"></i> <?php echo __('header.profile'); ?>
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i> <?php echo __('header.settings'); ?>
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="#" id="logout-btn" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> <?php echo __('header.logout'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar Overlay (for mobile) -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed top-0 left-0 bottom-0 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white z-40 transform lg:transform-none lg:translate-x-0 closed">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700 lg:hidden">
                    <div class="text-xl font-bold text-blue-400"><?php echo __('header.admin_cms'); ?></div>
                    <button id="sidebar-close" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-2">
                    <a href="dashboard" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'dashboard' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span class="ml-3"><?php echo __('menu.dashboard'); ?></span>
                    </a>

                    <a href="languages" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'languages' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-language w-6"></i>
                        <span class="ml-3"><?php echo __('menu.languages'); ?></span>
                    </a>

                    <a href="categories" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'categories' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-folder w-6"></i>
                        <span class="ml-3"><?php echo __('menu.categories'); ?></span>
                    </a>

                    <a href="posts" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'posts' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-file-alt w-6"></i>
                        <span class="ml-3"><?php echo __('menu.posts'); ?></span>
                    </a>

                    <a href="sections" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'sections' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-th-large w-6"></i>
                        <span class="ml-3"><?php echo __('menu.sections'); ?></span>
                    </a>

                    <a href="pages" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'page' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-th-large w-6"></i>
                        <span class="ml-3"><?php echo __('menu.page'); ?></span>
                    </a>

                    <a href="configs" class="flex items-center px-4 py-3 mb-1 rounded-lg <?php echo $current_page == 'configs' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                        <i class="fas fa-cog w-6"></i>
                        <span class="ml-3"><?php echo __('menu.configs'); ?></span>
                    </a>
                </nav>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-700">
                    <div class="text-xs text-gray-400">
                        © 2024 <?php echo __('header.admin_cms'); ?>. All rights reserved.
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="lg:ml-64 pt-16 min-h-screen">
            <div class="p-4 sm:p-6 lg:p-8">
                <?php if (isset($content)) {
                    echo $content;
                } ?>
            </div>
        </main>

        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
            <div class="bg-white p-4 rounded-lg flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span><?php echo __('common.loading'); ?></span>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Load languages for language switcher
                loadLanguages();

                // Toggle sidebar
                $('#sidebar-toggle, #sidebar-close').click(function() {
                    $('#sidebar').toggleClass('closed');
                    if ($(window).width() < 1024) {
                        $('#sidebar-overlay').toggleClass('hidden');
                    }
                });

                // Sidebar overlay click
                $('#sidebar-overlay').click(function() {
                    $('#sidebar').addClass('closed');
                    $(this).addClass('hidden');
                });

                // Language dropdown
                $('#language-dropdown').click(function(e) {
                    e.stopPropagation();
                    $('#language-menu').toggleClass('hidden');
                    $('#profile-menu').addClass('hidden');
                });

                // Profile dropdown
                $('#profile-dropdown').click(function(e) {
                    e.stopPropagation();
                    $('#profile-menu').toggleClass('hidden');
                    $('#language-menu').addClass('hidden');
                });

                // Close dropdowns when clicking outside
                $(document).click(function() {
                    $('#profile-menu, #language-menu').addClass('hidden');
                });

                // Load languages for language switcher
                function loadLanguages() {
                    const token = localStorage.getItem('admin_token');

                    fetch('../api/languages?active_only=true', {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.status === 'success' && result.data.languages) {
                                renderLanguageMenu(result.data.languages);
                                setCurrentLanguage(result.data.languages);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading languages:', error);
                        });
                }

                // Render language menu
                function renderLanguageMenu(languages) {
                    const languageMenu = $('#language-menu');
                    languageMenu.empty();

                    languages.forEach(lang => {
                        const isActive = localStorage.getItem('admin_language') === lang.code;
                        const menuItem = $(`
                    <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 ${isActive ? 'text-blue-600 bg-blue-50' : 'text-gray-700'}" data-lang-code="${lang.code}">
                        <i class="fas fa-language mr-2"></i>
                        ${lang.name}
                        ${lang.is_default ? '<span class="ml-2 text-xs text-gray-500">(<?php echo __('languages.default'); ?>)</span>' : ''}
                        ${isActive ? '<i class="fas fa-check ml-2 text-blue-600"></i>' : ''}
                    </a>
                `);

                        menuItem.click(function(e) {
                            e.preventDefault();
                            changeLanguage(lang.code, lang.name);
                        });

                        languageMenu.append(menuItem);
                    });
                }

                // Set current language display
                function setCurrentLanguage(languages) {
                    const currentLangCode = localStorage.getItem('admin_language');
                    if (currentLangCode) {
                        const currentLang = languages.find(lang => lang.code === currentLangCode);
                        if (currentLang) {
                            $('#current-language').text(currentLang.name);
                            // Set cookie for PHP to read
                            document.cookie = `admin_language=${currentLangCode};path=/`;
                            return;
                        }
                    }

                    // If no saved language, use default
                    const defaultLang = languages.find(lang => lang.is_default);
                    if (defaultLang) {
                        $('#current-language').text(defaultLang.name);
                        localStorage.setItem('admin_language', defaultLang.code);
                        document.cookie = `admin_language=${defaultLang.code};path=/`;
                    }
                }

                // Change language
                function changeLanguage(code, name) {
                    localStorage.setItem('admin_language', code);
                    document.cookie = `admin_language=${code};path=/`;
                    $('#current-language').text(name);
                    $('#language-menu').addClass('hidden');

                    // Show loading and reload page
                    showLoading();
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }

                // Load user info
                const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
                if (user && user.name) {
                    $('#user-name').text(user.name);
                }

                // Logout
                $('#logout-btn').click(async function(e) {
                    e.preventDefault();

                    try {
                        const token = localStorage.getItem('admin_token');
                        await fetch('../api/auth/logout', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json'
                            }
                        });
                    } catch (error) {
                        console.error('Logout error:', error);
                    } finally {
                        // Clear data and redirect
                        localStorage.removeItem('admin_token');
                        localStorage.removeItem('admin_user');
                        localStorage.removeItem('admin_language');
                        document.cookie = 'admin_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                        document.cookie = 'admin_language=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                        window.location.href = 'login';
                    }
                });
            });

            // Global utility functions
            function showToast(type, message) {
                const types = {
                    success: {
                        bg: 'bg-green-500',
                        icon: 'fa-check-circle'
                    },
                    error: {
                        bg: 'bg-red-500',
                        icon: 'fa-exclamation-circle'
                    },
                    warning: {
                        bg: 'bg-yellow-500',
                        icon: 'fa-exclamation-triangle'
                    },
                    info: {
                        bg: 'bg-blue-500',
                        icon: 'fa-info-circle'
                    }
                };

                const config = types[type] || types.info;

                const toast = $(`
            <div class="toast flex items-center p-4 rounded-lg shadow-lg text-white ${config.bg}">
                <i class="fas ${config.icon} mr-3"></i>
                <span>${message}</span>
            </div>
        `);

                $('#toast-container').append(toast);

                setTimeout(() => {
                    toast.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            function showLoading() {
                $('#loading-overlay').removeClass('hidden');
            }

            function hideLoading() {
                $('#loading-overlay').addClass('hidden');
            }
        </script>

    <?php endif; ?>
</body>

</html>