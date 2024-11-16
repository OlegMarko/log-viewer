@extends('log-viewer::log-viewer.layouts.main')

@section('title', "Viewing Log: $filename")
@section('heading', "Viewing Log: $filename")

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
    </style>
@endpush

@section('content')
    <a href="{{ route('logs.index', ['dir' => dirname($filePath)]) }}" class="btn btn-sm btn-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Logs
    </a>

    <pre class="bg-custom p-3 rounded">{{ $xmlContent }}</pre>
@endsection
