@if(session('flash'))
    <div data-flash="{{ session('flash') }}" data-type="{{ session('flash_type', 'ok') }}" style="display:none"></div>
@endif
