@extends('log-viewer::log-viewer.layouts.main')

@section('title', "Viewing Log: $fileName")
@section('heading', "Viewing Log: $fileName")

@push('head')
    <style>
        pre {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            font-family: 'Roboto Mono', monospace;
        }

        .download-form {
            float: right;
        }
    </style>
@endpush

@section('content')
    <a href="{{ route('log-viewer.index') }}" class="btn btn-sm btn-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Logs
    </a>

    <form class="download-form" method="POST" action="{{ route('log-viewer.download') }}">
        @csrf

        <input type="hidden" name="path" value="{{ $filePath }}">
        <input type="hidden" name="file" value="{{ $fileName }}">

        <button type="submit" class="btn btn-sm btn-primary mb-4">
            <i class="bi bi-download"></i> Download
        </button>
    </form>

    <pre class="bg-custom p-3 rounded">{{ $xmlContent }}</pre>
@endsection
