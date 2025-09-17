@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card product-form-card">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-edit"></i> Chỉnh sửa xe</h4>
                        <a class="btn btn-outline-light btn-sm" href="{{ route('admin.products.index') }}">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra!</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
                        @csrf
                        @method('PATCH')
                        
                        <!-- Basic Information -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên xe & Model</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $product->name) }}"
                                            placeholder="VD: Toyota Camry 2024" required>
                                        <div class="form-text">Nhập tên đầy đủ và model của xe</div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Danh mục xe</label>
                                        <select class="form-control @error('category_id') is-invalid @enderror" id="category_id"
                                            name="category_id" required>
                                            <option value="">Chọn danh mục</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price and Stock -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-dollar-sign"></i> Giá cả & Tồn kho</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Giá bán ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                                id="price" name="price" value="{{ old('price', $product->price) }}" 
                                                placeholder="0.00" min="0" required>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Số lượng tồn kho</label>
                                        <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                            id="quantity" name="quantity" value="{{ old('quantity', $product->quantity) }}"
                                            placeholder="0" min="0" required>
                                        @error('quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-image"></i> Hình ảnh xe</h5>
                            <div class="image-upload-container">
                                @if($product->image)
                                    <div class="current-image mb-3">
                                        <h6><i class="fas fa-image"></i> Ảnh hiện tại:</h6>
                                        <div class="current-image-container">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                                 class="current-img">
                                            <div class="current-image-overlay">
                                                <span class="badge bg-success">Ảnh hiện tại</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-content">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <h6>Kéo thả ảnh mới vào đây hoặc click để chọn</h6>
                                        <p class="text-muted">Hỗ trợ: JPEG, PNG, JPG, GIF - Tối đa 2MB</p>
                                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                            id="image" name="image" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('image').click()">
                                            <i class="fas fa-folder-open"></i> Chọn ảnh mới
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="image-preview" id="imagePreview" style="display: none;">
                                    <div class="preview-container">
                                        <img id="previewImage" src="" alt="Preview" class="preview-img">
                                        <div class="preview-overlay">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </div>
                                    </div>
                                    <div class="preview-info">
                                        <small class="text-muted" id="fileInfo"></small>
                                        <div class="alert alert-info mt-2">
                                            <i class="fas fa-info-circle"></i> Ảnh mới này sẽ thay thế ảnh hiện tại
                                        </div>
                                    </div>
                                </div>
                                
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-align-left"></i> Mô tả chi tiết</h5>
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả xe</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="5" 
                                    placeholder="Mô tả các tính năng, thông số kỹ thuật và thông tin bổ sung của xe...">{{ old('description', $product->description) }}</textarea>
                                <div class="form-text">Mô tả chi tiết về xe, các tính năng, thông số kỹ thuật</div>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-actions">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Hủy bỏ
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Cập nhật xe
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-form-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.form-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 15px;
    border-left: 4px solid #667eea;
}

.section-title {
    color: #495057;
    margin-bottom: 20px;
    font-weight: 600;
}

.current-image-container {
    position: relative;
    display: inline-block;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.current-img {
    max-width: 300px;
    max-height: 200px;
    object-fit: cover;
}

.current-image-overlay {
    position: absolute;
    top: 10px;
    left: 10px;
}

.image-upload-container {
    position: relative;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    background: white;
    cursor: pointer;
}

.upload-area:hover,
.upload-area.dragover {
    border-color: #667eea;
    background: #f8f9ff;
}

.upload-content h6 {
    color: #495057;
    margin-bottom: 10px;
}

.image-preview {
    margin-top: 20px;
}

.preview-container {
    position: relative;
    display: inline-block;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.preview-img {
    max-width: 300px;
    max-height: 200px;
    object-fit: cover;
}

.preview-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.preview-container:hover .preview-overlay {
    opacity: 1;
}

.preview-info {
    margin-top: 10px;
    text-align: center;
}

.form-actions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    margin-top: 30px;
}

.input-group-text {
    background: #e9ecef;
    border-color: #ced4da;
}

@media (max-width: 768px) {
    .form-section {
        padding: 20px;
    }
    
    .upload-area {
        padding: 30px 20px;
    }
    
    .preview-img,
    .current-img {
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const fileInfo = document.getElementById('fileInfo');
    const form = document.getElementById('productForm');

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // Click to upload
    uploadArea.addEventListener('click', function() {
        imageInput.click();
    });

    // File input change
    imageInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Vui lòng chọn file ảnh hợp lệ!');
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File ảnh không được lớn hơn 2MB!');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.style.display = 'block';
            uploadArea.style.display = 'none';
            
            // Show file info
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            fileInfo.textContent = `${file.name} (${sizeInMB} MB)`;
        };
        reader.readAsDataURL(file);
    }

    function removeImage() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
        uploadArea.style.display = 'block';
        fileInfo.textContent = '';
    }

    // Make removeImage function global
    window.removeImage = removeImage;

    // Form submission with loading state
    form.addEventListener('submit', function() {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
        submitBtn.disabled = true;
    });
});
</script>
@endsection
