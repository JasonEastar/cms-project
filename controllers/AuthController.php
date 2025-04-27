<?php
// controllers/AuthController.php
// Controller xử lý đăng nhập và xác thực

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // Phương thức xử lý đăng nhập
    public function login()
    {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['email']) || !isset($data['password'])) {
            return Response::validationError(['email' => 'Email là bắt buộc', 'password' => 'Mật khẩu là bắt buộc']);
        }

        // Xác thực người dùng
        $user = $this->userModel->authenticate($data['email'], $data['password']);

        if (!$user) {
            return Response::unauthorized('Email hoặc mật khẩu không đúng');
        }

        // Tạo JWT token
        $token = JWT::generate([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        // Trả về thông tin người dùng và token
        return Response::success([
            'user' => $user,
            'token' => $token
        ], 'Đăng nhập thành công');
    }

    // Phương thức lấy thông tin người dùng hiện tại
    public function me()
    {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Thông tin người dùng đã được lưu trong $_REQUEST['user'] bởi AuthMiddleware
        $user = $_REQUEST['user'] ?? null;

        if (!$user) {
            return Response::unauthorized('Chưa đăng nhập');
        }

        return Response::success(['user' => $user], 'Lấy thông tin người dùng thành công');
    }

    // Phương thức đổi mật khẩu
    public function changePassword()
    {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Thông tin người dùng đã được lưu trong $_REQUEST['user'] bởi AuthMiddleware
        $user = $_REQUEST['user'] ?? null;

        if (!$user) {
            return Response::unauthorized('Chưa đăng nhập');
        }

        // Lấy dữ liệu từ request body
        $data = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['current_password']) || !isset($data['new_password'])) {
            return Response::validationError([
                'current_password' => 'Mật khẩu hiện tại là bắt buộc',
                'new_password' => 'Mật khẩu mới là bắt buộc'
            ]);
        }

        // Xác thực mật khẩu hiện tại
        $authenticatedUser = $this->userModel->authenticate($user['email'], $data['current_password']);

        if (!$authenticatedUser) {
            return Response::validationError(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        // Đổi mật khẩu
        $this->userModel->update($user['id'], [
            'password' => $data['new_password']
        ]);

        return Response::success([], 'Đổi mật khẩu thành công');
    }

    // Phương thức đăng xuất (vô hiệu hóa token ở phía client)
    public function logout()
    {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Thực tế là JWT không thể vô hiệu hóa ở phía server
        // Việc đăng xuất sẽ được thực hiện ở phía client bằng cách xóa token

        return Response::success([], 'Đăng xuất thành công');
    }


    // Phương thức xử lý đăng ký tài khoản
    public function register()
    {
        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::error('Phương thức không được hỗ trợ', 405);
        }

        // Lấy dữ liệu từ request body
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Nếu không parse được JSON
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return Response::validationError(['json' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()]);
        }

        // Kiểm tra dữ liệu đầu vào
        $errors = [];

        if (!isset($data['name']) || empty($data['name'])) {
            $errors['name'] = 'Tên hiển thị là bắt buộc';
        }

        if (!isset($data['email']) || empty($data['email'])) {
            $errors['email'] = 'Email là bắt buộc';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $errors['password'] = 'Mật khẩu là bắt buộc';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        if (isset($data['password_confirmation']) && $data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Xác nhận mật khẩu không khớp';
        }

        // Nếu có lỗi, trả về thông báo
        if (!empty($errors)) {
            return Response::validationError($errors);
        }

        // Kiểm tra xem email đã tồn tại chưa
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser) {
            return Response::validationError(['email' => 'Email này đã được sử dụng']);
        }

        // Tạo người dùng mới
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = $this->userModel->create($userData);

        // Nếu tạo người dùng thất bại
        if (!$user) {
            return Response::serverError('Không thể tạo tài khoản. Vui lòng thử lại sau.');
        }

        // Tạo JWT token
        $token = JWT::generate([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        // Trả về thông tin người dùng và token
        return Response::success([
            'user' => $user,
            'token' => $token
        ], 'Đăng ký tài khoản thành công', 201);
    }
}
