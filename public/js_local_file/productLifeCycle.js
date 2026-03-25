function product_life_cycle_modal() {
    let checkedItems = document.querySelectorAll('.inventory_chkd:checked');

    if (checkedItems.length === 0) {
        toastr.error("Please select at least one inventory!");
        return;
    }

    let ids = Array.from(checkedItems).map(item => item.value);

    $("#inventory_details_body").html(
        "<tr><td class='text-center'>Loading...</td></tr>"
    );

    $.ajax({
        url: productLifeCycleUrl,
        type: "POST",
        data: {
            inventory_ids: ids,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (!response.status) {
                $("#inventory_details_body").html(
                    `<tr><td class="text-center">${response.message}</td></tr>`
                );
                return;
            }

            function createSection(title, contentHtml, secId) {
                return `
                    <div class="mb-2">
                        <div class="section-header d-flex justify-content-between align-items-center bg-secondary text-white px-3 py-2" 
                             style="cursor:pointer;" 
                             data-target="#section-content-${secId}">
                            <span class="fw-semibold">${title}</span>
                            <span class="arrow-icon">▲</span>
                        </div>
                        <div id="section-content-${secId}" class="section-content" style="display:block;">
                            <div class="p-3 border bg-light">${contentHtml}</div>
                        </div>
                    </div>
                `;
            }

            let rows = '';

            response.data.forEach(function(item, index) {
                let productName = item.product_name ?? "";
                let extra = [item.specification, item.size].filter(Boolean).join(' - ');

                // ===== Order Details =====
                let ordersHtml = '';
                if (item.orders && item.orders.length > 0) {
                    item.orders.forEach(function(order) {
                        ordersHtml += `
                            <tr>
                                <td>${order.order_no}</td>
                                <td>${order.rfq_no ?? '-'}</td>
                                <td>${order.order_date ?? ''}</td>
                                <td>${order.order_qty}</td>
                                <td>${order.rate}</td>
                                <td>${order.vendor_name}</td>
                                <td>${order.order_status}</td>
                                <td>
                                    <a href="${order.basePoUrl}" target="_blank" class="ra-btn ra-btn-primary font-size-11 w-100 justify-content-center">
                                        <i class="fa fa-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    ordersHtml = `<tr><td colspan="7" class="text-center">No Orders Found</td></tr>`;
                }

                let orderTableHtml = `
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order No</th>
                                <th>RFQ No</th>
                                <th>Date</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Vendor</th>
                                <th>Status</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>${ordersHtml}</tbody>
                    </table>
                `;

                // ===== Indent Details =====
                let indentHtml = '';
                if (item.indent_details && item.indent_details.length > 0) {
                    item.indent_details.forEach(indent => {
                        indentHtml += `
                            <tr>
                                <td>${indent.indent_number}</td>
                                <td>${indent.indent_qty}</td>
                                <td>${indent.status}</td>
                                <td>${indent.added_date}</td>
                            </tr>
                        `;
                    });
                    indentHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Indent No</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>${indentHtml}</tbody>
                        </table>
                    `;
                } else {
                    indentHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Indent No</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center">No Indent Found</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                }

                // ===== GRN Details =====
                let grnHtml = '';
                if (item.grn_details && item.grn_details.length > 0) {
                    item.grn_details.forEach(grn => {
                        grnHtml += `
                            <tr>
                                <td>${grn.grn_no}</td>
                                <td>${grn.grn_reference}</td>
                                <td>${grn.grn_qty}</td>
                                <td>${grn.rate}</td>
                                <td>${grn.added_date}</td>
                            </tr>
                        `;
                    });
                    grnHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Grn No</th>
                                    <th>Grn Reference</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>${grnHtml}</tbody>
                        </table>
                    `;
                } else {
                    grnHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Grn No</th>
                                    <th>Grn Reference</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">No GRN Found</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                }
                // ===== Issue Details =====
                let issueHtml = '';
                if (item.issue_details && item.issue_details.length > 0) {
                    item.issue_details.forEach(issue => {
                        issueHtml += `
                            <tr>
                                <td>${issue.issued_no}</td>
                                <td>${issue.reference}</td>
                                <td>${issue.qty}</td>
                                <td>${issue.rate}</td>
                                <td>${issue.added_date}</td>
                            </tr>
                        `;
                    });
                    issueHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Issue No</th>
                                    <th>Issue Reference</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>${issueHtml}</tbody>
                        </table>
                    `;
                } else {
                    issueHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Issue No</th>
                                    <th>Issue Reference</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">No Issue Found</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                }
                // ===== Consume Details =====
                let consumeHtml = '';
                if (item.consume_details && item.consume_details.length > 0) {
                    item.consume_details.forEach(consume => {
                        consumeHtml += `
                            <tr>
                                <td>${consume.issued_no}</td>
                                <td>${consume.reference}</td>
                                <td>${consume.issue_qty}</td>
                                <td>${consume.consume_no}</td>
                                <td>${consume.qty}</td>
                                <td>${consume.rate}</td>
                                <td>${consume.added_date}</td>
                            </tr>
                        `;
                    });
                    consumeHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Issue No</th>
                                    <th>Issue Reference</th>
                                    <th>Issue Qty</th>
                                    <th>Consume No</th>
                                    <th>Consume Qty</th>
                                    <th>Rate</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>${consumeHtml}</tbody>
                        </table>
                    `;
                } else {
                    consumeHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Issue No</th>
                                    <th>Issue Reference</th>
                                    <th>Issue Qty</th>
                                    <th>Consume No</th>
                                    <th>Consume Qty</th>
                                    <th>Rate</th>
                                    <th>Added Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center">No Consume Found</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                }

               
                

                // Combine all inner sections
                let allSectionsHtml = '';
                allSectionsHtml += createSection('Order Details', orderTableHtml, `${index}_1`);
                allSectionsHtml += createSection('Indent Details', indentHtml, `${index}_2`);
                allSectionsHtml += createSection('GRN Details', grnHtml, `${index}_3`);
                allSectionsHtml += createSection('Issue Details', issueHtml, `${index}_4`);
                allSectionsHtml += createSection('Consume Details', consumeHtml, `${index}_5`);

                rows += `
                    <tr>
                        <th class="p-0 border-0">
                            <div class="product-header p-2 mb-3 d-flex justify-content-between align-items-center" 
                                 style="background: linear-gradient(to right, #0d4c7d, #2e86c1); color:white; cursor:pointer;" 
                                 data-target="#product-sections-${index}">
                                <strong>${productName}${extra ? ' - ' + extra : ''}</strong>
                                <span class="arrow-icon">▲</span>
                            </div>

                            <div id="product-sections-${index}" class="product-sections" style="display:block;">
                                ${allSectionsHtml}
                            </div>
                        </th>
                    </tr>
                `;
            });

            $("#inventory_details_body").html(rows);

            // Toggle for product header (collapse/expand all inner sections)
            $("#inventory_details_body").off('click', '.product-header').on('click', '.product-header', function() {
                const $header = $(this);
                const targetSelector = $header.data('target');
                const $sections = $(targetSelector);
                const $arrow = $header.find('.arrow-icon');

                $sections.slideToggle(200, function() {
                    $arrow.text($sections.is(':visible') ? '▲' : '▼');
                });
            });

            // Toggle for inner sections (order, indent, grn, etc.)
            $("#inventory_details_body").off('click', '.section-header').on('click', '.section-header', function() {
                const $header = $(this);
                const targetSelector = $header.data('target');
                const $content = $(targetSelector);
                const $arrow = $header.find('.arrow-icon');

                $content.slideToggle(200, function() {
                    $arrow.text($content.is(':visible') ? '▲' : '▼');
                });
            });

            let modal = new bootstrap.Modal(document.getElementById('productLifeCycleModal'));
            modal.show();
        },
        error: function() {
            toastr.error("Something went wrong!");
        }
    });
}