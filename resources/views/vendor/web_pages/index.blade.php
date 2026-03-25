@extends('vendor.layouts.app_second',['title'=>'Change Password','sub_title'=>''])
@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>

@endsection
 
@section('content')
 <section class="container-fluid">
    <!-- Start Breadcrumb Here -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="{{route('vendor.dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mini Web Page Management</li>
        </ol>
    </nav>
    <!-- Start Content Here -->
    <section>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">Mini Web Page Management</h1>
            </div>
            <div class="card-body add-product-section">
                <form class="updateAboutUs" method="POST" action="{{route('vendor.web-pages.store')}}" id="updateAboutUs" enctype="multipart/form-data">
                    @csrf
                    <div class="web-page-form">

                        <div class="mb-3 row align-items-center">
                            <div class="col-sm-2 col-md-2 ">
                                <label class="col-form-label" for="about_us">About Us </label>
                            </div>
                            <div class="col-sm-10 col-md-10">
                                <input type="hidden" name="store_id" value="{{getParentUserId()}}" id="store_id">
                                <span class="text-secondary font-size-13 about-char-count">Characters: 0/10000</span>
                                <textarea name="about_us" id="about_us" rows="5" class="form-control" maxlength="10000">{!!(!empty($data)?$data->about_us:'')!!}</textarea>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <div class="col-sm-2 col-md-2 ">
                                <label class="col-form-label" for="catalogue">Catalogue</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="simple-file-upload button-browse">
                                    <input type="file" onchange="validatePDF(this)" name="catalogue" id="catalogue" class="real-file-input" style="display: none;">
                                    <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                        Upload Catalogue Document
                                    </div>
                                </div>
                                <span class="help-block error-message"></span>
                            </div>
                            <div class="col-sm-4">
                                @if(!empty($data->catalogue))
                                <a href="{{asset('uploads/web-page/'.$data->catalogue)}}" download>Download</a>
                                @endif
                                <span class="text-danger">
                                    (PDF/Image/Document)
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <div class="col-sm-2 col-md-2 ">
                                <label class="col-form-label" for="certification">Other Credentials</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="simple-file-upload button-browse">
                                    <input type="file" onchange="validatePDF(this)" name="certification" id="certification" class="real-file-input" style="display: none;">
                                    <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                        Upload Other Credentials Document
                                    </div>
                                </div>
                                <span class="help-block error-message"></span>
                            </div>
                            <div class="col-sm-4">
                                @if(!empty($data->other_credentials))
                                    <a href="{{asset('uploads/web-page/'.$data->other_credentials)}}" download>Download</a>
                                @endif
                                <span class="text-danger">
                                    (PDF/Image/Document)
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <div class="col-sm-2 col-md-2 ">
                                <label class="col-form-label" for="banner1">Home Banner Left</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="simple-file-upload button-browse">
                                    <input type="file" onchange="validateImage(this, 1890, 450)" name="banner1" id="banner1" class="real-file-input"  style="display: none;">
                                    <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                        Upload Home Banner Left
                                    </div>
                                </div>
                                <span class="help-block error-message"></span>
                            </div>
                            <div class="col-sm-4">
                                @if(!empty($data->left_banner))
                                    <a href="{{asset('uploads/web-page/'.$data->left_banner)}}" download>Download</a>
                                @endif
                                <span class="text-danger">
                                    (JPEG/JPG/PNG/GIF )
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <div class="col-sm-2 col-md-2 ">
                                <label class="col-form-label" for="banner2">Home Banner Right</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="simple-file-upload button-browse">
                                    <input type="file" onchange="validateImage(this, 1890, 450)" name="banner2" id="banner2" class="real-file-input" style="display: none;">
                                    <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                        Upload Home Banner Right
                                    </div>
                                </div>
                                <span class="help-block error-message"></span>
                            </div>

                            <div class="col-sm-4">
                                @if(!empty($data->right_banner))
                                    <a href="{{asset('uploads/web-page/'.$data->right_banner)}}" download>Download</a>
                                @endif
                                <span class="text-danger">
                                    (JPEG/JPG/PNG/GIF )
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-6 text-center">
                                <div class="d-flex gap-3 justify-content-md-end align-items-center">
                                    <button type="submit" class="ra-btn ra-btn-primary font-size-12">
                                        <span class="font-size-11">Submit</span>
                                    </button>
                                    <button type="button" class="ra-btn ra-btn-outline-danger font-size-12">
                                        <span class="font-size-11">Cancel</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </section>
</section>
 
@endsection
@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/12.0.0/classic/ckeditor.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    function validateFileSize(event) {
        var fileInput = event.target;
        var maxSize = 2 * 1024 * 1024; // 2MB in bytes
        var files = fileInput.files; 

        if (files.length > 0) {
            for (var i = 0; i < files.length; i++) {
            var fileSize = files[i].size;
            if (fileSize > maxSize) {
                alert('File "' + files[i].name + '" size exceeds 2MB limit.');
                // Clear the value of the file input element
                fileInput.value = '';

                let file_name_section = $(fileInput).closest(".custom-file").find(".product-file-name-div");

                if (file_name_section.length > 0) {
                file_name_section.find('.product-file-name').html('').addClass('d-none');
                file_name_section.find('.remove-product-file').addClass('d-none');
                } else {
                // Find the rfq-file-name span elements
                const fileNameSpans = document.querySelectorAll('.rfq-file-name-div, .delete-rfq-file, .product-file-name-div');
                // Add the class 'd-none' to each fileNameSpan element
                fileNameSpans.forEach(function(element) {
                    element.classList.add('d-none');
                });
                }
            } else {
                // File is valid, show file name and remove 'd-none' class
                let file_name_section = $(fileInput).closest(".custom-file").find(".product-file-name-div");
                if (file_name_section.length > 0) {
                file_name_section.find('.product-file-name').html(files[i].name).removeClass('d-none');
                file_name_section.find('.remove-product-file').removeClass('d-none');
                }

                const fileNameSpans = document.querySelectorAll('.rfq-file-name-div, .delete-rfq-file, .product-file-name-div');
                fileNameSpans.forEach(function(element) {
                element.classList.remove('d-none');
                });
            }
            }
        }
    }


    document.addEventListener("DOMContentLoaded", function () {
        const CKEDITOR_MAX_CHARACTERS = 10000; // Set character limit

        // Initialize CKEditor
        ClassicEditor.create(document.querySelector('#about_us'), {
            toolbar: {
                items: [
                    'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'
                ]
            }
        }).then(editor => {
            window.about_us_editor = editor; // Store editor instance
            let isProcessing = false;
            //  Select the existing <span class="about-char-count">
            let charCountDisplay = document.querySelector(".about-char-count");
            if (!charCountDisplay) {
                console.error("Error: Character count <span> not found!");
                return;
            }
            
            
            //  Function to update character count
            function updateCharCount() {
                const editorData = editor.getData();
                document.querySelector('#about_us').value = editorData;
                const tempElement = document.createElement("div");
                tempElement.innerHTML = editorData;
                const plainText = tempElement.innerText || tempElement.textContent;
                const charCount = plainText.length;

                //  Update counter text inside <span class="about-char-count">
                charCountDisplay.innerText = `Characters: ${charCount}/${CKEDITOR_MAX_CHARACTERS}`;

                //  Apply red color if exceeded
                if (charCount > CKEDITOR_MAX_CHARACTERS) {
                    charCountDisplay.style.color = "red";
                    let trimmedText = plainText.slice(0, CKEDITOR_MAX_CHARACTERS);
                    tempElement.innerText = trimmedText;
                    editor.setData(tempElement.innerHTML);
                } else {
                    charCountDisplay.style.color = "gray";
                }
            }
            //  Update character count **on page load** with existing content
            updateCharCount();
            //  Update character count on text change
            editor.model.document.on("change:data", () => {
                if (isProcessing) return;
                isProcessing = true;
                updateCharCount();
                isProcessing = false;
            });

            //  Fix Editor Overlapping Issue
            let editorContainer = editor.ui.view.editable.element.parentElement;
            editorContainer.style.width = "100%";
            editorContainer.style.maxWidth = "100%";
            editorContainer.style.boxSizing = "border-box";
        });

        //  Toastr Notification for Flash Messages
        var msg = "";
        if (msg !== '') {
            toastr.success(msg);
        }
    });
</script>
<script type="text/javascript">
    let about_us_editor;
    function validatePDF(e) {
        var avatarok = 1;
        var avatar = $(e).val();
        var extension = avatar.split('.').pop().toUpperCase();
        if (extension != "DOCX" && extension != "DOC" && extension != "JPEG" && extension != "JPG" && extension != "PNG" && extension != "GIF" && extension != "PDF") {
            $(e).val('');
            $(e).attr('src', '');
            appendFileError(e, "Invalid file extension.");
        } else {
            appendFileError(e);
        }
    }


    function validateImage(obj, requestWidth, requestHeight) {
        let avatarok = 1;
        let avatar = $(obj).val();
        let extension = avatar.split('.').pop().toUpperCase();
        var file, img;
        var file = obj.files[0];
        var img = new Image();
        img.src = window.URL.createObjectURL(file);
        // console.log("img", file);
        if (extension != "JPEG" && extension != "JPG" && extension != "PNG" && extension != "GIF") {
            // avatarok = 0;
            $(obj).val('');
            $(obj).attr('src', '');
            appendFileError(obj, "Invalid file extension.");
        }
        if (file.size > 1024 * 1024) {
            // avatarok = 0;
            $(obj).val('');
            $(obj).attr('src', '');
            appendFileError(obj, "Image size should be less than 1 MB.");
        }
        img.onload = function () {
            var width = img.naturalWidth;
            var height = img.naturalHeight;
            window.URL.revokeObjectURL(img.src);
           
            var avatarok = 1;
            appendFileError(obj, '');
            if (avatarok == 0) {
                $(obj).val('');
                $(obj).attr('src', '');
                return false;
            }
           
        };

    }

    function appendFileError(e, msg = '') {
        console.log(e);
        $(e).parents().parents().find('.error-message').text('');
        if (msg) {
            $(e).parents().parents().find('.error-message').text(msg);
        }
    }
   
    //for select file: end
    $('#updateAboutUs').submit(function (e) {
        e.preventDefault();
         
        let form = $(this);
        let url = form.attr('action');
        let formData = new FormData(this);
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                //$('#loader').show();
            },
            success: function (data) {
                //$('#loader').hide();
                if (data.success) {
                    location.reload();
                }
            },
            error: function (data) {
                //$('#loader').hide();
            }
        });
    });
</script>
@endsection