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
                        <div id="section-content-${secId}" class="section-content">
                            <div class="p-3 border bg-light">${contentHtml}</div>
                        </div>
                    </div>
                `;
            }

            let rows = '';

            response.data.forEach(function(item, index) {

                let productName = item.product_name ?? "";
                let extra = [item.specification, item.size].filter(Boolean).join(' - ');

                // ================= MANUAL ORDER =================                

                let html = '';

                item.manualPo.forEach(order => {
                    let orderShown = false; // প্রথম row-এ order date ও qty দেখানোর জন্য

                    // যদি GRN না থাকে, ফাঁকা array বানানো
                    const grns = order.grn?.length ? order.grn : [null];

                    grns.forEach(grn => {
                        // যদি Issue না থাকে
                        const issues = order.issue?.length ? order.issue : [null];

                        issues.forEach(issue => {
                            // যদি Consume না থাকে
                            const consumes = issue && issue.consume?.length ? issue.consume : [null];

                            consumes.forEach(consume => {
                                html += `<tr>`;

                                // Order column
                                if (!orderShown) {
                                    html += `<td>${order.order_date}</td><td>${order.order_qty}</td>`;
                                    orderShown = true;
                                } else {
                                    html += `<td></td><td></td>`;
                                }

                                // GRN column
                                if (grn) {
                                    html += `<td>${grn.added_date}</td><td>${grn.grn_qty}</td>`;
                                } else {
                                    html += `<td colspan="2" class="text-center">No GRN</td>`;
                                }

                                // Issue column
                                if (issue) {
                                    html += `<td>${issue.added_date ?? '-'}</td><td>${issue.qty ?? '-'}</td>`;
                                } else {
                                    html += `<td colspan="2" class="text-center">No Issue</td>`;
                                }

                                // Consume column
                                if (consume) {
                                    html += `<td>${consume.added_date ?? '-'}</td><td>${consume.qty ?? '-'}</td>`;
                                } else {
                                    html += `<td colspan="2" class="text-center">No Consume</td>`;
                                }

                                html += `</tr>`;
                            });
                        });
                    });
                });

                let orderTableHtml = `
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
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
                    <tbody>
                        ${html}
                    </tbody>
                </table>
                `;


                // ================= INDENT FLOW =================
                let indentTableHtml = '';

                if (item.indent?.length) {

                    let html = '';

                    item.indent.forEach(indent => {

                        const rfqs = indent.indentRFQ || [];

                        if (!rfqs.length) {
                            html += `
                                <tr>
                                    <td>${indent.added_date}</td>
                                    <td>${indent.indent_qty} [${indent.status}]</td>
                                    <td colspan="10" class="text-center">No Transaction Available</td>
                                </tr>
                            `;
                            return;
                        }

                        let indentRow = 0;

                        rfqs.forEach(rfq => {
                            const orders = rfq.orders || [];

                            if (!orders.length) {
                                indentRow++;
                            } else {
                                orders.forEach(order => {
                                    const issues = order.issue?.length ? order.issue : [null];

                                    issues.forEach(issue => {
                                        const consumes = issue?.consume?.length ? issue.consume : [null];
                                        indentRow += consumes.length;
                                    });
                                });
                            }
                        });

                        let rowIndex = 0;

                        rfqs.forEach(rfq => {

                            const orders = rfq.orders || [];

                            if (!orders.length) {

                                html += `<tr>`;

                                if (rowIndex === 0) {
                                    html += `<td rowspan="${indentRow}">${indent.added_date}</td>`;
                                    html += `<td rowspan="${indentRow}">${indent.indent_qty} [${indent.status}]</td>`;
                                }

                                html += `<td>${rfq.rfq_date}</td>`;
                                html += `<td>${rfq.rfq_qty}</td>`;
                                html += `<td colspan="8" class="text-center">No Transaction Available</td>`;

                                html += `</tr>`;
                                rowIndex++;
                                return;
                            }

                            orders.forEach(order => {

                                const issues = order.issue?.length ? order.issue : [null];

                                issues.forEach(issue => {

                                    const consumes = issue?.consume?.length ? issue.consume : [null];

                                    consumes.forEach((consume, c) => {

                                        html += `<tr>`;

                                        if (rowIndex === 0) {
                                            html += `<td rowspan="${indentRow}">${indent.added_date}</td>`;
                                            html += `<td rowspan="${indentRow}">${indent.indent_qty} [${indent.status}]</td>`;
                                        }

                                        if (c === 0) {
                                            html += `<td>${rfq.rfq_date}</td>`;
                                            html += `<td>${rfq.rfq_qty}</td>`;
                                            html += `<td>${order.order_date ?? '-'}</td>`;
                                            html += `<td>${order.order_qty ?? '-'}</td>`;
                                            html += `<td colspan="2" class="text-center">-</td>`;
                                        }

                                        if (issue) {
                                            html += `<td>${issue.added_date}</td>`;
                                            html += `<td>${issue.qty}</td>`;
                                        } else {
                                            html += `<td colspan="2">No Issue</td>`;
                                        }

                                        if (consume) {
                                            html += `<td>${consume.added_date}</td>`;
                                            html += `<td>${consume.qty}</td>`;
                                        } else {
                                            html += `<td colspan="2">No Consume</td>`;
                                        }

                                        html += `</tr>`;
                                        rowIndex++;
                                    });

                                });

                            });

                        });

                    });

                    indentTableHtml = `
                        <table class="table table-bordered table-sm">
                            <thead>
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
                            <tbody>${html}</tbody>
                        </table>
                    `;

                } else {
                    indentTableHtml = `<div class="text-center text-muted">No Indent Found</div>`;
                }

                let allSectionsHtml = '';
                allSectionsHtml += createSection('RFQ Order Flow', indentTableHtml, `${index}_1`);
                allSectionsHtml += createSection('Manual Order Flow', orderTableHtml, `${index}_2`);

                rows += `
                    <tr>
                        <td>
                            <div class="product-header bg-primary text-white p-2" style="cursor:pointer" data-target="#product-${index}">
                                ${productName} ${extra}
                            </div>

                            <div id="product-${index}">
                                ${allSectionsHtml}
                            </div>
                        </td>
                    </tr>
                `;
            });

            $("#inventory_details_body").html(rows);

            // toggle section
            $(document).off('click', '.section-header').on('click', '.section-header', function() {
                const target = $(this).data('target');
                $(target).slideToggle();
            });

            // toggle product
            $(document).off('click', '.product-header').on('click', '.product-header', function() {
                const target = $(this).data('target');
                $(target).slideToggle();
            });

            // ✅ SHOW MODAL
            let modalEl = document.getElementById('productLifeCycleModal');
            if (modalEl) {
                let modal = new bootstrap.Modal(modalEl);
                modal.show();
            }

        },

        error: function() {
            toastr.error("Something went wrong!");
        }
    });
}