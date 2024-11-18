@foreach ($structure as $key => $item)
    @if (is_array($item) && isset($item['name']) && isset($item['path']))
        <!-- Log File -->
        <li class="list-group-item">
            <a href="{{ route('log-viewer.show', ['filename' => $item['name'], 'path' => $item['path']]) }}"
               class="text-decoration-none text-primary">
                📄 {{ $item['name'] }}
            </a>
        </li>
    @elseif (is_array($item))
        <!-- Folder -->
        <li class="list-group-item">
            <button class="btn btn-link p-0 text-dark fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#folder-{{ \Illuminate\Support\Str::slug($key) }}"
                    aria-expanded="false"
                    aria-controls="folder-{{ \Illuminate\Support\Str::slug($key) }}"
            >📁 {{ $key }}</button>
            <div class="collapse mt-2" id="folder-{{ \Illuminate\Support\Str::slug($key) }}">
                <ul class="list-group ms-3">
                    @include('log-viewer::log-viewer.partials.folder-structure', ['structure' => $item])
                </ul>
            </div>
        </li>
    @endif
@endforeach
