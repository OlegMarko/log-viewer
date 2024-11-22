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
    </style>
@endpush

@section('content')
    <ul class="list-group">
        @include('log-viewer::log-viewer.partials.folder-structure', ['structure' => $logStructure])
    </ul>
@endsection
