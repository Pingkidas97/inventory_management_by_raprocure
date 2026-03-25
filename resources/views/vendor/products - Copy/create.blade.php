@extends('vendor.layouts.app_second')
@section('title', 'Add Product - Raprocure')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>

<section class="container-fluid">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb breadcrumb-global py-2 mb-0">
      <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="{{ route('vendor.products.index') }}">Manage Products</a></li>
      <li class="breadcrumb-item active" aria-current="page">Add Product</li>
    </ol>
  </nav>

  <section class="manage-product card">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
        <h1 class="card-title font-size-18 mb-0">Add Product</h1>
        <a href="{{ route('vendor.products.index') }}" class="ra-btn ra-btn-primary d-flex align-items-center font-size-12">
          <span class="bi bi-arrow-left-square font-size-11"></span>
          <span class="font-size-11">Back</span>
        </a>
      </div>

      <form id="productForm" enctype="multipart/form-data">
        @csrf
        <div class="card-body add-product-section">
          
          {{-- Product Name --}}
          <div class="mb-4 row align-items-center">
              <label for="product_name" class="col-md-3 col-form-label">Product Name <span
                      class="text-danger">*</span></label>
              <div class="col-md-4">
                  <div class="position-relative">
                      <input type="text" class="form-control" name="product_name"
                          id="product_name"
                          autocomplete="off">
                      <input type="hidden" name="product_id" id="product_id">
                      <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $id }}">
                      <div id="product_suggestions" class="dropdown-menu w-100 shadow"
                          style="display: none; max-height: 300px; overflow-y: auto; position: absolute; z-index: 1000;">
                      </div>
                  </div>
                  <span class="error-class text-danger product-name-error" id="product-name-error"></span>
              </div>
              <div class="col-md-1"></div>
              <div class="col-md-4">
                <div class="note-text mt-3 mt-sm-0 p-2 fw-bold">
                  <b>
                      <p>Note: 1. Start typing the name of your product, options will start
                          appearing in Dropdown, and then select anyone.</p>
                      <p>2. Just first few fields - marked <span class="text-danger"
                              style="font-size: 21px;">*</span> - are mandatory.</p>
                  </b>
                </div>
              </div>
          </div>


          <!-- Product Picture -->
          <div class="row gx-3 align-items-center mb-4 pb-0 pb-sm-4">
            <div class="col-sm-3">
              <label class="col-form-label">Upload Picture</label>
            </div>
            <div class="col-sm-4">
              <div class="simple-file-upload">
                <input type="file" id="uploadFile" name="product_image" class="real-file-input" accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;">
                <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                  Select Picture
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <span class="text-danger font-size-11">(JPEG/JPG/PNG/GIF - Max 2MB)</span>
            </div>
          </div>

          <!-- Product Description -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">Product Description <span class="text-danger">*</span></label>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 d-flex align-items-center">
                    <label class="form-label mb-0">Product Description<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-9">
                    <span class="char-count prod-des-count">Characters:0/500</span>
                    <textarea class="form-control" id="product_description" name="description"></textarea>
                    <span class="text-danger error-text product-description-error"></span>
                </div>
            </div>
          </div>

          <!-- Dealer Type -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
                <label class="col-form-label">Dealer Type <span class="text-danger">*</span></label>
            </div>
            <div class="col-sm-4">
                <select name="dealer_type" class="form-select" id="product_dealer_type">
                    <option value="">Select Dealer Type</option>
                    @foreach ($dealertypes as $dealerType)
                        <option value="{{ $dealerType->id }}">{{ $dealerType->dealer_type }}</option>
                    @endforeach
                </select>
                <span class="text-danger error-text product-dealer-type-error"></span>
            </div>
        </div>


          <!-- UOM -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">UOM</label>
            </div>
            <div class="col-sm-4">
              <select name="uom" class="form-select">
                <option value="">Select</option>
                @foreach ($uoms as $uom)
                  <option value="{{ $uom->id }}">{{ $uom->uom_name }}</option>
                @endforeach 
              </select>
              <span class="text-danger error-text product-uom-error"></span>
            </div>
          </div>

          <!-- GST/Sales Tax Rate -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">GST/Sales Tax Rate <span class="text-danger">*</span></label>
            </div>
            <div class="col-sm-4">
              <select name="gst" id="product_gst" class="form-select">
                <option value="">Select</option>
                @foreach ($taxes as $tax)
                  <option value="{{ $tax->id }}">
                    {{ $tax->tax_name }} - {{ $tax->tax }}%
                  </option>
                @endforeach
              </select>
              <span class="text-danger error-text product-gst-error"></span>
            </div>
          </div>

          <!-- HSN Code -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">HSN Code <span class="text-danger">*</span></label>
            </div>
            <div class="col-sm-4">
              <input type="text" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" maxlength="8"  oninput="this.value = this.value.replace(/[^0-9]/g, '');" class="ean_code form-control"  name="hsn_code" id="product_hsn_code" class="form-control" placeholder="HSN Code">
              <span class="text-danger error-text product-hsn-code-error"></span>
            </div>
          </div>

          <!-- Aliases and Tags -->
          <div class="row mb-3">
              <div class="col-md-3 d-flex align-items-center">
                  <label class="form-label mb-0">Aliases & Tags</strong></label>
              </div>
              <div class="col-md-4">
                  <span><b>Master Aliases:</b> </span>
                 
                  <input type="text" data-role="tagsinput" class="form-control" name="tag" id="tags-input">
                  <div class="product-alias-error-msg"></div>
              </div>
          </div>

          <!-- Product Catalogue -->
          <div class="row gx-3 align-items-center mb-4 pb-4">
            <div class="col-sm-3">
              <label class="col-form-label">Product Catalogue</label>
            </div>
            <div class="col-sm-4">
              <div class="simple-file-upload">
                <input type="file" id="uploadFile" name="product_catalogue" class="real-file-input" style="display: none;">
                <div class="file-display-box form-control text-start font-size-12 text-dark" role="button">
                  Select Catalogue
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <span class="text-danger font-size-11">(PDF/Image/Document)</span>
            </div>
          </div>

        
          <!-- Dealership -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">Dealership</label>
            </div>
            <div class="col-sm-4">
              <input type="text" name="dealership" class="form-control">
            </div>
          </div>

          <!-- Dealership Attachment -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">Dealership Attachment</label>
            </div>
            <div class="col-sm-4">
              <div class="simple-file-upload">
                <input type="file" id="uploadFile" name="dealership_attachment" class="real-file-input" style="display: none;">
                <div class="file-display-box form-control text-start font-size-12 text-dark" role="button">
                  Select Dealership Attachment
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <span class="text-danger font-size-11">(PDF/Image/Document)</span>
            </div>
          </div>

      

          <!-- Brand -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">Brand</label>
            </div>
            <div class="col-sm-4">
              <input type="text" name="brand" class="form-control">
            </div>
          </div>

          <!-- Country of Origin -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-3">
              <label class="col-form-label">Country of Origin</label>
            </div>
            <div class="col-sm-4">
              <div class="d-flex">
                <label class="radio-inline"><input type="radio" name="country_origin" value="India" checked> India </label>
                <label class="radio-inline ms-4"><input type="radio" name="country_origin" value="International"> International</label>
              </div>
            </div>
          </div>

          <!-- Submit and Cancel Buttons -->
          <div class="row gx-3 align-items-center mb-4">
            <div class="col-sm-12">
              <div class="d-flex gy-2"></div>
              <button type="submit" id="submitBtn" name="submit" class="ra-btn ra-btn-primary mb-2 mb-sm-0">
                <span class="bi bi-send-plus" aria-hidden="true"></span>
                Send for Product Approval
              </button>
              <button type="button" id="cancelBtn" name="cancel" class="ra-btn ra-btn-outline-danger mb-2 mb-sm-0" onclick="window.location.href='{{ route('vendor.products.index') }}'">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </section>
</section>

<!-- New Product Request Modal -->
<div class="modal fade" id="new-product-request-modal" tabindex="-1" style="z-index: 9999;"   aria-labelledby="newProductRequestLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newProductRequestLabel">
          <i class="bi bi-pencil"></i> New Product Request
        </h5>
        <button type="button" class="btn-close cancel-new-product-request" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <h4>Please try to choose the product from the dropdown. If you still cannot find your Product, Click on OK to add the Product to RaProcure</h4>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary send-new-product-request">OK</button>
        <button type="button" class="btn btn-danger cancel-new-product-request" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>


@endsection


@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script>
$(document).ready(function () {
   
    const editorConfig = {
        toolbar: [
            "heading", "bold", "italic", "bulletedList", "numberedList",
            "blockQuote", "undo", "redo"
        ]
    };

    const MAX_CHARS = 500;

    function initEditor(selector, counterSelector, errorSelector = null, validateForm = false) {
        ClassicEditor
            .create(document.querySelector(selector), editorConfig)
            .then(editor => {
                const counter = document.querySelector(counterSelector);
                let isProcessing = false;

                const updateCount = () => {
                    if (isProcessing) return;
                    isProcessing = true;

                    const content = editor.getData();
                    const text = content.replace(/<[^>]*>/g, '');
                    const count = text.length;

                    counter.textContent = `Characters: ${count}/${MAX_CHARS}`;
                    counter.className = 'char-count';

                    if (count > MAX_CHARS) {
                        counter.classList.add('error');
                        const trimmed = text.substring(0, MAX_CHARS);
                        editor.setData(trimmed);
                    } else if (count > 450) {
                        counter.classList.add('warning');
                    }

                    isProcessing = false;
                };

                updateCount();
                editor.model.document.on('change:data', updateCount);

                if (validateForm && errorSelector) {
                    document.querySelector('#productForm').addEventListener('submit', function (e) {
                        const content = editor.getData();
                        const text = content.replace(/<[^>]*>/g, '');
                        if (text.length > MAX_CHARS) {
                            e.preventDefault();
                            document.querySelector(errorSelector).textContent = 'Description must be 500 characters or less';
                        }
                    });
                }
            })
            .catch(error => {
                console.error(`Editor initialization error for ${selector}:`, error);
            });
    }

      // Initialize both editors
    initEditor('#product_description', '.prod-des-count', '#product-description-error', true);
    


    $('#productForm').submit(function (e) {
      e.preventDefault();
      $('span.error-text').text('');

      const product_name = $('#product_name').val().trim();
      const product_description = $('#product_description').val().trim();
      const product_hsn_code = $('#product_hsn_code').val();
      const product_gst = $('#product_gst').val();
      const product_dealer_type = $('#product_dealer_type').val();
      
      let hasErrors = false;
      let errorMessage = "Please fill all the mandatory fields marked with *.";

      if (!product_name) {
          $('.product-name-error').text('Product name is required***.');
          hasErrors = true;
      } else if (product_name.length < 2) {
          $('.product-name-error').text('Product name must be at least 2 characters.');
          hasErrors = true;
      }

      if (!product_description) {
          $('.product-description-error').text('Product Description is required***.');
          hasErrors = true;
      }

      if (!product_hsn_code) {
          $('.product-hsn-code-error').text('GST/Sales Tax Rate is required***');
          hasErrors = true;
      } else if (!/^\d{2,8}$/.test(product_hsn_code)) {
          $('.product-hsn-code-error').text('HSN code must be 2 to 8 digits.');
          hasErrors = true;
      }

      if (!product_gst) {
          $('.product-gst-error').text('GST/Sales Tax Rate is required***');
          hasErrors = true;
      }

      if (!product_dealer_type) {
          $('.product-dealer-type-error').text('Dealer type is required***');
          hasErrors = true;
      }

      if (hasErrors) {
          toastr.error(errorMessage);
          return;
      }

      let formData = new FormData(this);

      // Disable submit button
      $('#submitBtn').attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

      $.ajax({
          url: "{{ route('vendor.products.store') }}",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          success: function (response) {
              $('#submitBtn').attr('disabled', false).html('<span class="bi bi-send-plus" aria-hidden="true"></span> Send for Product Approval');
              
              if (response.status == 1) {
                  toastr.success(response.message);
                  setTimeout(function () {
                      window.location.href = "{{ route('vendor.products.index') }}";
                  }, 300);
              } else {
                  if (response.alias_error_message && Array.isArray(response.alias_error_message)) {
                      let alias_error_html = '';
                      response.alias_error_message.forEach(function(alias_error) {
                          alias_error_html += '<li><span class="text-danger">*</span> ' + alias_error + '</li>';
                      });
                      $(".product-alias-error-msg").html('<ul>' + alias_error_html + '</ul>');
                  }
                  toastr.error(response.message);
              }
          },
          error: function (xhr) {
              $('#submitBtn').attr('disabled', false).html('<span class="bi bi-send-plus" aria-hidden="true"></span> Send for Product Approval');

              if (xhr.status === 422) {
                  $.each(xhr.responseJSON.errors, function (key, value) {
                      const errorField = key.replace('.', '_');
                      $(`span.error-text.${errorField}_error`).text(value[0]);
                  });
              } else {
                  toastr.error('An error occurred. Please try again.');
              }
          }
      });
  });

});

$(document).ready(function() {
    const $input = $('#product_name');
    const $hiddenInput = $('#product_id');
    const $suggestionBox = $('#product_suggestions');
    let debounceTimer;

    $input.on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(debounceTimer);
        $hiddenInput.val('');

        if (query.length < 3) {
            $suggestionBox
                .html('<div class="dropdown-item disabled"><font style="color:#6aa510;">Please enter more than 3 characters.</font></div>')
                .show();
            return;
        }

        debounceTimer = setTimeout(function() {
            $.get('{{ route("vendor.product.autocomplete") }}', { term: query }, function(data) {
                $suggestionBox.empty();

                if (data.length === 0) {
                    $suggestionBox.append('<div class="dropdown-item disabled">No products found</div>');
                    $('#new-product-request-modal').modal('show');
                    return;
                }

                $suggestionBox.append(`<div class="dropdown-item disabled text-muted">Showing result for "<strong>${query}</strong>" — ${data.length} found</div>`);

                $.each(data, function(i, item) {
                    const $option = $('<div class="dropdown-item" style="cursor: pointer;"></div>').html(item.label);
                    $option.on('click', function() {
                        $input.val(item.value);
                        $hiddenInput.val(item.id);
                        $suggestionBox.hide();
                    });
                    $suggestionBox.append($option);
                });

                $suggestionBox.show();
            });
        }, 300);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#product_name, #product_suggestions').length) {
            $suggestionBox.hide();
        }
    });

    // Handle modal OK button
    $('.send-new-product-request').click(function() {
        const inputVal = encodeURIComponent($('#product_name').val().trim());
        if (inputVal) {
            window.location.href = `{{ url('/vendor/products/create') }}?search_type=new&input_name=${inputVal}`;
        }
    });

    // Handle modal Cancel button
    $('.cancel-new-product-request').click(function() {
        $('#new-product-request-modal').modal('hide');
    });
});
</script>
@endsection
