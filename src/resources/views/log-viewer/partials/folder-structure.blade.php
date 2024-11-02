@foreach ($structure as $key => $item)
    @if (is_array($item) && isset($item['name']) && isset($item['path']))
        <!-- Log File -->
        <li class="list-group-item">
            <a href="{{ route('logs.show', ['filename' => $item['name'], 'path' => $item['path']]) }}"
               class="text-decoration-none text-primary">
                📄 {{ $item['name'] }}
            </a>
        </li>
    @elseif (is_array($item))
        <!-- Folder -->
        <li class="list-group-item">
            <strong>📁 {{ $key }}</strong>
            <ul class="list-group ms-3 mt-2">
                @include('log-viewer::log-viewer.partials.folder-structure', ['structure' => $item])
            </ul>
        </li>
    @endif
@endforeach
