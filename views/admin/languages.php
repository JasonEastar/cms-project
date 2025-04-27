<?php
// views/admin/categories.php
// Giao diện quản lý danh mục cải tiến với design responsive và đẹp hơn

$page_title = 'Quản lý ngôn ngữ - Admin CMS';
ob_start();
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Quản lý ngôn ngữ</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#languageModal">
            <i class="bi bi-plus-lg"></i> Thêm ngôn ngữ
        </button>
    </div>

    <!-- Bảng danh sách ngôn ngữ -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="10%">Mã</th>
                            <th>Tên ngôn ngữ</th>
                            <th width="10%">Thứ tự</th>
                            <th width="10%">Mặc định</th>
                            <th width="10%">Trạng thái</th>
                            <th width="15%">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="languagesTable">
                        <!-- Dữ liệu sẽ được load bằng JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa ngôn ngữ -->
<div class="modal fade" id="languageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm ngôn ngữ mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="languageForm">
                    <input type="hidden" id="languageId">
                    
                    <div class="mb-3">
                        <label for="languageCode" class="form-label">Mã ngôn ngữ</label>
                        <input type="text" class="form-control" id="languageCode" required>
                        <div class="form-text">Ví dụ: vi, en, zh, jp, ...</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="languageName" class="form-label">Tên ngôn ngữ</label>
                        <input type="text" class="form-control" id="languageName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sortOrder" class="form-label">Thứ tự hiển thị</label>
                        <input type="number" class="form-control" id="sortOrder" value="0">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isDefault">
                        <label class="form-check-label" for="isDefault">Đặt làm ngôn ngữ mặc định</label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Kích hoạt</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveLanguage()">Lưu</button>
            </div>
        </div>
    </div>
</div>

<script>
// Biến toàn cục
let languages = [];
const languageModal = new bootstrap.Modal(document.getElementById('languageModal'));

// Load danh sách ngôn ngữ khi trang load
document.addEventListener('DOMContentLoaded', function() {
    loadLanguages();
});

// Hàm load danh sách ngôn ngữ
function loadLanguages() {
    fetch('/api/languages', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            languages = result.data.languages;
            renderLanguagesTable();
        } else {
            showToast('error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Không thể tải danh sách ngôn ngữ');
    });
}

// Hàm render bảng ngôn ngữ
function renderLanguagesTable() {
    const tbody = document.getElementById('languagesTable');
    tbody.innerHTML = '';
    
    languages.forEach(lang => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${lang.id}</td>
            <td><code>${lang.code}</code></td>
            <td>${lang.name}</td>
            <td>${lang.sort_order}</td>
            <td>
                ${lang.is_default ? '<span class="badge bg-primary">Mặc định</span>' : ''}
            </td>
            <td>
                ${lang.is_active ? 
                    '<span class="badge bg-success">Hoạt động</span>' : 
                    '<span class="badge bg-danger">Ngừng</span>'}
            </td>
            <td>
                <button class="btn btn-sm btn-info" onclick="editLanguage(${lang.id})" title="Sửa">
                    <i class="bi bi-pencil"></i>
                </button>
                ${!lang.is_default ? `
                    <button class="btn btn-sm btn-warning" onclick="setDefault(${lang.id})" title="Đặt mặc định">
                        <i class="bi bi-star"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteLanguage(${lang.id})" title="Xóa">
                        <i class="bi bi-trash"></i>
                    </button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Hàm hiển thị form thêm ngôn ngữ
function showAddLanguageForm() {
    document.getElementById('languageForm').reset();
    document.getElementById('languageId').value = '';
    document.querySelector('#languageModal .modal-title').textContent = 'Thêm ngôn ngữ mới';
    languageModal.show();
}

// Hàm hiển thị form sửa ngôn ngữ
function editLanguage(id) {
    const language = languages.find(l => l.id === id);
    if (!language) return;
    
    document.getElementById('languageId').value = language.id;
    document.getElementById('languageCode').value = language.code;
    document.getElementById('languageName').value = language.name;
    document.getElementById('sortOrder').value = language.sort_order;
    document.getElementById('isDefault').checked = language.is_default;
    document.getElementById('isActive').checked = language.is_active;
    
    document.querySelector('#languageModal .modal-title').textContent = 'Sửa ngôn ngữ';
    languageModal.show();
}

// Hàm lưu ngôn ngữ
function saveLanguage() {
    const id = document.getElementById('languageId').value;
    const data = {
        code: document.getElementById('languageCode').value,
        name: document.getElementById('languageName').value,
        sort_order: parseInt(document.getElementById('sortOrder').value) || 0,
        is_default: document.getElementById('isDefault').checked ? 1 : 0,
        is_active: document.getElementById('isActive').checked ? 1 : 0
    };
    
    const url = id ? 
        `/api/languages/${id}` : 
        '/api/languages/create';
    
    const method = id ? 'PUT' : 'POST';
    
    showLoading();
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        
        if (result.status === 'success') {
            showToast('success', result.message);
            languageModal.hide();
            loadLanguages();
        } else {
            showToast('error', result.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('error', 'Đã có lỗi xảy ra');
    });
}

// Hàm đặt ngôn ngữ mặc định
function setDefault(id) {
    if (!confirm('Bạn có chắc muốn đặt ngôn ngữ này làm mặc định?')) {
        return;
    }
    
    showLoading();
    
    fetch(`/api/languages/set-default/${id}`, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        
        if (result.status === 'success') {
            showToast('success', result.message);
            loadLanguages();
        } else {
            showToast('error', result.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('error', 'Đã có lỗi xảy ra');
    });
}

// Hàm xóa ngôn ngữ
function deleteLanguage(id) {
    if (!confirm('Bạn có chắc muốn xóa ngôn ngữ này? Điều này sẽ xóa tất cả bản dịch liên quan!')) {
        return;
    }
    
    showLoading();
    
    fetch(`/api/languages/${id}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        
        if (result.status === 'success') {
            showToast('success', result.message);
            loadLanguages();
        } else {
            showToast('error', result.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('error', 'Đã có lỗi xảy ra');
    });
}

// Xử lý sự kiện khi modal đóng
document.getElementById('languageModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('languageForm').reset();
    document.getElementById('languageId').value = '';
});
</script>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>