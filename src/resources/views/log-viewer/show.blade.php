@extends('log-viewer::log-viewer.layouts.main')

@section('title', "Viewing Log: $filename")
@section('heading', "Viewing Log: $filename")

@push('head')
    <style>
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row.critical td {
            background-color: rgba(255, 0, 0, 0.2);
        }
        .clickable-row.error td {
            background-color: rgba(255, 0, 0, 0.1);
        }
        .modal-sub-title {
            display: block;
            margin-bottom: 5px;
        }

        .download-form {
            float: right;
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
    <a href="{{ route('log-viewer.index', ['dir' => dirname($filePath)]) }}" class="btn btn-sm btn-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Logs
    </a>
    <form class="download-form" method="POST" action="{{ route('log-viewer.download') }}">
        @csrf

        <input type="hidden" name="filename" value="{{ $filename }}">

        <button type="submit" class="btn btn-sm btn-primary mb-4">
            <i class="bi bi-download"></i> Download
        </button>
    </form>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Log Summary</h5>
            <div class="row">
                <div class="col text-danger">
                    @include('log-viewer::log-viewer.partials.log-icon', ['type' => 'critical'])
                    Critical: {{ $counts['critical'] }}
                </div>
                <div class="col text-danger">
                    @include('log-viewer::log-viewer.partials.log-icon', ['type' => 'error'])
                    Errors: {{ $counts['error'] }}
                </div>
                <div class="col text-info">
                    @include('log-viewer::log-viewer.partials.log-icon', ['type' => 'info'])
                    Info: {{ $counts['info'] }}
                </div>
                <div class="col text-warning">
                    @include('log-viewer::log-viewer.partials.log-icon', ['type' => 'warning'])
                    Warnings: {{ $counts['warning'] }}
                </div>
                <div class="col text-muted">
                    @include('log-viewer::log-viewer.partials.log-icon', ['type' => 'other'])
                    Other: {{ $counts['other'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
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
                    <tr class="clickable-row {{ $entry['type'] }}" onclick="showLogDetails('logDescription{{ $index }}')">
                        <td>@include('log-viewer::log-viewer.partials.log-icon', ['type' => $entry['type']]) {{ ucfirst($entry['type']) }}</td>
                        <td>{{ $entry['time'] }}</td>
                        <td>{{ $entry['env'] }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($entry['description'], 80) }}</td>

                        <!-- Hidden fields to store the plain description and JSON -->
                        @unless($entry['only_json'])
                            <input type="hidden" id="logDescription{{ $index }}" value="{{ $entry['description'] }}">
                            <input type="hidden" id="logDescription{{ $index }}_payload_description" value="{{ $entry['payload_description'] }}">
                        @endunless
                        <input type="hidden" id="logDescription{{ $index }}_payload" value="{{ $entry['json'] }}">
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
                    <div id="logDescription"></div>
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
                let modalContent = '';

                const payload_description = document.getElementById(`${elementId}_payload_description`);
                if (payload_description && payload_description.value !== undefined && payload_description.value) {
                    modalContent += `
                    <span class="modal-sub-title">Message:</span>
                    <div class="d-flex align-items-center">
                        <pre class="bg-custom p-3 rounded flex-grow-1" id="messageContent">${payload_description.value}</pre>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyToClipboard('messageContent')">Copy</button>
                    </div>`;
                }

                if (modalContent === '') {
                    const description = document.getElementById(elementId);
                    if (description && description.value !== undefined && description.value) {
                        modalContent += `
                        <span class="modal-sub-title">Message:</span>
                        <div class="d-flex align-items-center">
                            <pre class="bg-custom p-3 rounded flex-grow-1" id="messageContent">${description.value}</pre>
                            <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyToClipboard('messageContent')">Copy</button>
                        </div>`;
                    }
                }

                const jsonData = document.getElementById(`${elementId}_payload`);
                if (jsonData && jsonData.value !== undefined && jsonData.value) {
                    modalContent += `
                    <span class="modal-sub-title">Payload:</span>
                    <div class="d-flex align-items-center">
                        <pre class="bg-custom p-3 rounded flex-grow-1" id="payloadContent">${jsonData.value}</pre>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyToClipboard('payloadContent')">Copy</button>
                    </div>`;
                }

                document.getElementById('logDescription').innerHTML = modalContent;
                new bootstrap.Modal(document.getElementById('logDetailModal')).show();
            }

            function copyToClipboard(elementId) {
                const content = document.getElementById(elementId);
                if (content) {
                    navigator.clipboard.writeText(content.textContent || content.innerText).then(() => {}).catch(err => {});
                }
            }
        </script>
    @endpush
@endsection
