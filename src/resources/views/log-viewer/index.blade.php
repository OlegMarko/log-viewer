@extends('log-viewer::log-viewer.layouts.main')

@section('title', 'Log Files')
@section('heading', 'Log Files')

@push('head')
    <style>
        .file-size {
            color: #ccc;
            white-space: pre-wrap;
            font-family: 'Roboto Mono', monospace;
        }
        .file-perm {
            color: #ddd;
            white-space: pre-wrap;
            font-family: 'Roboto Mono', monospace;
        }
        .delete-form {
            float: right;
        }
    </style>
@endpush

@section('content')
    <ul class="list-group">
        @include('log-viewer::log-viewer.partials.folder-structure', ['structure' => $logStructure])
    </ul>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".delete-form").forEach(form => {
                    form.addEventListener("submit", function (event) {
                        if (!confirm("Are you sure you want to delete this file?")) {
                            event.preventDefault();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
