<?php
// index.php
// Entry point - Điểm khởi đầu của ứng dụng

// Bật hiển thị lỗi để dễ debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Xử lý routing
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Lấy đường dẫn từ URI và loại bỏ các tham số truy vấn
$path = parse_url($requestUri, PHP_URL_PATH);

// Xử lý truy cập file uploads
if (strpos($path, '/uploads/') === 0) {
    $filePath = __DIR__ . $path;
    if (file_exists($filePath)) {
        $mime = mime_content_type($filePath);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo 'File not found';
        exit;
    }
}


// Loại bỏ '/api' từ đường dẫn nếu có
$apiPrefix = '/api';
$isApiRequest = false;

if (strpos($path, $apiPrefix) === 0) {
    $path = substr($path, strlen($apiPrefix));
    $isApiRequest = true;
}

// Chia các path thành các phần
$pathParts = explode('/', trim($path, '/'));

// Controller là phần đầu tiên của path
$controllerName = isset($pathParts[0]) ? strtolower($pathParts[0]) : '';

// Action là phần thứ hai của path
$action = isset($pathParts[1]) ? strtolower($pathParts[1]) : '';

// ID là phần thứ ba của path (nếu có)
$id = isset($pathParts[2]) ? $pathParts[2] : null;

// Phân biệt giữa các yêu cầu API và yêu cầu giao diện
if ($isApiRequest) {
    // Xử lý API request - thiết lập header JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Xử lý CORS preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    // Load các tệp class cần thiết cho API
    require_once __DIR__ . '/utils/Response.php';
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/CategoryController.php';
    require_once __DIR__ . '/controllers/PostController.php';
    require_once __DIR__ . '/controllers/ConfigController.php';
    require_once __DIR__ . '/controllers/SectionController.php';
    require_once __DIR__ . '/controllers/LanguageController.php';
    require_once __DIR__ . '/controllers/PageController.php';


    // Xử lý API routes
    try {
        switch ($controllerName) {
            case 'auth':
                $controller = new AuthController();

                switch ($action) {
                    case 'login':
                        $controller->login();
                        break;

                    case 'register':
                        $controller->register();
                        break;

                    case 'me':
                        AuthMiddleware::authenticate();
                        $controller->me();
                        break;

                    case 'change-password':
                        AuthMiddleware::authenticate();
                        $controller->changePassword();
                        break;

                    case 'logout':
                        AuthMiddleware::authenticate();
                        $controller->logout();
                        break;

                    default:
                        Response::notFound('Route không tồn tại');
                }
                break;

            case 'languages':
                $controller = new LanguageController();

                switch ($action) {
                    case '':
                        $controller->getAll();
                        break;

                    case 'create':
                        AuthMiddleware::authenticate();
                        $controller->create();
                        break;

                    case 'default':
                        $controller->getDefault();
                        break;

                    case 'set-default':
                        if ($id) {
                            AuthMiddleware::authenticate();
                            $controller->setDefault($id);
                        } else {
                            Response::error('ID ngôn ngữ không hợp lệ');
                        }
                        break;

                    default:
                        // Nếu action là một ID
                        if (is_numeric($action)) {
                            $id = $action;
                            if ($requestMethod === 'GET') {
                                $controller->getById($id);
                            } elseif ($requestMethod === 'PUT' || $requestMethod === 'PATCH') {
                                AuthMiddleware::authenticate();
                                $controller->update($id);
                            } elseif ($requestMethod === 'DELETE') {
                                AuthMiddleware::authenticate();
                                $controller->delete($id);
                            } else {
                                Response::error('Phương thức không được hỗ trợ', 405);
                            }
                        } else {
                            Response::notFound('Route không tồn tại');
                        }
                }
                break;

            case 'categories':
                $controller = new CategoryController();

                switch ($action) {
                    case '':
                        $controller->getAll();
                        break;

                    case 'create':
                        AuthMiddleware::authenticate();
                        $controller->create();
                        break;

                    case 'get-by-slug':
                        $controller->getBySlug();
                        break;

                    case 'languages':
                        $controller->getLanguages();
                        break;

                    default:
                        // Nếu action là một ID
                        if (is_numeric($action)) {
                            $id = $action;
                            if ($requestMethod === 'GET') {
                                $controller->getById($id);
                            } elseif ($requestMethod === 'PUT' || $requestMethod === 'PATCH') {
                                AuthMiddleware::authenticate();
                                $controller->update($id);
                            } elseif ($requestMethod === 'DELETE') {
                                AuthMiddleware::authenticate();
                                $controller->delete($id);
                            } else {
                                Response::error('Phương thức không được hỗ trợ', 405);
                            }
                        } else {
                            Response::notFound('Route không tồn tại');
                        }
                }
                break;

            case 'posts':
                $controller = new PostController();

                switch ($action) {
                    case '':
                        $controller->getAll();
                        break;

                    case 'create':
                        AuthMiddleware::authenticate();
                        $controller->create();
                        break;

                    case 'get-by-slug':
                        $controller->getBySlug();
                        break;

                    case 'categories':
                        $controller->getCategories();
                        break;

                    case 'languages':
                        $controller->getLanguages();
                        break;

                    default:
                        // Nếu action là một ID
                        if (is_numeric($action)) {
                            $id = $action;
                            if ($requestMethod === 'GET') {
                                $controller->getById($id);
                            } elseif ($requestMethod === 'PUT' || $requestMethod === 'PATCH' || $requestMethod === 'POST') {
                                AuthMiddleware::authenticate();
                                $controller->update($id);
                            } elseif ($requestMethod === 'DELETE') {
                                AuthMiddleware::authenticate();
                                $controller->delete($id);
                            } else {
                                Response::error('Phương thức không được hỗ trợ', 405);
                            }
                        } else {
                            Response::notFound('Route không tồn tại');
                        }
                }
                break;

            case 'configs':
                $controller = new ConfigController();

                switch ($action) {
                    case '':
                        $controller->getAll();
                        break;

                    case 'get':
                        $controller->get();
                        break;

                    case 'get-group':
                        $controller->getGroup();
                        break;

                    case 'set':
                        AuthMiddleware::authenticate();
                        $controller->set();
                        break;

                    case 'set-bulk':
                        AuthMiddleware::authenticate();
                        $controller->setBulk();
                        break;

                    case 'delete':
                        AuthMiddleware::authenticate();
                        $controller->delete();
                        break;

                    default:
                        Response::notFound('Route không tồn tại');
                }
                break;

            // Thêm routes cho sections API
            case 'sections':
                $controller = new SectionController();

                switch ($action) {
                    case '':
                        $controller->getAll();
                        break;

                    case 'create':
                        AuthMiddleware::authenticate();
                        $controller->create();
                        break;

                    case 'update-order':
                        AuthMiddleware::authenticate();
                        $controller->updateSectionOrder();
                        break;

                    default:
                        // Nếu action là một ID
                        if (is_numeric($action)) {
                            $id = $action;
                            if ($requestMethod === 'GET') {
                                $controller->getById($id);
                            } elseif ($requestMethod === 'PUT' || $requestMethod === 'PATCH' || $requestMethod === 'POST') {
                                AuthMiddleware::authenticate();
                                $controller->update($id);
                            } elseif ($requestMethod === 'DELETE') {
                                AuthMiddleware::authenticate();
                                $controller->delete($id);
                            } else {
                                Response::error('Phương thức không được hỗ trợ', 405);
                            }
                        } else {
                            Response::notFound('Route không tồn tại');
                        }
                }
                break;

            case 'section-items':
                $controller = new SectionController();

                switch ($action) {
                    case 'update-order':
                        AuthMiddleware::authenticate();
                        $controller->updateItemOrder();
                        break;

                    default:
                        Response::notFound('Route không tồn tại');
                }
                break;
            // Thêm route cho pages
            case 'pages':
                $controller = new PageController();

                switch ($action) {
                    case '':
                        $controller->getAll();
                        break;

                    case 'create':
                        AuthMiddleware::authenticate();
                        $controller->create();
                        break;

                    default:
                        if (is_numeric($action)) {
                            $id = $action;
                            if (isset($pathParts[2]) && $pathParts[2] === 'sections') {
                                if (isset($pathParts[3]) && $pathParts[3] === 'clear' && $requestMethod === 'DELETE') {
                                    // Route này để xóa tất cả sections
                                    $controller->clearSections($id);
                                } elseif ($requestMethod === 'GET') {
                                    $controller->getSections($id);
                                } elseif ($requestMethod === 'POST') {
                                    AuthMiddleware::authenticate();
                                    $controller->addSection($id);
                                }
                            } elseif ($requestMethod === 'GET') {
                                $controller->getById($id);
                            } elseif ($requestMethod === 'PUT' || $requestMethod === 'PATCH' || $requestMethod === 'POST') {
                                AuthMiddleware::authenticate();
                                $controller->update($id);
                            } elseif ($requestMethod === 'DELETE') {
                                AuthMiddleware::authenticate();
                                $controller->delete($id);
                            } else {
                                Response::error('Phương thức không được hỗ trợ', 405);
                            }
                        } else {
                            Response::notFound('Route không tồn tại');
                        }
                }
                break;
            // Nếu không có controller, hiển thị thông tin API
            case '':
                Response::json([
                    'status' => 'success',
                    'message' => 'API hoạt động bình thường',
                    'data' => [
                        'version' => '1.0.0',
                        'endpoints' => [
                            'auth' => [
                                '/auth/login',
                                '/auth/register',
                                '/auth/me (yêu cầu xác thực)',
                                '/auth/change-password (yêu cầu xác thực)',
                                '/auth/logout (yêu cầu xác thực)'
                            ],
                            'languages' => [
                                '/languages',
                                '/languages/{id}',
                                '/languages/create (yêu cầu xác thực)',
                                '/languages/default',
                                '/languages/set-default/{id} (yêu cầu xác thực)',
                                '/languages/{id} - PUT/DELETE (yêu cầu xác thực)'
                            ],
                            'categories' => [
                                '/categories',
                                '/categories/{id}',
                                '/categories/create (yêu cầu xác thực)',
                                '/categories/get-by-slug',
                                '/categories/{id} - PUT/DELETE (yêu cầu xác thực)'
                            ],
                            'posts' => [
                                '/posts',
                                '/posts/{id}',
                                '/posts/create (yêu cầu xác thực)',
                                '/posts/get-by-slug',
                                '/posts/{id} - PUT/DELETE (yêu cầu xác thực)'
                            ],
                            'configs' => [
                                '/configs',
                                '/configs/get',
                                '/configs/get-group',
                                '/configs/set (yêu cầu xác thực)',
                                '/configs/set-bulk (yêu cầu xác thực)',
                                '/configs/delete (yêu cầu xác thực)'
                            ],
                            'sections' => [
                                '/sections',
                                '/sections/{id}',
                                '/sections/create (yêu cầu xác thực)',
                                '/sections/update-order (yêu cầu xác thực)',
                                '/sections/{id} - PUT/DELETE (yêu cầu xác thực)',
                                '/section-items/update-order (yêu cầu xác thực)'
                            ],
                            'pages' => [
                                '/pages',
                                '/pages/{id}',
                                '/pages/create (yêu cầu xác thực)',
                                '/pages/{id}/sections',
                                '/pages/{id} - PUT/DELETE (yêu cầu xác thực)'
                            ],
                        ]
                    ]
                ]);
                break;

            default:
                Response::notFound('API route không tồn tại');
        }
    } catch (Exception $e) {
        Response::serverError($e->getMessage());
    }
} else {
    // Xử lý các yêu cầu giao diện
    require_once __DIR__ . '/utils/View.php';

    try {
        // Kiểm tra quyền truy cập admin nếu cần
        $isAdmin = strpos($path, '/admin') === 0;

        // Xử lý routes cho giao diện
        switch ($controllerName) {
            case 'admin':
                // Kiểm tra xem đã đăng nhập chưa (trừ trang login)
                if ($action !== 'login') {
                    // Kiểm tra cookie hoặc session để xác thực
                    if (!isset($_COOKIE['admin_token'])) {
                        // Chuyển hướng đến trang đăng nhập
                        header('Location: ' . $basePath . './admin/login');
                        exit;
                    }
                }

                switch ($action) {
                    case 'login':
                        View::render('./admin/login');
                        break;

                    case 'dashboard':
                        View::render('./admin/dashboard');
                        break;

                    case 'languages':
                        View::render('./admin/languages');
                        break;

                    case 'categories':
                        View::render('./admin/categories');
                        break;
                    case 'category-edit':
                        View::render('./admin/category-edit');
                        break;

                    case 'posts':
                        View::render('./admin/posts');
                        break;

                    case 'post-edit':
                        View::render('./admin/post-edit');
                        break;


                    case 'sections':
                        View::render('./admin/sections');
                        break;

                    case 'section-edit':
                        View::render('./admin/section-edit');
                        break;
                    case 'pages':
                        View::render('./admin/pages');
                        break;

                    case 'page-edit':
                        View::render('./admin/page-edit');
                        break;

                    case 'configs':
                        View::render('./admin/configs');
                        break;

                    case '':
                        // Chuyển hướng đến dashboard nếu truy cập /admin
                        header('Location: ' . $basePath . './admin/dashboard');
                        exit;

                    default:
                        // Trang 404 cho admin
                        View::render('./admin/404');
                }
                break;

            case '':
                // Trang chủ frontend
                View::render('frontend/home');
                break;

            default:
                // Kiểm tra xem có phải là trang frontend không
                $viewPath = 'frontend/' . $controllerName;
                if (View::exists($viewPath)) {
                    View::render($viewPath);
                } else {
                    // Trang 404 cho frontend
                    View::render('frontend/404');
                }
        }
    } catch (Exception $e) {
        // Hiển thị trang lỗi
        View::render('error', ['message' => $e->getMessage()]);
    }
}
