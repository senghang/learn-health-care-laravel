<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MediFlow EMR') — ប្រព័ន្ធព័ត៌មានសុខភាព</title>

    {{-- External CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Hanuman:wght@300;400;700&family=Nunito:wght@400;500;600;700;800&display=swap"/>

    @vite(['resources/css/clinic.css'])
</head>
<body>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
@include('clinics.layout.sidebar')

{{-- Topbar --}}
@include('clinics.layout.header')

{{-- Main content --}}
<main id="main">
    <div class="main-body">
        @yield('content')
    </div>
</main>

{{-- Mobile Bottom Nav --}}
@include('clinics.layout.bottom-nav')

{{-- Toast container --}}
<div class="toast-wrap" id="toastWrap"></div>

{{-- Flash data for JS toast system --}}
<div id="flashData" style="display:none"
     data-success="{{ session('success') }}"
     data-warning="{{ session('warning') }}"
     data-error="{{ session('error') }}"></div>

{{-- Flash from session (rendered as toast via emr.js) --}}
<x-flash/>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/clinic.js'])

</body>
</html>
