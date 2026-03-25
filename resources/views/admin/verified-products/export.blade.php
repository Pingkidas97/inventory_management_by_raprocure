@extends('admin.layouts.app')
@section('title', 'Export Verified Products')
@section('content')

<style>
.text-monospace {
    font-family: monospace;
}

.export-form {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10" style="margin-top: 30px;">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Export Verified Products</h3>
                </div>
                <div class="card-body">

                    <!-- Export Form -->
                    <div class="export-form">
                        <form id="exportForm">
                            @csrf
                            <button type="submit" class="btn btn-primary" id="exportButton">
                                <span id="exportButtonText">Export</span>
                                <span id="exportSpinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>

                    <div id="exportAlert" class="alert d-none mb-4"></div>

                    <!-- Export History Table -->
                    <h5>Export History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="exportsTable">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>EXPORT ID</th>
                                    <th>RECORDS</th>
                                    <th>STATUS</th>
                                    <th>DOWNLOAD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exports as $export)
                                <tr>
                                    <td>{{ $export->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-monospace">{{ $export->export_id }}</td>
                                    <td>{{ $export->record_count ?? '-' }}</td>
                                    <td>
                                        @php
                                        $statusClass = [
                                        'processing' => 'bg-warning',
                                        'completed' => 'bg-success',
                                        'failed' => 'bg-danger'
                                        ][$export->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($export->status) }}</span>
                                    </td>
                                    <td>
                                        @if($export->status === 'completed')
                                        <a href="{{ route('admin.exports.download', $export->export_id) }}"
                                            class="btn btn-sm btn-outline-primary">Download</a>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportForm = document.getElementById('exportForm');
    const exportButton = document.getElementById('exportButton');
    const exportButtonText = document.getElementById('exportButtonText');
    const exportSpinner = document.getElementById('exportSpinner');
    const exportAlert = document.getElementById('exportAlert');
    const exportsTable = document.getElementById('exportsTable').getElementsByTagName('tbody')[0];

    exportForm.addEventListener('submit', function(e) {
        e.preventDefault();

        exportButton.disabled = true;
        exportButtonText.textContent = 'Exporting...';
        exportSpinner.classList.remove('d-none');
        exportAlert.classList.add('d-none');

        fetch("{{ route('admin.exports-verified-products') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.export_id) {
                    showAlert('success', 'Export started successfully!');
                    addNewExportToTable(data.export_id); // ⬅️ Add new row immediately
                    trackExportStatus(data.export_id); // ⬅️ Track live status
                }
            })
            .catch(() => {
                showAlert('danger', 'Failed to start export.');
            })
            .finally(() => {
                exportButton.disabled = false;
                exportButtonText.textContent = 'Export';
                exportSpinner.classList.add('d-none');
            });
    });

    function trackExportStatus(exportId) {
        const interval = setInterval(() => {
            fetch(route('admin.exports.status', {
                    exportId
                }))
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(interval);
                        updateRowStatus(data.export_id, data.status, data.record_count);
                        if (data.status === 'completed') {
                            showAlert('success', 'Export completed!');
                            window.location.reload()
                        } else {
                            showAlert('danger', 'Export failed.');
                        }
                    }
                });
        }, 2000);
    }

    function addNewExportToTable(exportId) {
        const now = new Date();
        const formattedDate = now.toISOString().replace('T', ' ').substring(0, 19);
        const row = exportsTable.insertRow(0);
        row.setAttribute('id', `export-row-${exportId}`);
        row.innerHTML = `
                <td>${formattedDate}</td>
                <td class="text-monospace">${exportId}</td>
                <td>-</td>
                <td><span class="badge bg-warning">Processing</span></td>
                <td><span class="text-muted">-</span></td>
            `;
    }

    function updateRowStatus(exportId, status, count) {
        const row = document.getElementById(`export-row-${exportId}`);
        if (!row) return;

        const statusClass = {
            'completed': 'bg-success',
            'failed': 'bg-danger',
            'processing': 'bg-warning'
        } [status] ?? 'bg-secondary';

        const downloadBtn = status === 'completed' ?
            `<a href="${route('admin.exports.download', { exportId })}" class="btn btn-sm btn-outline-primary">Download</a>` :
            '<span class="text-muted">-</span>';

        row.innerHTML = `
                <td>${new Date().toISOString().replace('T', ' ').substring(0, 19)}</td>
                <td class="text-monospace">${exportId}</td>
                <td>${count || '-'}</td>
                <td><span class="badge ${statusClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                <td>${downloadBtn}</td>
            `;
    }

    function showAlert(type, message) {
        exportAlert.className = `alert alert-${type}`;
        exportAlert.textContent = message;
        exportAlert.classList.remove('d-none');
    }

    function route(name, params = {}) {
        let routes = {
            'admin.exports.status': "{{ route('admin.exports.status', ['exportId' => ':exportId']) }}",
            'admin.exports.download': "{{ route('admin.exports.download', ['exportId' => ':exportId']) }}"
        };

        let url = routes[name];
        for (const key in params) {
            url = url.replace(`:${key}`, params[key]);
        }
        return url;
    }
});
</script>
@endsection