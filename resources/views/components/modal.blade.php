{{--
    Reusable EMR modal component.
    Usage:
      <x-modal id="editModal" title="Edit Role">
          ... body content (forms, etc.) ...
          <x-slot:footer>
              <button type="submit" class="btn btn-primary">Save</button>
              <button type="button" class="btn btn-outline-secondary" onclick="closeModal('editModal')">Cancel</button>
          </x-slot:footer>
      </x-modal>

    Open:  openModal('editModal')
    Close: closeModal('editModal')

    Props:
      id      — required, unique modal id
      title   — modal header title
      size    — sm | md (default) | lg | xl
      icon    — Bootstrap icon class for header
--}}
@props([
    'id',
    'title'  => null,
    'size'   => 'md',
    'icon'   => null,
])

<div id="{{ $id }}" class="emr-modal-wrap" onclick="if(event.target===this)closeModal('{{ $id }}')">
    <div class="emr-modal {{ $size !== 'md' ? 'modal-'.$size : '' }}">

        {{-- Header --}}
        <div class="emr-modal-hd">
            @if($icon)
                <i class="bi {{ $icon }}" style="color:#4154f1;font-size:16px;flex-shrink:0"></i>
            @endif
            <div class="emr-modal-title">{{ $title }}</div>
            <button type="button" class="emr-modal-close" onclick="closeModal('{{ $id }}')" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="emr-modal-body">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
        <div class="emr-modal-ft">
            {{ $footer }}
        </div>
        @endif

    </div>
</div>

@once
@push('scripts')
<script>
    window.openModal  = id => { var el = document.getElementById(id); if (el) el.classList.add('open'); };
    window.closeModal = id => { var el = document.getElementById(id); if (el) el.classList.remove('open'); };
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.querySelectorAll('.emr-modal-wrap.open').forEach(m => m.classList.remove('open'));
    });
</script>
@endpush
@endonce
