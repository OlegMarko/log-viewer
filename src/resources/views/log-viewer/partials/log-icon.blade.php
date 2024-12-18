@switch($type)
    @case('critical')
        <i class="bi bi-exclamation-diamond text-danger"></i>
        @break
    @case('error')
        <i class="bi bi-x-circle text-danger"></i>
        @break
    @case('info')
        <i class="bi bi-info-circle text-info"></i>
        @break
    @case('warning')
        <i class="bi bi-exclamation-triangle text-warning"></i>
        @break
    @default
        <i class="bi bi-file-earmark-text text-muted"></i>
@endswitch
