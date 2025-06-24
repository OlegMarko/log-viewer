@extends('log-viewer::log-viewer.layouts.main')

@section('title', $title)
@section('heading', "Viewing Log: $fileName")

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('log-viewer.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Logs
        </a>
        <div>
            <button type="button" class="btn btn-sm btn-danger me-3" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal">
                <i class="bi bi-trash"></i> Destroy
            </button>
            <form class="download-form" method="POST" action="{{ route('log-viewer.download') }}" style="display: inline-block;">
                @csrf
                <input type="hidden" name="path" value="{{ $filePath }}">
                <input type="hidden" name="file" value="{{ $fileName }}">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-download"></i> Download
                </button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the file "<strong>{{ $fileName }}</strong>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="confirmDeleteForm" method="POST" action="{{ route('log-viewer.delete-file') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="path" value="{{ $filePath }}">
                        <input type="hidden" name="file" value="{{ $fileName }}">
                        <button type="submit" class="btn btn-danger">Delete Anyway</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Log Summary</h5>
            <div class="row text-center">
                <div class="col-6 col-md text-danger mb-2 mb-md-0">
                    <i class="bi bi-exclamation-diamond text-danger me-1"></i>
                    Critical: {{ $summary['critical'] }}
                </div>
                <div class="col-6 col-md text-danger mb-2 mb-md-0">
                    <i class="bi bi-x-circle text-danger me-1"></i>
                    Errors: {{ $summary['error'] }}
                </div>
                <div class="col-6 col-md text-info mb-2 mb-md-0">
                    <i class="bi bi-info-circle text-info me-1"></i>
                    Info: {{ $summary['info'] }}
                </div>
                <div class="col-6 col-md text-warning mb-2 mb-md-0">
                    <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                    Warnings: {{ $summary['warning'] }}
                </div>
                <div class="col-6 col-md text-muted mb-2 mb-md-0">
                    <i class="bi bi-file-earmark-text text-muted me-1"></i>
                    Other: {{ $summary['other'] }}
                </div>
                <div class="col-6 col-md text-dark mb-2 mb-md-0">
                    <i class="bi bi-journals me-1"></i>
                    Total: {{ $summary['total'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
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
                    @forelse ($entries as $index => $entry)
                        <tr class="clickable-row {{ $entry['type'] }}" onclick="showLogDetails('logEntry{{ $index }}')">
                            <td>
                                @switch($entry['type'])
                                    @case('critical')
                                        <i class="bi bi-exclamation-diamond text-danger" title="Critical"></i>
                                        @break
                                    @case('error')
                                        <i class="bi bi-x-circle text-danger" title="Error"></i>
                                        @break
                                    @case('warning')
                                        <i class="bi bi-exclamation-triangle text-warning" title="Warning"></i>
                                        @break
                                    @case('info')
                                        <i class="bi bi-info-circle text-info" title="Info"></i>
                                        @break
                                    @case('debug')
                                        <i class="bi bi-bug-fill text-secondary" title="Debug"></i>
                                        @break
                                    @case('notice')
                                        <i class="bi bi-bell-fill text-primary" title="Notice"></i>
                                        @break
                                    @case('alert')
                                        <i class="bi bi-megaphone-fill text-danger" title="Alert"></i>
                                        @break
                                    @case('emergency')
                                        <i class="bi bi-exclamation-octagon-fill text-danger" title="Emergency"></i>
                                        @break
                                    @default
                                        <i class="bi bi-file-earmark-text text-muted" title="Other"></i>
                                @endswitch
                                <span class="d-none d-md-inline">{{ ucfirst($entry['type']) }}</span>
                            </td>
                            <td>{{ $entry['time'] }}</td>
                            <td>{{ $entry['env'] }}</td>
                            <td>{{ $entry['short_description'] }}</td>

                            <input type="hidden" id="logEntry{{ $index }}_full_description" value="{{ $entry['description'] }}">
                            <input type="hidden" id="logEntry{{ $index }}_payload_description" value="{{ $entry['payload_description'] }}">
                            <input type="hidden" id="logEntry{{ $index }}_json" value="{{ $entry['json'] }}">
                            <input type="hidden" id="logEntry{{ $index }}_only_json" value="{{ $entry['only_json'] ? 'true' : 'false' }}">
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No log entries found for this page.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailModalLabel">Log Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="logDescriptionContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showLogDetails(elementPrefix) {
                let modalContentHtml = '';

                const fullDescriptionElement = document.getElementById(`${elementPrefix}_full_description`);
                const payloadDescriptionElement = document.getElementById(`${elementPrefix}_payload_description`);
                const jsonElement = document.getElementById(`${elementPrefix}_json`);
                const onlyJsonElement = document.getElementById(`${elementPrefix}_only_json`);

                const fullDescription = fullDescriptionElement ? fullDescriptionElement.value : '';
                const payloadDescription = payloadDescriptionElement ? payloadDescriptionElement.value : '';
                const jsonData = jsonElement ? jsonElement.value : null;
                const isOnlyJson = onlyJsonElement ? onlyJsonElement.value === 'true' : false;

                let displayDescription = '';
                if (isOnlyJson && jsonData) {
                } else if (payloadDescription) {
                    displayDescription = payloadDescription;
                } else if (fullDescription) {
                    displayDescription = fullDescription;
                }

                if (displayDescription) {
                    modalContentHtml += `
                    <span class="modal-sub-title">Message:</span>
                    <div class="d-flex align-items-start">
                        <pre id="messageContent" class="flex-grow-1">${escapeHtml(displayDescription)}</pre>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyToClipboard('messageContent')" title="Copy message to clipboard">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>`;
                }

                if (jsonData && jsonData !== 'null') {
                    modalContentHtml += `
                    <span class="modal-sub-title">Content:</span>
                    <div class="d-flex align-items-start">
                        <pre id="payloadContent" class="flex-grow-1">${escapeHtml(jsonData)}</pre>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyToClipboard('payloadContent')" title="Copy content to clipboard">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>`;
                }

                if (!displayDescription && (!jsonData || jsonData === 'null')) {
                    modalContentHtml += `
                    <span class="modal-sub-title">Raw Log Entry:</span>
                    <div class="d-flex align-items-start">
                        <pre id="messageContent" class="flex-grow-1">${escapeHtml(fullDescription)}</pre>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyToClipboard('messageContent')" title="Copy raw entry to clipboard">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>`;
                }

                document.getElementById('logDescriptionContent').innerHTML = modalContentHtml;
                new bootstrap.Modal(document.getElementById('logDetailModal')).show();
            }

            function copyToClipboard(elementId) {
                const contentElement = document.getElementById(elementId);
                if (contentElement) {
                    const textToCopy = contentElement.textContent || contentElement.innerText;

                    const tempTextArea = document.createElement('textarea');
                    tempTextArea.value = textToCopy;
                    tempTextArea.style.position = 'fixed';
                    tempTextArea.style.top = '0';
                    tempTextArea.style.left = '0';
                    tempTextArea.style.width = '2em';
                    tempTextArea.style.height = '2em';
                    tempTextArea.style.padding = '0';
                    tempTextArea.style.border = 'none';
                    tempTextArea.style.outline = 'none';
                    tempTextArea.style.boxShadow = 'none';
                    tempTextArea.style.background = 'transparent';
                    document.body.appendChild(tempTextArea);

                    tempTextArea.focus();
                    tempTextArea.select();
                    tempTextArea.setSelectionRange(0, tempTextArea.value.length);

                    try {
                        const successful = document.execCommand('copy');
                        const msg = successful ? 'Copied!' : 'Copy failed.';
                        console.log('Copy command: ' + msg);
                    } catch (err) {
                        console.error('Failed to copy text: ', err);
                    } finally {
                        document.body.removeChild(tempTextArea);
                    }
                }
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        </script>
    @endpush
@endsection