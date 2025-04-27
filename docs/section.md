# API Documentation - Sections & Section Items

## Base URL
`/api`

## Authentication
Tất cả các request POST, PUT, PATCH, DELETE yêu cầu header Authorization với Bearer token.

```
Authorization: Bearer <token>
```

## Sections API

### 1. Lấy danh sách sections
`GET /sections`

**Query Parameters:**
- `lang` (optional): Mã ngôn ngữ (vi, en, zh...)
- `is_active` (optional): Lọc theo trạng thái active (0 hoặc 1)
- `show_on_mobile` (optional): Lọc theo hiển thị trên mobile (0 hoặc 1)

**Response:**
```json
{
    "status": "success",
    "message": "Lấy danh sách sections thành công",
    "data": {
        "sections": [
            {
                "id": 1,
                "code": "services",
                "template": "default",
                "image": "/uploads/sections/1234.jpg",
                "icon": null,
                "is_active": 1,
                "show_on_mobile": 1,
                "sort_order": 0,
                "module_name": "Dịch vụ",
                "title": "Dịch vụ của chúng tôi",
                "description": "Mô tả về dịch vụ...",
                "language_code": "vi"
            }
        ]
    }
}
```

### 2. Lấy chi tiết section
`GET /sections/{id}`

**Query Parameters:**
- `lang` (optional): Mã ngôn ngữ

**Response:**
```json
{
    "status": "success",
    "message": "Lấy thông tin section thành công",
    "data": {
        "section": {
            "id": 1,
            "code": "services",
            "template": "default",
            "image": "/uploads/sections/1234.jpg",
            "icon": null,
            "is_active": 1,
            "show_on_mobile": 1,
            "sort_order": 0,
            "module_name": "Dịch vụ",
            "title": "Dịch vụ của chúng tôi",
            "description": "Mô tả về dịch vụ...",
            "language_code": "vi",
            "translations": [
                {
                    "language_code": "vi",
                    "language_name": "Tiếng Việt",
                    "module_name": "Dịch vụ",
                    "title": "Dịch vụ của chúng tôi",
                    "description": "Mô tả về dịch vụ..."
                },
                {
                    "language_code": "en",
                    "language_name": "English",
                    "module_name": "Services",
                    "title": "Our Services",
                    "description": "Description about services..."
                }
            ],
            "items": [
                {
                    "id": 1,
                    "section_id": 1,
                    "type": "text",
                    "image": null,
                    "icon": "fas fa-truck",
                    "link": null,
                    "is_active": 1,
                    "sort_order": 0,
                    "title": "Vận chuyển đường bộ",
                    "description": "Mô tả về vận chuyển đường bộ...",
                    "language_code": "vi"
                }
            ]
        }
    }
}
```

### 3. Tạo section mới
`POST /sections/create`

**Form Data:**
- `code` (optional): Mã section (tự động tạo nếu để trống)
- `template` (optional): Template hiển thị (default: "default")
- `is_active` (optional): Trạng thái active (default: 1)
- `show_on_mobile` (optional): Hiển thị trên mobile (default: 1)
- `sort_order` (optional): Thứ tự sắp xếp (default: 0)
- `image` (file, optional): File ảnh
- `translations` (JSON string): Mảng các bản dịch

**Translations Format:**
```json
[
    {
        "language_code": "vi",
        "module_name": "Dịch vụ",
        "title": "Dịch vụ của chúng tôi",
        "description": "Mô tả về dịch vụ..."
    },
    {
        "language_code": "en",
        "module_name": "Services",
        "title": "Our Services",
        "description": "Description about services..."
    }
]
```

### 4. Cập nhật section
`POST /sections/{id}`

**Form Data:** Giống như tạo mới, tất cả các field là optional

### 5. Xóa section
`DELETE /sections/{id}`

### 6. Cập nhật thứ tự sections
`POST /sections/update-order`

**Request Body (JSON):**
```json
{
    "orders": [
        {"id": 1, "sort_order": 0},
        {"id": 2, "sort_order": 1},
        {"id": 3, "sort_order": 2}
    ]
}
```

## Section Items API

### 1. Lấy danh sách items của section
`GET /sections/{sectionId}/items`

**Query Parameters:**
- `lang` (optional): Mã ngôn ngữ
- `is_active` (optional): Lọc theo trạng thái active

### 2. Lấy chi tiết item
`GET /section-items/{id}`

**Query Parameters:**
- `lang` (optional): Mã ngôn ngữ

### 3. Tạo item mới
`POST /section-items/create`

**Form Data:**
- `section_id` (required): ID của section
- `type` (optional): Loại item (text, image, icon) (default: "text")
- `link` (optional): Đường dẫn
- `is_active` (optional): Trạng thái active (default: 1)
- `sort_order` (optional): Thứ tự sắp xếp (default: 0)
- `image` (file, optional): File ảnh
- `translations` (JSON string): Mảng các bản dịch

**Translations Format:**
```json
[
    {
        "language_code": "vi",
        "title": "Vận chuyển đường bộ",
        "description": "Mô tả về vận chuyển đường bộ..."
    },
    {
        "language_code": "en",
        "title": "Road Transportation",
        "description": "Description about road transportation..."
    }
]
```

### 4. Cập nhật item
`POST /section-items/{id}`

**Form Data:** Giống như tạo mới, tất cả các field là optional

### 5. Xóa item
`DELETE /section-items/{id}`

### 6. Cập nhật thứ tự items
`POST /section-items/update-order`

**Request Body (JSON):**
```json
{
    "orders": [
        {"id": 1, "sort_order": 0},
        {"id": 2, "sort_order": 1},
        {"id": 3, "sort_order": 2}
    ]
}
```

### 7. Di chuyển item sang section khác
`POST /section-items/{itemId}/move`

**Request Body (JSON):**
```json
{
    "section_id": 2
}
```

## Response Codes
- `200`: Thành công
- `201`: Tạo mới thành công
- `400`: Lỗi validation
- `401`: Chưa xác thực
- `403`: Không có quyền
- `404`: Không tìm thấy
- `405`: Phương thức không được hỗ trợ
- `500`: Lỗi server

## Error Response Format
```json
{
    "status": "error",
    "message": "Mô tả lỗi",
    "errors": {
        "field_name": "Chi tiết lỗi"
    }
}
```