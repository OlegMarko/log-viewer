@php use Illuminate\Support\Str; @endphp
@foreach ($structure as $key => $item)
    @if (is_array($item) && isset($item['name']) && isset($item['path']))
        <li class="list-group-item">
            <a href="{{ route('log-viewer.show', ['file' => $item['name'], 'path' => $item['path']]) }}"
               class="text-decoration-none text-primary">
                📄 <span class="file-perm">{{ $item['perms'] ?? null }}</span> {{ $item['name'] }}
                <span class="file-size">{{ $item['size'] }}</span>
            </a>
        </li>
    @elseif (is_array($item) && isset($item['folder_structure']))
        <li class="list-group-item">
            <button class="btn btn-link p-0 text-dark fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#folder-{{ Str::slug($key) }}"
                    aria-expanded="false"
                    aria-controls="folder-{{ Str::slug($key) }}"
            >📁 <span class="file-perm">{{ $item['folder_perms'] ?? null }}</span> {{ $key }}</button>
            <div class="collapse mt-2" id="folder-{{ Str::slug($key) }}">
                <form method="POST" action="{{ route('log-viewer.download-zip') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="directory" value="{{ $key }}">
                    <button type="submit" class="btn btn-sm btn-secondary mb-2 me-2">
                        <i class="bi bi-download"></i> Download
                    </button>
                </form>

                <form method="POST" action="{{ route('log-viewer.delete-directory') }}" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this directory and all its contents?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="directory" value="{{ $key }}">
                    <button type="submit" class="btn btn-sm btn-danger mb-2">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>

                <ul class="list-group ms-3">
                    @include('log-viewer::log-viewer.partials.folder-structure', ['structure' => $item['folder_structure']])
                </ul>
            </div>
        </li>
    @endif
@endforeach
