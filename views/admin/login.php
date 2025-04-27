<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Đăng nhập - Admin CMS'; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .login-container {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="login-container bg-white rounded-lg shadow-md p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">CMS Admin</h1>
            <p class="text-gray-600 mt-2">Đăng nhập để quản lý hệ thống</p>
        </div>

        <div id="error-message" class="hidden mb-4 bg-red-100 text-red-700 p-3 rounded"></div>
        
        <form id="login-form" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nhập địa chỉ email"
                    value="jason@gmail.com"
                >
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nhập mật khẩu"
                    value="123qwe"
                >
            </div>
            
            <div>
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    id="login-btn"
                >
                    Đăng nhập
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');
            const errorMessage = document.getElementById('error-message');
            
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Ẩn thông báo lỗi
                errorMessage.classList.add('hidden');
                
                // Disable nút đăng nhập
                loginBtn.disabled = true;
                loginBtn.textContent = 'Đang xử lý...';
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                try {
                    const response = await fetch('/api/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email, password })
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        // Lưu token và thông tin người dùng
                        localStorage.setItem('admin_token', data.data.token);
                        localStorage.setItem('admin_user', JSON.stringify(data.data.user));
                        
                        // Tạo cookie để backend có thể kiểm tra
                        document.cookie = `admin_token=${data.data.token}; path=/; max-age=86400`;
                        
                        // Chuyển hướng đến trang dashboard
                        window.location.href = '/admin/dashboard';
                    } else {
                        // Hiển thị thông báo lỗi
                        errorMessage.textContent = data.message || 'Đăng nhập thất bại!';
                        errorMessage.classList.remove('hidden');
                    }
                } catch (error) {
                    errorMessage.textContent = 'Lỗi kết nối: ' + error.message;
                    errorMessage.classList.remove('hidden');
                } finally {
                    // Enable lại nút đăng nhập
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Đăng nhập';
                }
            });
        });
    </script>
</body>
</html>