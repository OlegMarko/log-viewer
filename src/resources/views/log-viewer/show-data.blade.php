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

    <div class="card">
        <div class="card-body">
            <pre>{!! $content !!}</pre>
        </div>
    </div>
@endsection
