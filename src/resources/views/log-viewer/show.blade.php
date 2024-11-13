@extends('log-viewer::log-viewer.layouts.main')

@section('title', "Viewing Log: $filename")
@section('heading', "Viewing Log: $filename")

@push('head')
    <style>
        .clickable-row {
            cursor: pointer;
        }

        pre {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            font-family: 'Roboto Mono', monospace;
        }
    </style>
@endpush

@section('content')
    <a href="{{ route('logs.index', ['dir' => dirname($filePath)]) }}" class="btn btn-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Logs
    </a>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Log Summary</h5>
            <div class="row">
                <div class="col text-info">ℹ️ Info: {{ $counts['info'] }}</div>
                <div class="col text-danger">❌ Errors: {{ $counts['error'] }}</div>
                <div class="col text-warning">⚠️ Warnings: {{ $counts['warning'] }}</div>
                <div class="col text-muted">📄 Other: {{ $counts['other'] }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Time</th>
                    <th>Environment</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($logContents as $index => $entry)
                    <tr class="clickable-row" onclick="showLogDetails('logDescription{{ $index }}')">
                        <td>@include('log-viewer::log-viewer.partials.log-icon', ['type' => $entry['type']]) {{ ucfirst($entry['type']) }}</td>
                        <td>{{ $entry['time'] }}</td>
                        <td>{{ $entry['env'] }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($entry['description'], 50) }}</td>

                        <!-- Hidden field to store the full log description -->
                        <input type="hidden" id="logDescription{{ $index }}" value="{{ $entry['description'] }}">
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailModalLabel">Log Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="logDescription"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function showLogDetails(elementId) {
                const description = document.getElementById(elementId).value;
                let formattedDescription;

                try {
                    const json = JSON.parse(description);
                    formattedDescription = JSON.stringify(json, null, 2);
                } catch (e) {
                    formattedDescription = description;
                }

                document.getElementById('logDescription').innerHTML = `<pre class="bg-custom p-3 rounded">${formattedDescription}<pre/>`;
                new bootstrap.Modal(document.getElementById('logDetailModal')).show();
            }
        </script>
    @endpush
@endsection
