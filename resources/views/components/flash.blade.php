{{--
    Flash component — reads session flash and emits data-flash attributes.
    Supports:
      session('flash')       — main message
      session('flash_type')  — ok | wrn | err | inf  (default: ok)
      session('flash_title') — optional bold title
--}}
@if(session('flash'))
<div data-flash="{{ session('flash') }}"
     data-type="{{ session('flash_type', 'ok') }}"
     data-title="{{ session('flash_title', '') }}"
     style="display:none"
     aria-hidden="true"></div>
@endif
@if(session('success'))
<div data-flash="{{ session('success') }}"
     data-type="ok"
     style="display:none" aria-hidden="true"></div>
@endif
@if(session('error'))
<div data-flash="{{ session('error') }}"
     data-type="err"
     style="display:none" aria-hidden="true"></div>
@endif
@if(session('warning'))
<div data-flash="{{ session('warning') }}"
     data-type="wrn"
     style="display:none" aria-hidden="true"></div>
@endif
