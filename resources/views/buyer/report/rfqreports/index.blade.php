@extends('buyer.layouts.app', ['title' => 'RFQ Report'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
    <style>
        .export-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .export-overlay-card {
            max-width: 320px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(13, 113, 187, 0.15);
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .export-overlay-card .progress {
            height: 0.65rem;
        }

        .export-overlay-card .progress-bar {
            transition: width 0.2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <div class="bg-white">
        @include('buyer.layouts.sidebar-menu')
    </div>

    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="bg-white active-rfq-page">
                <h3 class="card-head-line">RFQ Report</h3>

                <div class="alert alert-info small py-2" role="alert" style="margin-bottom: 25px;">
                    <strong>Note:</strong> By default the RFQ report shows the last four months. Use the RFQ Start Date and RFQ End Date filters to view other periods, but each search and export is limited to a maximum window of four months.
                </div>

                <div class="px-2">
                    <form id="rfq-report-filter"
                          action="{{ route('buyer.report.rfq-report.index') }}"
                          method="GET"
                          data-export-url="{{ route('buyer.report.rfq-report.export') }}">
                        <div class="row g-3 rfq-filter-button">

                            <!-- RFQ No -->
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="rfq_no" id="rfq-no" placeholder="" value="{{ request('rfq_no') }}">
                                        <label for="rfq-no">RFQ No</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="start-date" name="start_date" placeholder="" value="{{ $startDate ?? '' }}" autocomplete="off" />
                                        <label for="start-date">RFQ Start Date</label>
                                    </div>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="end-date" name="end_date" placeholder="" value="{{ $endDate ?? '' }}" autocomplete="off" />
                                        <label for="end-date">RFQ End Date</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch / Unit -->
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-diagram-3"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="branch-unit" name="branch_unit">
                                            <option value="">All</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->branch_id }}" {{ request('branch_unit') == $branch->branch_id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="branch-unit">Branch / Unit</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-person"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="username" name="username">
                                            <option value="">All</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ request('username') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="username">Username</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Product -->
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-box"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="product" name="product" placeholder="" value="{{ request('product') }}">
                                        <label for="product">Product</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span> Search
                                    </button>
                                    <a href="{{ route('buyer.report.rfq-report.index') }}" class="ra-btn small-btn ra-btn-outline-danger" id="reset-filter">
                                        <span class="bi bi-arrow-clockwise"></span> Reset
                                    </a>
                                    <button type="button" class="ra-btn small-btn ra-btn-outline-primary" id="export-report">
                                        <span class="bi bi-download"></span> Export
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="table-responsive p-2" id="rfq-report-table">
                    @include('buyer.report.rfqreports.partials.table', ['results' => $results, 'totals' => $totals ?? []])
                </div>
            </div>
        </div>
    </main>

    <div class="export-overlay d-none" id="export-loading-overlay" role="status" aria-live="polite">
        <div class="export-overlay-card">
            <div class="spinner-border text-primary" role="presentation" aria-hidden="true"></div>
            <p class="fw-semibold text-primary mt-3 mb-1">Preparing your export…</p>
            <p class="text-muted small mb-3" id="export-progress-text">This may take a few moments.</p>
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" id="export-progress-bar" style="width: 0%;">0%</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script>
        // Ajax table loader
        function loadRfqReportTable(url) {
            $.get(url, function (response) {
                $('#rfq-report-table').html(response);
            });
        }

        function hasExportableResults() {
            const container = $('#rfq-report-table');
            const metadata = container.find('[data-role="rfq-report-meta"]').first();

            if (metadata.length) {
                const value = metadata.data('hasResults');
                if (typeof value === 'string') {
                    return value.toLowerCase() === 'true';
                }

                return Boolean(value);
            }

            const rows = container.find('table tbody tr');
            if (!rows.length) {
                return false;
            }

            if (rows.length === 1) {
                const firstRow = rows.first();
                const cell = firstRow.find('td').first();
                if (cell.length && cell.attr('colspan') === '16') {
                    return false;
                }
            }

            return true;
        }

        $(document).ready(function () {
            const today = new Date();
            const MAX_RANGE_MONTHS = 4;

            // ----- Helpers -----
            function formatDate(date) {
                if ($.datetimepicker && typeof $.datetimepicker.formatDate === 'function') {
                    return $.datetimepicker.formatDate('d/m/Y', date);
                }
                const dd = String(date.getDate()).padStart(2, '0');
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const yyyy = date.getFullYear();
                return dd + '/' + mm + '/' + yyyy;
            }
            function parseDate(value) {
                if (!value) return null;
                const parts = value.split('/');
                if (parts.length !== 3) return null;
                const day = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1;
                const year = parseInt(parts[2], 10);
                const d = new Date(year, month, day);
                return Number.isNaN(d.getTime()) ? null : d;
            }
            function cloneDate(d){ return new Date(d.getTime()); }
            function addMonths(d, m){ const r = cloneDate(d); r.setMonth(r.getMonth() + m); return r; }

            function defaultRange() {
                const end = cloneDate(today);
                const start = addMonths(end, -MAX_RANGE_MONTHS);
                return { start, end };
            }

            function normalizeDateInputs() {
                let startDate = parseDate($('#start-date').val());
                let endDate = parseDate($('#end-date').val());

                if (!startDate && !endDate) {
                    const range = defaultRange();
                    startDate = range.start;
                    endDate = range.end;
                }

                if (endDate && endDate > today) {
                    endDate = cloneDate(today);
                }

                if (startDate && !endDate) {
                    endDate = addMonths(startDate, MAX_RANGE_MONTHS);
                    if (endDate > today) {
                        endDate = cloneDate(today);
                    }
                }

                if (!startDate && endDate) {
                    startDate = addMonths(endDate, -MAX_RANGE_MONTHS);
                }

                if (startDate && endDate && startDate > endDate) {
                    startDate = cloneDate(endDate);
                }

                if (startDate) {
                    startDate.setHours(0, 0, 0, 0);
                }
                if (endDate) {
                    endDate.setHours(0, 0, 0, 0);
                }

                if (startDate) {
                    $('#start-date').val(formatDate(startDate));
                }
                if (endDate) {
                    $('#end-date').val(formatDate(endDate));
                }

                $('#start-date').datetimepicker('setOptions', {
                    maxDate: endDate ? endDate : today,
                    minDate: false
                });

                $('#end-date').datetimepicker('setOptions', {
                    minDate: startDate ? startDate : false,
                    maxDate: today
                });
            }

            function validateRange(showAlert = true) {
                const startDate = parseDate($('#start-date').val());
                const endDate = parseDate($('#end-date').val());

                if (!startDate || !endDate) {
                    return true;
                }

                const maxEnd = addMonths(startDate, MAX_RANGE_MONTHS);
                if (endDate > maxEnd) {
                    if (showAlert) {
                        alert('Please select a date range of four months or less.');
                    }
                    return false;
                }

                return true;
            }

            // Init pickers
            $('#start-date').datetimepicker({
                timepicker: false,
                format: 'd/m/Y',
                scrollMonth: false,
                scrollInput: false,
                onShow: normalizeDateInputs,
                onSelectDate: normalizeDateInputs
            });

            $('#end-date').datetimepicker({
                timepicker: false,
                format: 'd/m/Y',
                scrollMonth: false,
                scrollInput: false,
                onShow: normalizeDateInputs,
                onSelectDate: normalizeDateInputs
            });

            // First pass
            normalizeDateInputs();

            // Submit -> AJAX refresh
            $(document).on('submit', '#rfq-report-filter', function (e) {
                e.preventDefault();
                
                // Ensure dates are set
                if (!$('#start-date').val() || !$('#end-date').val()) {
                    normalizeDateInputs();
                }
                
                normalizeDateInputs();
                if (!validateRange()) {
                    return false;
                }
                const form = $(this);
                const url = form.attr('action') + '?' + form.serialize();
                loadRfqReportTable(url);
            });

            // Pagination (AJAX)
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                normalizeDateInputs();
                loadRfqReportTable($(this).attr('href'));
            });

            // Per-Page change (AJAX)
            $(document).on('change', '#perPage', function () {
                normalizeDateInputs();
                const form = $('#rfq-report-filter');
                const perPage = $(this).val();
                const url = form.attr('action') + '?' + form.serialize() + '&per_page=' + perPage;
                loadRfqReportTable(url);
            });

            const exportOverlay = $('#export-loading-overlay');
            const exportProgressBar = $('#export-progress-bar');
            const exportProgressText = $('#export-progress-text');

            if (exportOverlay.length) {
                exportOverlay.appendTo('body');
            }
            let exportInProgress = false;
            let overlayHideTimeout = null;

            function buildExportUrl(baseUrl, queryString) {
                const url = new URL(baseUrl, window.location.origin);
                if (queryString) {
                    const params = new URLSearchParams(queryString);
                    params.forEach((value, key) => {
                        url.searchParams.append(key, value);
                    });
                }
                return url.toString();
            }

            function showExportOverlay(message) {
                if (overlayHideTimeout) {
                    clearTimeout(overlayHideTimeout);
                    overlayHideTimeout = null;
                }
                exportOverlay.removeClass('d-none');
                exportProgressBar.removeClass('bg-danger');
                exportProgressBar.css('width', '0%').text('0%');
                exportProgressBar.attr({'aria-valuenow': 0});
                exportProgressText.text(message || 'This may take a few moments.');
            }

            function scheduleOverlayReset(delay) {
                if (overlayHideTimeout) {
                    clearTimeout(overlayHideTimeout);
                }

                overlayHideTimeout = setTimeout(() => {
                    exportOverlay.addClass('d-none');
                    exportProgressBar.removeClass('progress-bar-striped progress-bar-animated bg-danger');
                    exportProgressBar.css('width', '0%').text('0%');
                    exportProgressBar.attr({'aria-valuenow': 0});
                    exportProgressText.text('This may take a few moments.');
                    overlayHideTimeout = null;
                }, delay);
            }

            function updateProgressDisplay(received, total) {
                if (total) {
                    const percent = Math.max(0, Math.min(100, Math.round((received / total) * 100)));
                    exportProgressBar.removeClass('progress-bar-striped progress-bar-animated');
                    exportProgressBar.css('width', percent + '%').text(percent + '%');
                    exportProgressBar.attr({'aria-valuenow': percent});
                    exportProgressText.text('Downloading… ' + percent + '%');
                } else {
                    exportProgressBar.addClass('progress-bar-striped progress-bar-animated');
                    exportProgressBar.css('width', '100%').text('');
                    exportProgressBar.attr({'aria-valuenow': 100});
                    exportProgressText.text('Preparing download…');
                }
            }

            function extractFilenameFromDisposition(disposition) {
                if (!disposition) {
                    return null;
                }

                const encodedMatch = disposition.match(/filename\*=([^;]+)/i);
                if (encodedMatch) {
                    const value = encodedMatch[1].split("''", 2);
                    if (value.length === 2) {
                        return decodeURIComponent(value[1].replace(/"/g, '').trim());
                    }
                }

                const quotedMatch = disposition.match(/filename="?([^";]+)"?/i);
                if (quotedMatch) {
                    return quotedMatch[1].trim();
                }

                return null;
            }

            function readErrorBlob(blob, contentType, fallbackMessage) {
                return new Promise((resolve) => {
                    if (!blob || !blob.size) {
                        resolve(fallbackMessage);
                        return;
                    }

                    const reader = new FileReader();
                    reader.onloadend = function () {
                        const text = reader.result || '';
                        let message = fallbackMessage;

                        if (text) {
                            if (contentType.includes('application/json')) {
                                try {
                                    const data = JSON.parse(text);
                                    if (data && data.message) {
                                        message = data.message;
                                    } else {
                                        message = text;
                                    }
                                } catch (_error) {
                                    message = text;
                                }
                            } else {
                                message = text;
                            }
                        }

                        resolve(message);
                    };
                    reader.onerror = function () {
                        resolve(fallbackMessage);
                    };
                    reader.readAsText(blob);
                });
            }

            function downloadWithProgress(url) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    let totalBytes = null;

                    xhr.open('GET', url, true);
                    xhr.responseType = 'blob';
                    xhr.withCredentials = true;
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    const updateTotalFromHeaders = () => {
                        const headerLength = parseInt(xhr.getResponseHeader('Content-Length'), 10);
                        if (Number.isFinite(headerLength) && headerLength > 0) {
                            totalBytes = headerLength;
                        }
                    };

                    xhr.addEventListener('readystatechange', function () {
                        if (xhr.readyState === XMLHttpRequest.HEADERS_RECEIVED) {
                            updateTotalFromHeaders();
                            updateProgressDisplay(0, totalBytes);
                        }
                    });

                    xhr.addEventListener('progress', function (event) {
                        if (event.lengthComputable) {
                            totalBytes = event.total;
                        }
                        updateProgressDisplay(event.loaded, totalBytes);
                    });

                    xhr.addEventListener('load', async function () {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            const finalTotal = totalBytes || (xhr.response ? xhr.response.size : null) || 0;
                            updateProgressDisplay(finalTotal, totalBytes || finalTotal);
                            resolve({
                                blob: xhr.response,
                                filename: extractFilenameFromDisposition(xhr.getResponseHeader('Content-Disposition'))
                            });
                            return;
                        }

                        const fallbackMessage = 'Failed to export. Please try again.';
                        const contentType = xhr.getResponseHeader('Content-Type') || '';
                        const message = await readErrorBlob(xhr.response, contentType, fallbackMessage);
                        reject(new Error(message));
                    });

                    xhr.addEventListener('error', function () {
                        reject(new Error('Failed to export. Please try again.'));
                    });

                    xhr.send();
                });
            }

            function triggerDownload(blob, filename) {
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = filename || 'rfq-report.xlsx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(downloadUrl);
            }

            // Export
            $('#export-report').on('click', function (e) {
                e.preventDefault();
                if (exportInProgress) {
                    return;
                }

                const button = $(this);
                const form = $('#rfq-report-filter');
                normalizeDateInputs();
                if (!validateRange()) {
                    return false;
                }

                if (!hasExportableResults()) {
                    window.alert('No RFQ report data found.');
                    return;
                }

                const exportUrl = form.data('export-url');
                const queryString = form.serialize();
                const requestUrl = buildExportUrl(exportUrl, queryString);

                exportInProgress = true;
                button.prop('disabled', true).addClass('disabled');
                showExportOverlay('Preparing your export. Please wait…');

                downloadWithProgress(requestUrl)
                    .then(({ blob, filename }) => {
                        triggerDownload(blob, filename);
                        exportProgressBar.removeClass('progress-bar-striped progress-bar-animated');
                        exportProgressBar.css('width', '100%').text('100%');
                        exportProgressBar.attr({'aria-valuenow': 100});
                        exportProgressText.text('Download ready.');
                        scheduleOverlayReset(1200);
                    })
                    .catch((error) => {
                        const message = (error && error.message) ? error.message : 'Failed to export. Please try again.';
                        exportProgressBar.removeClass('progress-bar-striped progress-bar-animated');
                        exportProgressBar.addClass('bg-danger');
                        exportProgressBar.css('width', '100%').text('Error');
                        exportProgressBar.attr({'aria-valuenow': 100});
                        exportProgressText.text(message);

                        if (typeof window !== 'undefined' && typeof window.alert === 'function') {
                            const normalizedMessage = message.toLowerCase();
                            if (normalizedMessage.includes('no rfq report data found')) {
                                alert('No auction records found.');
                            }
                        }

                        scheduleOverlayReset(1800);
                    })
                    .finally(() => {
                        exportInProgress = false;
                        button.prop('disabled', false).removeClass('disabled');
                    });
            });
        });
    </script>
@endsection
