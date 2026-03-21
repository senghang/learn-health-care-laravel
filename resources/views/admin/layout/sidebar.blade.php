  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      @php
      // Slug in database table permission
      //  $PermissionUser = App\Models\RolePermissionModel::getPermission('user.view', Auth::user()->role_id);
      //  $PermissionRole = App\Models\RolePermissionModel::getPermission('role.view', Auth::user()->role_id);
      //  $PermissionReception = App\Models\RolePermissionModel::getPermission('reception.view', Auth::user()->role_id);
      //  $PermissionPatient = App\Models\RolePermissionModel::getPermission('patient.view', Auth::user()->role_id);

      //  $PermissionService = App\Models\RolePermissionModel::getPermission('service.view', Auth::user()->role_id);
      //  $PermissionMedicine = App\Models\RolePermissionModel::getPermission('medicine.view', Auth::user()->role_id);


      //  $PermissionProvince = App\Models\RolePermissionModel::getPermission('province.view', Auth::user()->role_id);
      //  $PermissionDistrict = App\Models\RolePermissionModel::getPermission('district.view', Auth::user()->role_id);
      //  $PermissionCommune  = App\Models\RolePermissionModel::getPermission('commune.view', Auth::user()->role_id);
      //  $PermissionVillage = App\Models\RolePermissionModel::getPermission('village.view', Auth::user()->role_id);
      @endphp

      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'dashboard') collapsed @endif" href="{{ url('backend/dashboard') }}">
          <i class="bi bi-grid"></i>
          <span>DashBoard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'clinic') collapsed @endif" href="{{ url('backend/clinic') }}">
          <i class="bi bi-person"></i>
          <span>Clinic</span>
        </a>
      </li>
<!-- Reception -->
    @if(!empty($PermissionReception) )
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'reception') collapsed @endif" href="{{ url('backend/reception') }}">
          <i class="bi bi-person"></i>
          <span>Reception</span>
        </a>
      </li>
    @endif
    @if(!empty($PermissionPatient) )
<!-- Patient -->
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'patient') collapsed @endif" href="{{ url('backend/patient') }}">
          <i class="bi bi-person"></i>
          <span>Patient</span>
        </a>
      </li>
    @endif
<!-- User -->        

      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'user') collapsed @endif" href="{{ url('backend/user/clinic') }}">
          <i class="bi bi-person"></i>
          <span>Clinic | Users</span>
        </a>
      </li>   

      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'role') collapsed @endif" href="{{ url('backend/role') }}">
          <i class="bi bi-person"></i>
          <span>Clinic | Role</span>
        </a>
      </li>


      @if(!empty($PermissionService) )
<!-- Service Setup -->
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'service') collapsed @endif" href="{{ url('backend/service') }}">
          <i class="bi bi-person"></i>
          <span>Service</span>
        </a>
      </li>
      @endif
      @if(!empty($PermissionMedicine) )
<!-- Medical -->
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'medical') collapsed @endif" href="{{ url('backend/medicine') }}">
          <i class="bi bi-person"></i>
          <span>Medicine</span>
        </a>
      </li>
      @endif

{{-- Province --}}
     
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'province') collapsed @endif" href="{{ url('backend/province') }}">
          <i class="bi bi-person"></i>
          <span>Province</span>
        </a>
      </li>
  

{{-- District --}}
     
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'district') collapsed @endif" href="{{ url('backend/district') }}">
          <i class="bi bi-person"></i>
          <span>District</span>
        </a>
      </li>
      
{{-- Commune --}}
      
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'commune') collapsed @endif" href="{{ url('backend/commune') }}">
          <i class="bi bi-person"></i>
          <span>Commune</span>
        </a>
      </li>

{{-- Village --}}
    
      <li class="nav-item">
        <a class="nav-link @if(Request::segment(2) != 'village') collapsed @endif" href="{{ url('backend/village') }}">
          <i class="bi bi-person"></i>
          <span>Village</span>
        </a>
      </li>

    </ul>
  </aside>