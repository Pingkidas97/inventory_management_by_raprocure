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
                        <div class="section-header d-flex justify-content-between align-items-center bg-secondary text-white px-2 py-1"
                            style="cursor:pointer; font-size:13px;"
                            data-target="#section-content-${secId}">
                            <span class="fw-semibold">${title}</span>
                            <span class="arrow-icon">▲</span>
                        </div>
                        <div id="section-content-${secId}" class="section-content" style="display:block;">
                            <div class="p-2 border bg-light">${contentHtml}</div>
                        </div>
                    </div>
                `;
            }

            let rows = '';

            response.data.forEach(function(item, index) {
                let productName = item.product_name ?? "";
                let extra = [item.specification, item.size].filter(Boolean).join(' - ');

                // ===== Manual Orders =====
                let orderTableHtml = renderOrders(item);
                
                // ===== Indents =====
                let indentTableHtml = renderIndentRFQ(item);

                // ===== Combine sections =====
                let allSectionsHtml = '';
                allSectionsHtml += createSection('RFQ Order Flow', indentTableHtml, `${index}_1`);
                allSectionsHtml += createSection('Manual Order Flow', orderTableHtml, `${index}_2`);

                rows += `
                    <div class="mb-2 border rounded">

                        <!-- Product Header -->
                        <div class="product-header p-2 d-flex justify-content-between align-items-center"
                            style="background: linear-gradient(to right, #0d4c7d, #2e86c1); color:white; cursor:pointer; font-size:13px;"
                            data-target="#product-sections-${index}">
                            
                            <strong>${productName}${extra ? ' - ' + extra : ''}</strong>
                            <span class="arrow-icon">▲</span>
                        </div>

                        <!-- Product Body -->
                        <div id="product-sections-${index}" class="product-sections p-2" style="display:block;">
                            ${allSectionsHtml}
                        </div>

                    </div>
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

// ===== Helper Functions =====
function renderOrders(item) {
    let html = '';
    item.manualPo?.forEach(order => {
        let grns = order.grn ?? [];
        let issues = order.issue ?? [];
        let consumes = order.consume ?? [];

        html += `<div class="row border rounded p-1 mb-1 bg-light" style="font-size:13px;">`;

        // ORDER
        html += `
        <div class="col-md-3 col-12 mb-1">
            <div class="border rounded p-1 bg-white">
                <table class="table table-bordered table-sm text-center mb-0 text-nowrap" style="margin-bottom:0; font-size:13px;">
                    <thead class="table-primary">
                        <tr><th>Order Date</th><th>Order Qty</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>${order.order_date ?? '-'}</td><td><strong>${order.order_qty ?? '-'}</strong>  [ ${order.order_status} ]</td></tr>
                    </tbody>
                </table>
            </div>
        </div>`;

        // GRN / ISSUE / CONSUME
        [['grn','GRN'], ['issue','Issue'], ['consume','Consume']].forEach(([key,title])=>{
            let data = order[key] ?? [];
            html += `<div class="col-md-3 col-12 mb-1">
                <div class="border rounded p-1 bg-white">
                    ${
                        data.length
                        ? `<table class="table table-sm table-bordered mb-0 text-center text-nowrap" style="margin-bottom:0; font-size:13px;">
                            <thead class="table-primary"><tr><th>${title} Date</th><th>${title} Qty</th></tr></thead>
                            <tbody>
                                ${data.map(d=>`<tr><td>${d.added_date??'-'}</td><td><strong>${d.qty??d.grn_qty??'-'}</strong></td></tr>`).join('')}
                            </tbody>
                        </table>`
                        : `<div class="text-muted text-center" style="font-size:13px;">No ${title}</div>`
                    }
                </div>
            </div>`;
        });

        html += `</div>`; // row end
    });

    return html || `<div class="text-center text-muted" style="font-size:13px;">No Transaction Available</div>`;
}

function renderRFQ(item) {
    let html = '';
    item.rfq?.forEach(rfq=>{
        let orders = rfq.orders ?? [];
        html += `<div class="row border rounded p-1 mb-1 bg-light align-items-start" style="font-size:13px;">`;

        // RFQ
        html += `<div class="col-md-3 col-12 mb-1">
            <div class="border rounded p-1 bg-white">
                <table class="table table-bordered table-sm text-center mb-0 text-nowrap" style="margin-bottom:0; font-size:13px;">
                    <thead class="table-primary"><tr><th>RFQ Date</th><th>RFQ Qty</th></tr></thead>
                    <tbody><tr><td>${rfq.rfq_date??'-'}</td><td><strong>${rfq.rfq_qty??'-'}</strong>${rfq.rfq_closed==="Closed"?" [ Closed ]":""}</td></tr></tbody>
                </table>
            </div>
        </div>`;

        // ORDER
        let order = orders[0] ?? null;
        if(order){
            html += `<div class="col-md-3 col-12 mb-1">
                <div class="border rounded p-1 bg-white">
                    <table class="table table-bordered table-sm text-center mb-0 text-nowrap" style="margin-bottom:0; font-size:13px;">
                        <thead class="table-primary"><tr><th>Order Date</th><th>Order Qty</th></tr></thead>
                        <tbody><tr><td>${order.order_date??'-'}</td><td><strong>${order.order_qty??'-'}</strong> [ ${order.order_status} ]</td></tr></tbody>
                    </table>
                </div>
            </div>`;
        } else {
            html += `<div class="col-md-3 col-12 mb-1">
                <div class="border rounded p-1 bg-white d-flex align-items-center justify-content-center">
                    <div class="text-muted text-center" style="font-size:13px;">No Order Found</div>
                </div>
            </div>`;
        }

        ['grn','issue','consume'].forEach(type=>{
            let data = order?.[type]??[];
            let title = type.toUpperCase();
            html += `<div class="col-md-2 col-12 mb-1">
                <div class="border rounded p-1 bg-white">
                    ${
                        data.length
                        ? `<table class="table table-sm table-bordered mb-0 text-center text-nowrap" style="margin-bottom:0; font-size:13px;">
                            <thead class="table-primary"><tr><th>${title} Date</th><th>${title} Qty</th></tr></thead>
                            <tbody>${data.map(d=>`<tr><td>${d.added_date??'-'}</td><td><strong>${d.qty??d.grn_qty??'-'}</strong></td></tr>`).join('')}</tbody>
                        </table>`
                        : `<div class="text-muted text-center" style="font-size:13px;">No ${title}</div>`
                    }
                </div>
            </div>`;
        });

        html += `</div>`; // row end
    });
    return html || `<div class="text-center text-muted" style="font-size:13px;">No Transaction Available</div>`;
}

function renderIndentRFQ(item){
    let html = '';
    html += `<div class="row border rounded p-1 mb-1" style="font-size:13px;">`;

    // Left: Indent
    html += `<div class="col-md-2 col-12 mb-1">
        <div class="border rounded p-1 bg-white">
            ${
                item.indent?.length
                ? `<table class="table table-sm table-bordered mb-0 text-center text-nowrap" style="margin-bottom:0; font-size:13px;">
                        <thead class="table-primary"><tr><th>Indent Date</th><th>Indent Qty</th></tr></thead>
                        <tbody>${item.indent.map(indent=>`<tr><td>${indent.added_date??'-'}</td><td><strong>${indent.indent_qty??'-'}</strong></td></tr>`).join('')}</tbody>
                  </table>`
                : `<div class="text-center text-muted" style="font-size:13px;">No Indent</div>`
            }
        </div>
    </div>`;

    // Right: RFQ scrollable
    html += `<div class="col-md-10 col-12 mb-1">
        <div class="overflow-auto" style="max-height:350px;">
            ${renderRFQ(item)}
        </div>
    </div>`;

    html += `</div>`;
    return html || `<div class="text-center text-muted" style="font-size:13px;">No Transaction Available</div>`;
}