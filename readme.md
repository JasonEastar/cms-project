# Tài liệu API

## Giới thiệu

Đây là tài liệu mô tả các API của hệ thống quản lý nội dung đa ngôn ngữ xây dựng bằng PHP theo mô hình MVC.

Tất cả API đều trả về dữ liệu dưới dạng JSON với cấu trúc:

```json
{
  "status": "success|error",
  "message": "Thông báo",
  "data": { ... } // Dữ liệu trả về (chỉ có khi status = success)
}
```

## Xác thực (Authentication)

### Đăng nhập

- URL: `/api/auth/login`
- Method: `POST`
- Body:
  ```json
  {
    "email": "admin@example.com",
    "password": "password"
  }
  ```
- Response:
  ```json
  {
    "status": "success",
    "message": "Đăng nhập thành công",
    "data": {
      "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@example.com",
        "created_at": "2023-01-01 00:00:00"
      },
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
  }
  ```

### Lấy thông tin người dùng hiện tại

- URL: `/api/auth/me`
- Method: `GET`
- Headers: `Authorization: Bearer {token}`
- Response:
  ```json
  {
    "status": "success",
    "message": "Lấy thông tin người dùng thành công",
    "data": {
      "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@example.com",
        "created_at": "2023-01-01 00:00:00"
      }
    }
  }
  ```

### Đổi mật khẩu

- URL: `/api/auth/change-password`
- Method: `POST`
- Headers: `Authorization: Bearer {token}`
- Body:
  ```json
  {
    "current_password": "password",
    "new_password": "new_password"
  }
  ```
- Response:
  ```json
  {
    "status": "success",
    "message": "Đổi mật khẩu thành công",
    "data": {}
  }
  ```

### Đăng xuất

- URL: `/api/auth/logout`
- Method: `POST`
- Headers: `Authorization: Bearer {token}`
- Response:
  ```json
  {
    "status": "success",
    "message": "Đăng xuất thành công",
    "data": {}
  }
  ```

## Danh mục (Categories)

### Lấy danh sách danh mục

- URL: `/api/categories?lang=vi&hierarchical=true`
- Method: `GET`
- Query params:
  - `lang`: Ngôn ngữ (mặc định: vi)
  - `hierarchical`: Lấy danh mục phân cấp (mặc định: false)
- Response:
  ```json
  {
    "status": "success",
    "message": "Lấy danh sách danh mục thành công",
    "data": {