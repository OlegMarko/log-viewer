@extends('log-viewer::log-viewer.layouts.main')

@section('title', 'Log Files')
@section('heading', 'Log Files')

@section('content')
    <ul class="list-group">
        @include('log-viewer::log-viewer.partials.folder-structure', ['structure' => $logStructure])
    </ul>
@endsection
