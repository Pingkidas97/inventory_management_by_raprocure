@extends('vendor.layouts.app_second')
@section('title', 'Product Gallery - Raprocure')

@section('content')
<section class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vendor.products.index') }}">Manage Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Product Gallery</li>
        </ol>
    </nav>

    <section class="manage-product card">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">Product Gallery - {{ $product->name }}</h1>
                <a href="{{ route('vendor.products.index') }}" class="ra-btn ra-btn-primary d-flex align-items-center font-size-12">
                    <span class="bi bi-arrow-left-square font-size-11"></span>
                    <span class="font-size-11">Back</span>
                </a>
            </div>

            <div class="card-body">
                <form id="gallery-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-12 text-center" style="border-bottom: 1px solid #e5e5e5;padding-bottom: 22px; padding-top: 22px;">
                            <div class="col-ting1">
                                <div class="control-group file-box" id="upload-box">
                                    <div class="image-box image-box1 text-center">
                                        <p><i class="fa fa-cloud-upload"></i><br>Upload Gallery Images</p>
                                        <img src="" name="logo_image_view" alt="">
                                    </div>
                                </div>
                                <input class="d-none" type="file" name="images[]" id="gallery-images" accept="image/*" multiple>
                            </div>
                        </div>

                        <div class="list_gallery margin_top_btm_20 col-md-12">
                            <div class="gallery_list">
                                <ul class="img-list">
                                    @forelse($product->gallery as $image)
                                        <li>
                                            <img style="width:149px;height:149px;object-fit:cover;"
                                                 src="{{ asset('public/uploads/product/' . $image->image) }}"
                                                 data="{{ $image->image }}" data-id="{{ $image->id }}">
                                            <span class="remove_db">X</span>
                                        </li>

                                    @empty
                                        <li class="text-muted">No images uploaded yet</li>
                                    @endforelse
                                </ul>
                                <span class="text-muted"><strong>Image size should not be less than 512 X 350 pixels.</strong></span>
                            </div>
                        </div>

                        <div class="text-center margin_top_btm_20 col-md-12">
                            <button type="submit" class="btn btn-primary submit-btn">
                                Save Changes
                            </button>
                            <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>
@endsection

<style>
.file-box .image-box .fa {
    color: #45bd41;
    font-size: 30px;
}
.col-ting1 {
    float: none;
    display: inline-block;
    width: 208px;
    border: 1px solid #e5e5e5;
    cursor: pointer;
    border-radius: 5px;
    color: #45bd41;
}
.table-responsive {
    display: block;
    width: 100%;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch;
}
.control-group {
    float: left;
    width: 100%;
    padding: 10px 0;
}
.file-box .image-box p {
    position: relative;
    top: 25%;
    color: #ccc;
    margin-top: 20px;
}
.gallery_list {
    float: left;
    width: 100%;
    padding: 15px;
}
.gallery_list ul {
    margin: 0;
    padding: 0;
    float: left;
    width: 100%;
}
.gallery_list ul li {
    margin: 0 0 20px 0;
    padding: 0;
    float: left;
    padding-right: 30px;
    list-style: none;
    position: relative;
}
.gallery_list ul li img {
    border: 2px solid #bdbaba;
    border-radius: 5px;
    object-fit: cover;
}
.remove,
.remove_db {
    position: absolute;
    right: 20px;
    z-index: 9999;
    top: -8px;
    color: #fff;
    font-size: 14px;
    background: #c8202d;
    border-radius: 50%;
    height: 25px;
    width: 25px;
    line-height: 25px;
    text-align: center;
    cursor: pointer;
}
.blue.btn {
    color: #FFFFFF;
    background-color: #c8202d;
}
.text-center .btn {
    height: 40px;
}
.btn {
    box-shadow: none !important;
}
.btn {
    border-width: 0;
    padding: 8px 45px !important;
    font-size: 14px;
    outline: none !important;
    background-image: none !important;
    filter: none;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    box-shadow: none;
    text-shadow: none;
    text-transform: uppercase;
    border-radius: 0px !important;
    display: block;
}
.blue.btn:hover,
.blue.btn:focus,
.blue.btn:active,
.blue.btn.active {
    color: #FFFFFF !important;
    background-color: #252525 !important;
}
</style>

@section('scripts')
<script>
$(document).ready(function() {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Cancel button
    $(".cancel-button").click(function() {
        window.location.href = "{{ route('vendor.products.index') }}";
    });

    // Trigger file input when upload box is clicked
    $(document).on('click', '#upload-box', function() {
        $('#gallery-images').trigger('click');
    });

    // Handle file selection
    $(document).on('change', '#gallery-images', function() {
        const files = this.files;
        const formData = new FormData();

        // Show loading state
        $(".image-box1").find('p').find('i').removeClass('fa-cloud-upload').addClass('fa-spin fa-spinner');

        // Validate files
        let validFiles = true;
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        for (let i = 0; i < files.length; i++) {
            if (!validTypes.includes(files[i].type)) {
                validFiles = false;
                alert('Only JPG, JPEG, PNG, and GIF images are allowed.');
                break;
            }
            if (files[i].size > maxSize) {
                validFiles = false;
                alert('Image size should not exceed 2MB.');
                break;
            }
            formData.append('images[]', files[i]); // Append actual file object
        }

        if (!validFiles) {
            $(".image-box1").find('p').find('i').removeClass('fa-spin fa-spinner').addClass('fa-cloud-upload');
            $(this).val('');
            return;
        }

        // Upload files via AJAX
        $.ajax({
            url: "{{ route('vendor.products.gallery.upload-temp') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Append new images to gallery
                    response.images.forEach(function(image) {
                        $('.img-list').append(`
                            <li>
                                <img style="width:149px;height:149px;" 
                                     src="${image.temp_url}" 
                                     data="${image.name}">
                                <span class="remove">X</span>
                            </li>
                        `);
                    });
                } else {
                    alert(response.message || 'Error uploading images');
                }
                $(".image-box1").find('p').find('i').removeClass('fa-spin fa-spinner').addClass('fa-cloud-upload');
                $('#gallery-images').val('');
            },
            error: function(xhr) {
                alert('Error uploading images: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $(".image-box1").find('p').find('i').removeClass('fa-spin fa-spinner').addClass('fa-cloud-upload');
                $('#gallery-images').val('');
            }
        });
    });

    // Remove temporary image
    $(document).on('click', '.remove', function() {
        const imageName = $(this).siblings('img').attr('data');
        const listItem = $(this).parent();

        $.ajax({
            url: "{{ route('vendor.products.gallery.remove-temp') }}",
            type: 'POST',
            data: {
                image: imageName
            },
            success: function(response) {
                if (response.success) {
                    listItem.remove();
                } else {
                    alert(response.message || 'Error removing image');
                }
            },
            error: function() {
                alert('Error removing image');
            }
        });
    });

    // Remove image from database
    $(document).on('click', '.remove_db', function() {
        if (!confirm('Are you sure you want to delete this image?')) return;

        const imageId = $(this).siblings('img').attr('data-id');
        const imageName = $(this).siblings('img').attr('data');
        const listItem = $(this).parent();
        const productId = "{{ $product->id }}";

        $.ajax({
            url: "{{ route('vendor.products.gallery.destroy', ['product' => ':productId']) }}".replace(':productId', productId),
            type: 'DELETE',
            data: {
                image_id: imageId,
                image_name: imageName
            },
            success: function(response) {
                if (response.success) {
                    listItem.remove();
                    alert('Image deleted successfully');
                } else {
                    alert(response.message || 'Error deleting image');
                }
            },
            error: function() {
                alert('Error deleting image');
            }
        });
    });

    
    // Form submission
    $('#gallery-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const productId = "{{ $product->id }}";
        $('.submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        // Get all temporary images, skipping the first <li>
        const tempImages = [];
        $('.img-list li').each(function(index) {
            if (index === 0) return; // Skip the first <li>
            const img = $(this).find('img');
            if (img.length && img.attr('data-id') === undefined) {
                tempImages.push(img.attr('data')); // Push only image name (string)
            }
        });

        if (tempImages.length === 0) {
            toastr.error('No new images to upload');
            $('.submit-btn').prop('disabled', false).html('Save Changes');
            return;
        }

        $.ajax({
            url: "{{ route('vendor.products.gallery.store', ['product' => ':productId']) }}".replace(':productId', productId),
            type: 'POST',
            data: {
                images: tempImages // Send array of image names (strings)
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Gallery updated successfully');
                    window.location.reload();
                } else {
                    alert(response.message || 'Error updating gallery');
                }
                $('.submit-btn').prop('disabled', false).html('Save Changes');
            },
            error: function(xhr) {
                alert('Error updating gallery: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $('.submit-btn').prop('disabled', false).html('Save Changes');
            }
        });
    });
});
</script>
@endsection