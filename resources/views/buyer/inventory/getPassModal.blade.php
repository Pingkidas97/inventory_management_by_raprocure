
<div class="modal fade" id="getPassModal" class="getPassModal" tabindex="-1" aria-labelledby="getPassModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- বড় মডাল -->
    <div class="modal-content">
      <div class="modal-header bg-graident text-white">
        <h5 class="modal-title font-size-13" id="getPassModalLabel"><i class="bi bi-pencil"></i> Get Pass</h5>
        <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
         <form id="getPassForm" class="mb-3">

          <div class="position-relative mb-3">
              <label for="product_name_search" class="form-label font-size-13">Enter Product Name</label>
              <input type="text" class="form-control" name="product_name_search" id="product_name_search" autocomplete="off" placeholder="Enter Product Name" />
              <input type="hidden" name="inventory_id" id="inventory_id" />
              
              <div class="search_text_box shadow rounded manualPOdropdown-menu show" id="productSuggestionBox" style="position:absolute; top:100%; left:0; width:100%; z-index:1050; display:none;"></div>

              <div id="product_name_display" class="mt-2 font-size-14"></div>
          </div>

          <!-- Common Fields -->
          <div id="commonFields" class="mb-3" style="display:none;">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label for="invoice_number" class="form-label fw-bold text-nowrap">Invoice Number</label>
                    <input type="text" class="form-control text-center bg-white" id="invoice_number" name="invoice_number[]" maxlength="50" autocomplete="off">
                </div>
                <div class="col-md-3" style="display:none">
                    <label for="bill_date" class="form-label fw-bold text-nowrap">Bill Date</label>
                    <input type="text" class="form-control dateTimePickerStart text-center bg-white bill-date" id="bill_date" name="bill_date[]" maxlength="50" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label for="transporter_name" class="form-label fw-bold text-nowrap">Transporter Name</label>
                    <input type="text" class="form-control text-center bg-white" id="transporter_name" name="transporter_name[]" maxlength="255" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label for="vehicle_lr_number" class="form-label fw-bold text-nowrap">Vehicle No / LR No</label>
                    <input type="text" class="form-control text-center bg-white vehicle_lr_number" id="vehicle_lr_number" name="vehicle_lr_number[]" maxlength="20" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label for="gross_weight" class="form-label fw-bold text-nowrap">Gross Wt (kgs)</label>
                    <input type="text" class="form-control text-center bg-white smt_numeric_only" id="gross_weight" name="gross_weight[]" maxlength="20" autocomplete="off">
                </div>
                <div class="col-md-3" style="display:none">
                    <label for="freight_charges" class="form-label fw-bold text-nowrap">Freight / Other Charges (₹)</label>
                    <input type="text" class="form-control text-center bg-white smt_numeric_only" id="freight_charges" name="freight_charges[]" maxlength="20" autocomplete="off">
                </div>
                <div class="col-md-3" style="display:none">
                    <label for="approved_by" class="form-label fw-bold text-nowrap">Approved By</label>
                    <input type="text" class="form-control text-center bg-white" id="approved_by" name="approved_by[]" maxlength="255" autocomplete="off">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold text-nowrap">Vendor Name</label>
                    <select id="vendorDropdown" class="form-control">
                        <option value="">Select Vendor</option>
                    </select>
                </div>
            </div>
          </div>

          <!-- Inventory Table -->
          <div class="table-responsive" style="max-height: 50vh; overflow-y:auto;">
            <table class="product-listing-table w-100" id="inventoryListTable" style="display:none;">
              <thead class="table-light">
                <tr>                  
                  <th class="text-center">Order Number</th>
                  <th class="text-center">RFQ No.</th>
                  <th class="text-center">Order Quantity</th>
                  <th class="text-center">Vendor Name</th>
                  <!-- <th class="text-center text-wrap">Gate Entered</th> -->
                  <th class="text-center">Gate Entry Qty</th>
                  <th class="text-center">Gate Entery Date</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <div id="getPassResult" class="mt-3"></div>
          
          <!-- Save Get Pass Button -->
          <div class="d-flex justify-content-center mt-3" id="saveGetPassContainer" style="display:none!important;">
            <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11" id="saveGetPassBtn">Save Gate Pass</button>
          </div>
        </form>
       
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function () {

  // Modal open cleanup
  $('#getPassModal').on('shown.bs.modal', function () {
      $('#product_name_search').val('');
      $('#product_name_display').empty();
      $('#getPassResult').html('');
      $('#commonFields').hide();
      $('#inventoryListTable tbody').empty();
      $('#inventoryListTable').hide();
      $('#saveGetPassContainer').hide();      
      $('#saveGetPassContainer')[0].style.setProperty('display', 'none', 'important');
      $('#inventory_id').val('');
      $('#invoice_number').val('');
      $('#transporter_name').val('');
      $('#vehicle_lr_number').val('');
      $('#gross_weight').val('');
      $('#productSuggestionBox').hide('');
      
  });

  // Debounce timer for search input
  let delayTimer;

  // Product Search keyup event
  $(document).on('keyup', '#product_name_search', function (e) {
      clearTimeout(delayTimer);
      if (e.which === 13) { // 13 = Enter key
          e.preventDefault(); // Default action (form submit) block করবে
          return false; // event propagation বন্ধ
      }
      let inventory_id= $('#inventory_id').val();
      if(inventory_id){          
        $('#product_name_display').empty();
        $('#getPassResult').html('');
        $('#commonFields').hide();
        $('#inventoryListTable tbody').empty();
        $('#inventoryListTable').hide();
        $('#saveGetPassContainer')[0].style.setProperty('display', 'none', 'important');
        $('#inventory_id').val('');
        $('#productSuggestionBox').hide('');        
        $('#invoice_number').val('');
        $('#transporter_name').val('');
        $('#vehicle_lr_number').val('');
        $('#gross_weight').val('');
      }
      let productName = $(this).val().trim();
      let branchId = $('#branch_id').val() ? $('#branch_id').val().trim() : '';

      if (productName.length < 3) {
          $('#productSuggestionBox').html('<div class="p-2 text-muted font-size-14"><strong>Minimum 3 characters required</strong></div>').show();
          return;
      }

      delayTimer = setTimeout(function () {
          $.ajax({
              url: "{{ route('buyer.inventory.showProductNameList') }}",
              type: "GET",
              data: { productName: productName, branch_id: branchId },
              success: function (res) {
                  let html = '';

                  if (res.status && res.data.length > 0) {
                      res.data.forEach(function (item) {
                          let name = item.product?.product_name || item.buyer_product_name || '';
                          let spec = item.specification || '';
                          let size = item.size || '';
                          let extra = [spec, size].filter(v => v).join(' - ');

                          html += `<a href="javascript:void(0)" class="manualPOdropdown-item manualPOdropdown-item-border"
                              data-id="${item.id}"
                              data-name="${name}"
                              data-spec="${spec}"
                              data-size="${size}">
                              <strong>${name}</strong>${extra ? ` - <small>${extra}</small>` : ''}
                          </a>`;
                      });
                  } else {
                      html = '<div class="p-2 text-danger font-size-14"><strong>No product found</strong></div>';
                  }
                  $('#productSuggestionBox').html(html).show();
              },
              error: function () {
                  $('#productSuggestionBox').html('<div class="p-2 text-danger font-size-13">Something went wrong</div>').show();
              }
          });
      }, 300);
  });

  // Suggestion click - fill input & trigger fetch
  $(document).on('click', '.manualPOdropdown-item', function (e) {
      e.preventDefault();
      let id = $(this).data('id');
      let name = $(this).data('name');
      let spec = $(this).data('spec');
      let size = $(this).data('size');

      $('#product_name_search').val(name);
      $('#inventory_id').val(id);
      $('#product_name_display').html(`<strong>Specification:</strong> ${spec}<br><strong>Size:</strong> ${size}`);
      $('#productSuggestionBox').hide();

      fetchPendingOrdersForInventory(id);
  });

//   function fetchPendingOrdersForInventory(inventory_id) {
//       const branchId = $('#branch_id').val() ? $('#branch_id').val().trim() : '';

//       $('#commonFields').hide();
//       $('#inventoryListTable tbody').empty();
//       $('#inventoryListTable').hide();
//       $('#saveGetPassContainer').hide();
//       $('#getPassResult').html('<div class="text-muted font-size-13">Searching...</div>');

//       $.ajax({
//           url: "{{ route('buyer.inventory.checkPoPending') }}",
//           type: "GET",
//           data: { inventory_id: inventory_id, branch_id: branchId },
//           success: function (response) {
//               if (response.status && response.data && response.data.length > 0) {
//                   let firstItem = response.data[0];
//                   let firstOrder = firstItem.pending_orders && firstItem.pending_orders.length > 0 ? firstItem.pending_orders[0] : null;

//                     if (firstOrder) {
//                         $('#commonFields').show();
//                         $('.bill-date').datetimepicker({
//                             format: 'd/m/Y',
//                             timepicker: false,
//                             maxDate: 0
//                         });
//                     }

//                   renderInventoryTable(response.data);
//               } else if (response.status === false && response.message) {
//                   $('#commonFields').hide();
//                   $('#inventoryListTable').hide();
//                   $('#saveGetPassContainer').hide();
//                   $('#getPassResult').html(`<div class="alert alert-warning font-size-13 text-center">${response.message}</div>`);
//               } else {
//                   $('#commonFields').hide();
//                   $('#inventoryListTable').hide();
//                   $('#saveGetPassContainer').hide();
//                   $('#getPassResult').html('<div class="alert alert-warning font-size-13 text-center">No data found</div>');
//               }
//           },
//           error: function () {
//               $('#commonFields').hide();
//               $('#inventoryListTable').hide();
//               $('#saveGetPassContainer').hide();
//               $('#getPassResult').html('<div class="alert alert-danger font-size-13 text-center">Something went wrong</div>');
//           }
//       });
//   }
function fetchPendingOrdersForInventory(inventory_id) {
    const branchId = $('#branch_id').val() ? $('#branch_id').val().trim() : '';

    $('#commonFields').hide();
    $('#inventoryListTable tbody').empty();
    $('#inventoryListTable').hide();
    $('#saveGetPassContainer').hide();
    $('#vendorDropdown').empty().append('<option value="">Select Vendor</option>');
    $('#getPassResult').html('<div class="text-muted font-size-13">Searching...</div>');

    $.ajax({
        url: "{{ route('buyer.inventory.checkPoPending') }}",
        type: "GET",
        data: { inventory_id: inventory_id, branch_id: branchId },
        success: function (response) {

            if (response.status && response.data && response.data.length > 0) {
                $('#getPassResult').html('<div class="text-muted font-size-13">Please select vendor</div>');
                let firstItem = response.data[0];
                let firstOrder = firstItem.pending_orders && firstItem.pending_orders.length > 0 
                    ? firstItem.pending_orders[0] 
                    : null;

                if (firstOrder) {
                    $('#commonFields').show();

                    $('.bill-date').datetimepicker({
                        format: 'd/m/Y',
                        timepicker: false,
                        maxDate: 0
                    });
                }

                // All order collect
                let allOrders = [];
                response.data.forEach(item => {
                    if (item.pending_orders && item.pending_orders.length > 0) {
                        allOrders = allOrders.concat(item.pending_orders);
                    }
                });

                //  unique vendor list
                let uniqueVendors = [...new Set(allOrders.map(order => order.vendor_name))];

                //  dropdown fill
                uniqueVendors.forEach(vendor => {
                    $('#vendorDropdown').append(`<option value="${vendor}">${vendor}</option>`);
                });

                //  global store
                window.allPendingOrders = allOrders;

                //  table initially hide থাকবে
                $('#inventoryListTable').hide();
                $('#saveGetPassContainer').hide();

                //  vendor change event
                $('#vendorDropdown').off('change').on('change', function () {

                    let selectedVendor = $(this).val();

                    if (!selectedVendor) {
                        $('#inventoryListTable').hide();
                        $('#saveGetPassContainer').hide();
                        return;
                    }

                    let filteredOrders = window.allPendingOrders.filter(order => 
                        order.vendor_name === selectedVendor
                    );

                    if (filteredOrders.length > 0) {
                        $('#inventoryListTable').show();
                        $('#saveGetPassContainer').show();

                        renderInventoryTable([{ pending_orders: filteredOrders }]);
                    } else {
                        $('#inventoryListTable').hide();
                        $('#saveGetPassContainer').hide();
                    }
                });

            } 
            else if (response.status === false && response.message) {
                $('#commonFields').hide();
                $('#inventoryListTable').hide();
                $('#saveGetPassContainer').hide();
                $('#getPassResult').html(`<div class="alert alert-warning font-size-13 text-center">${response.message}</div>`);
            } 
            else {
                $('#commonFields').hide();
                $('#inventoryListTable').hide();
                $('#saveGetPassContainer').hide();
                $('#getPassResult').html('<div class="alert alert-warning font-size-13 text-center">No data found</div>');
            }
        },
        error: function () {
            $('#commonFields').hide();
            $('#inventoryListTable').hide();
            $('#saveGetPassContainer').hide();
            $('#getPassResult').html('<div class="alert alert-danger font-size-13 text-center">Something went wrong</div>');
        }
    });
}

  function renderInventoryTable(data) {
      console.log
      let tbodyHtml = '';
      data.forEach(function (item) {
          if (item.pending_orders && item.pending_orders.length > 0) {
              item.pending_orders.forEach(function (order) {
                  let maxGrnQty = parseFloat(((parseFloat(order.order_quantity || 0) * 1.02) - parseFloat(order.grn_entered || 0)).toFixed(3));
                  const url = order.baseManualPoUrl.replace('__ID__', order.id);
                  if(maxGrnQty > 0){
                  tbodyHtml += `
                  <tr>                    
                    <td class="text-center">${order.order_number || ''}</td>
                    <td class="text-center">${
                      order.order_type !== 'manual_order'
                        ? `${order.rfq_number || ''}<input type="hidden" name="grn_type[]" value="1">`
                        : `- <input type="hidden" name="grn_type[]" value="4">`
                    }</td>
                    <td class="text-center">${order.show_order_quantity || ''}</td>
                    <td class="text-center">${order.vendor_name || ''}</td>
                    <td class="text-center">
                       <input type="text" class="grn_quantity_input form-control bg-white text-center w-100" name="grn_qty[]" data-max="${maxGrnQty}" maxlength="20" min="0" step="any" style="width: 200px;">
                        <input type="hidden" name="rate[]" class="rate" value="${order.rate || ''}">
                        <input type="hidden" name="inventory_id[]" class="inventory_id" value="${order.inventory_id || ''}">
                        <input type="hidden" name="order_id[]" class="order_id" value="${order.id || ''}">
                        <input type="hidden" name="gst_percentage[]" class="gst_percentage" value="${order.gst_percentage}">
                        <input type="hidden" name="buyer_rate[]" class="buyer_rate" value="${order.grn_buyer_rate || ''}">
                        <input type="hidden" name="po_number[]"value="${order.order_number || ''}">
                        <input type="hidden" name="order_qty[]" value="${order.order_quantity || ''}">
                        <input type="hidden" name="vendor_name[]" value="${order.vendor_name || ''}">
                        <input type="hidden" name="grn_entered[]" value="${order.grn_entered || '0'}"></td>
                    </td>
                    <td class="text-center d-flex justify-content-center align-items-center">
                      <input type="text" class="form-control dateTimePickerStart bg-white text-center w-80 grn-date" name="grn_date[]" maxlength="50">
                      <input type="hidden" class="grn-min-date" name="grn_min_date[]" value="${order.order_date || ''}">
                    </td>
                    
                  </tr>`;
                  }
              });
          }
      });

      if (tbodyHtml) {
          $('#inventoryListTable tbody').html(tbodyHtml);
          $('#inventoryListTable').show();
          $('#saveGetPassContainer').show();
          $('#getPassResult').empty();

          // Init GRN date pickers
          $('.grn-date').each(function () {
              const row = $(this).closest('tr');
              const minDateStr = row.find('.grn-min-date').val();

              let minDate = false;

              if (minDateStr) {
                  if (minDateStr.includes('/')) {
                      const p = minDateStr.split('/');
                      minDate = new Date(p[2], p[1] - 1, p[0]);
                  } else if (minDateStr.includes('-')) {
                      const p = minDateStr.split('-');
                      minDate = new Date(p[0], p[1] - 1, p[2]);
                  }
              }
              $(this).datetimepicker({
                  format: 'd/m/Y',
                  timepicker: false,
                  minDate: minDate,
                  maxDate: 0,
                  value: new Date()
              });
          });

      } else {
          $('#inventoryListTable').hide();
          $('#saveGetPassContainer').hide();
          $('#getPassResult').html('<div class="alert alert-warning font-size-13 text-center">No pending orders found</div>');
      }
  }

  // Numeric input with max validation & 3 decimals + GST calc
  $('#getPassModal').on('input paste blur', '.grn_quantity_input', function () {
      let $input = $(this);
      let val = $input.val();

      val = val.replace(/[^0-9.]/g, '');
      val = val.replace(/(\..*)\./g, '$1');

      if (val.includes('.')) {
          let parts = val.split('.');
          val = parts[0] + '.' + parts[1].slice(0, 3);
      }

      let maxQty = parseFloat($input.data('max'));
      let enteredQty = parseFloat(val);

      if (!isNaN(maxQty) && !isNaN(enteredQty) && enteredQty > maxQty) {
          $input.val('');
          toastr.error(`GRN Quantity cannot exceed available quantity (${maxQty}).`);
      } else {
          $input.val(val);
      }

      let $row = $input.closest('tr');
      calculateRowGST($row);
  });

  // Rate in local currency change triggers GST calculation
  $('#getPassModal').on('input', '.rate_in_local_currency', function () {
      let $row = $(this).closest('tr');
      calculateRowGST($row);
  });

  function calculateRowGST($row) {
      let enteredQty = parseFloat($row.find('.grn_quantity_input').val()) || 0;
      let rate = parseFloat($row.find('.rate').val()) || 0;
      let buyer_rate = parseFloat($row.find('.buyer_rate').val()) || 0;
      let rate_in_local_currency = parseFloat($row.find('.rate_in_local_currency').val()) || 0;
      let gstPercentage = parseFloat($row.find('.gst_percentage').val()) || 0;

      if (enteredQty === '') {
          $row.find('.gst').val('');
          return;
      }

      rate = rate_in_local_currency !== 0
          ? rate_in_local_currency
          : buyer_rate !== 0
              ? buyer_rate
              : rate;

      let gstAmount = (enteredQty * rate * gstPercentage) / 100;
      $row.find('.gst').val(gstAmount.toFixed(2));
  }

  // Submit Get Pass Form
  let isSaveGetPassSubmitting = false;
  // $('#getPassForm').off('submit').on('submit', function (e) {
  //     e.preventDefault();
  //     if (isSaveGetPassSubmitting) {
  //         return;
  //     }
  //     let inventory_id=$('#inventory_id').val();
  //     if(!inventory_id) {
  //       toastr.error('Please select inventory product');
  //       return; 
  //     }
  //     isSaveGetPassSubmitting = true;
  //     $('#saveGetPassBtn').attr('disabled', true);

  //     $.ajax({
  //         type: "POST",
  //         url: "{{ route('buyer.getpass.store') }}",
  //         data: $('#getPassForm').serialize(),
  //         headers: {
  //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  //         },
  //         beforeSend: function () {
  //             $('#saveGetPassBtn').attr('disabled', true);
  //         },
  //         success: function (response) {
            
  //             if (response.status) {
  //                 toastr.success(response.message);
  //                 // let downloadUrl = `/getpass-download/${response.getPassId}`;
  //                 // window.location.href = downloadUrl;

  //                 $("#getPassModal").modal('hide');
  //             } else {
  //                 toastr.error(response.message || 'Something went wrong. Please try again.');
  //             }
  //             isSaveGetPassSubmitting = false;
  //             $('#saveGetPassBtn').removeAttr('disabled');
  //         },
  //         error: function (xhr) {
  //             isSaveGetPassSubmitting = false;
  //             $('#saveGetPassBtn').removeAttr('disabled');

  //             if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
  //                 let errors = xhr.responseJSON.errors;
  //                 Object.keys(errors).forEach(function (key) {
  //                     toastr.error(errors[key][0]);
  //                 });
  //             } else if (xhr.responseJSON && xhr.responseJSON.message) {
  //                 toastr.error(xhr.responseJSON.message);
  //             } else {
  //                 toastr.error('Something went wrong. Please try again.');
  //             }
  //         }
  //     });
  // });
$('#getPassForm').off('submit').on('submit', function (e) {
    e.preventDefault();

    if (isSaveGetPassSubmitting) return;

    let inventory_id = $('#inventory_id').val();
    if (!inventory_id) {
        toastr.error('Please select inventory product');
        return;
    }
    let qtyError = false;

    
    
    isSaveGetPassSubmitting = true;

    let $button = $('#saveGetPassBtn');
    let originalHtml = $button.html();

    $button.prop('disabled', true);
    $button.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    $.ajax({
        type: "POST",
        url: "{{ route('buyer.getpass.store') }}",
        data: $('#getPassForm').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function (response) {

            if (response.status) {

                let downloadUrl = "{{ route('buyer.getpass.download', '') }}/" + response.getPassId;

                // ✅ success message
                toastr.success(response.message);

                // ✅ modal close immediately
                $('#getPassModal').modal('hide');

                // ✅ button restore immediately
                $button.html(originalHtml);
                $button.prop('disabled', false);
                isSaveGetPassSubmitting = false;

                // ✅ PDF download trigger (NO AJAX)
                setTimeout(() => {
                    window.open(downloadUrl, '_blank'); 
                }, 500); // slight delay for smooth UX

            } else {
                toastr.error(response.message || 'Something went wrong.');

                $button.html(originalHtml);
                $button.prop('disabled', false);
                isSaveGetPassSubmitting = false;
            }
        },

        error: function () {
            toastr.error('Something went wrong.');

            $button.html(originalHtml);
            $button.prop('disabled', false);
            isSaveGetPassSubmitting = false;
        }
    });
});
});
</script>