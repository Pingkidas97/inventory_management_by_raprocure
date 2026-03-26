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

                // ===== Manual Orders =====
                let orderTableHtml = '';
                if (item.manualPo && item.manualPo.length > 0) {
                    let ordersHtml = '';
                    item.manualPo.forEach(function(order) {
                        ordersHtml += `
                            <tr>
                                <!--
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>${order.order_no} [${order.order_status ?? '-'}]</span>
                                        <a href="${order.basePoUrl}" target="_blank" class="ra-btn font-size-11 ms-2">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                                -->
                                <td>${order.order_date ?? '-'}</td>
                                <td>${order.order_qty ?? '-'}</td>
                                <!--
                                <td>${order.rate ?? '-'}</td>
                                <td>${order.vendor_name ?? '-'}</td>
                                -->                                
                            </tr>
                        `;
                    });
                    orderTableHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <!-- <th>Order No</th> -->
                                    <th>Order Date</th>
                                    <th>Order Qty</th>
                                    <!-- <th>Rate</th> -->
                                    <!-- <th>Vendor</th> -->
                                </tr>
                            </thead>
                            <tbody>${ordersHtml}</tbody>
                        </table>
                    `;
                } else {
                    orderTableHtml = `<div class="text-center text-muted">No Orders Found</div>`;
                }

                // ===== Indents =====
                let indentTableHtml = '';

                if (item.indent && item.indent.length > 0) {
                    let indentHtml = '';

                    item.indent.forEach((indent) => {
                        const rfqs = indent.indentRFQ || [];

                        if (rfqs.length === 0) {
                            indentHtml += `
                                <tr>
                                    <td>${indent.added_date ?? '-'}</td>
                                    <td>${indent.indent_qty ?? '-'} [${indent.status ?? '-'}]</td>
                                    <td colspan="8" class="text-center">No Transaction Available</td>
                                </tr>
                            `;
                            return;
                        }

                        let indentRowspan = 0;

                        rfqs.forEach(rfq => {
                            const orders = rfq.orders || [];

                            if (orders.length === 0) {
                                indentRowspan += 1;
                            } else {
                                orders.forEach(order => {
                                    const grns = order.grn?.length ? order.grn : [null];

                                    grns.forEach(grn => {
                                        const issues = grn?.issue?.length ? grn.issue : [null];

                                        issues.forEach(issue => {
                                            const consumes = issue?.consume?.length ? issue.consume : [null];
                                            indentRowspan += consumes.length;
                                        });
                                    });
                                });
                            }
                        });

                        rfqs.forEach((rfq, j) => {
                            const orders = rfq.orders || [];

                            if (orders.length === 0) {
                                indentHtml += '<tr>';

                                if (j === 0) {
                                    indentHtml += `<td rowspan="${indentRowspan}">${indent.added_date ?? '-'}</td>`;
                                    indentHtml += `<td rowspan="${indentRowspan}">${indent.indent_qty ?? '-'} [${indent.status ?? '-'}]</td>`;
                                }

                                indentHtml += `<td>${rfq.rfq_date ?? '-'}</td>`;
                                indentHtml += `<td>${rfq.rfq_qty ?? '-'}</td>`;
                                indentHtml += `<td colspan="6" class="text-center">No Transaction Available</td>`;
                                indentHtml += '</tr>';
                                return;
                            }

                            let rfqRowspan = 0;

                            orders.forEach(order => {
                                const grns = order.grn?.length ? order.grn : [null];

                                grns.forEach(grn => {
                                    const issues = grn?.issue?.length ? grn.issue : [null];

                                    issues.forEach(issue => {
                                        const consumes = issue?.consume?.length ? issue.consume : [null];
                                        rfqRowspan += consumes.length;
                                    });
                                });
                            });

                            orders.forEach((order, k) => {
                                const grns = order.grn?.length ? order.grn : [null];

                                grns.forEach((grn, g) => {
                                    const issues = grn?.issue?.length ? grn.issue : [null];

                                    issues.forEach((issue, i) => {
                                        const consumes = issue?.consume?.length ? issue.consume : [null];

                                        consumes.forEach((consume, c) => {

                                            indentHtml += '<tr>';

                                            // INDENT
                                            if (j === 0 && k === 0 && g === 0 && i === 0 && c === 0) {
                                                indentHtml += `<td rowspan="${indentRowspan}">${indent.added_date ?? '-'}</td>`;
                                                indentHtml += `<td rowspan="${indentRowspan}">${indent.indent_qty ?? '-'} [${indent.status ?? '-'}]</td>`;
                                            }

                                            // RFQ
                                            if (k === 0 && g === 0 && i === 0 && c === 0) {
                                                indentHtml += `<td rowspan="${rfqRowspan}">${rfq.rfq_date ?? '-'}</td>`;
                                                indentHtml += `<td rowspan="${rfqRowspan}">${rfq.rfq_qty ?? '-'}</td>`;
                                            }

                                            // ORDER
                                            if (g === 0 && i === 0 && c === 0) {
                                                let orderRowspan = 0;

                                                grns.forEach(gr => {
                                                    const iss = gr?.issue?.length ? gr.issue : [null];
                                                    iss.forEach(is => {
                                                        const cons = is?.consume?.length ? is.consume : [null];
                                                        orderRowspan += cons.length;
                                                    });
                                                });

                                                indentHtml += `<td rowspan="${orderRowspan}">${order.order_date ?? '-'}</td>`;
                                                indentHtml += `<td rowspan="${orderRowspan}">${order.order_qty ?? '-'} [${order.order_status ?? '-'}]</td>`;
                                            }

                                            // GRN
                                            if (i === 0 && c === 0) {
                                                let grnRowspan = 0;

                                                issues.forEach(is => {
                                                    const cons = is?.consume?.length ? is.consume : [null];
                                                    grnRowspan += cons.length;
                                                });

                                                if (grn) {
                                                    indentHtml += `<td rowspan="${grnRowspan}">${grn.added_date ?? '-'}</td>`;
                                                    indentHtml += `<td rowspan="${grnRowspan}">${grn.grn_qty ?? '-'}</td>`;
                                                } else {
                                                    indentHtml += `<td colspan="2" class="text-center">No GRN</td>`;
                                                }
                                            }

                                            // ISSUE
                                            if (c === 0) {
                                                let issueRowspan = consumes.length;

                                                if (issue) {
                                                    indentHtml += `<td rowspan="${issueRowspan}">${issue.added_date ?? '-'}</td>`;
                                                    indentHtml += `<td rowspan="${issueRowspan}">${issue.qty ?? '-'}</td>`;
                                                } else {
                                                    indentHtml += `<td colspan="2" class="text-center">No Issue</td>`;
                                                }
                                            }

                                            // CONSUME
                                            if (consume) {
                                                indentHtml += `<td>${consume.added_date ?? '-'}</td>`;
                                                indentHtml += `<td>${consume.qty ?? '-'}</td>`;
                                            } else {
                                                indentHtml += `<td colspan="2" class="text-center">No Consume</td>`;
                                            }

                                            indentHtml += '</tr>';
                                        });
                                    });
                                });
                            });
                        });
                    });

                    indentTableHtml = `
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Indent Date</th>
                                    <th>Qty</th>
                                    <th>RFQ Date</th>
                                    <th>RFQ Qty</th>
                                    <th>Order Date</th>
                                    <th>Order Qty</th>
                                    <th>GRN Date</th>
                                    <th>GRN Qty</th>
                                    <th>Issue Date</th>
                                    <th>Issue Qty</th>
                                    <th>Consume Date</th>
                                    <th>Consume Qty</th>
                                </tr>
                            </thead>
                            <tbody>${indentHtml}</tbody>
                        </table>
                    `;
                } else {
                    indentTableHtml = `<div class="text-center text-muted">No Indent Found</div>`;
                }

                // ===== Combine sections =====
                let allSectionsHtml = '';
                allSectionsHtml += createSection('RFQ Order Flow', indentTableHtml, `${index}_1`);
                allSectionsHtml += createSection('Manual Order Flow', orderTableHtml, `${index}_2`);

                // ===== Build row for this product =====
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

            // Toggle for product header
            $("#inventory_details_body").off('click', '.product-header').on('click', '.product-header', function() {
                const $header = $(this);
                const targetSelector = $header.data('target');
                const $sections = $(targetSelector);
                const $arrow = $header.find('.arrow-icon');

                $sections.slideToggle(200, function() {
                    $arrow.text($sections.is(':visible') ? '▲' : '▼');
                });
            });

            // Toggle for inner sections
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
