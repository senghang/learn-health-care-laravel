@extends('admin.layout.app')
@section('content')



    <div class="pagetitle">

      <div class="row">
        <div class="col-md-3">
          <h1>District</h1>
        </div>
        <div class="col-md-9">

            <a href="{{ url('backend/district/add') }}" class="btn btn-primary mb-3 ml-3 float-right"><i class="bi bi-plus"></i>Add</a>
            <a href="" data-bs-toggle="modal" data-bs-target="#verticalycentered" class="btn btn-primary mb-3 mr-3"><i class="bi bi-list"></i>Import</a>

        </div>
        <div class="modal fade" id="verticalycentered" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Import District</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="{{ route('districts.import') }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
    </div>

    </div><!-- End Page Title -->

 <section class="section dashboard">

        <div class="col-lg-12">

          @include('_message')
          
          <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title">District List</h5>
                    </div>
                    
                    <div class="col-md-6 mt-3">
                        <form method="GET" action="{{ route('district.list') }}">
                            <div class="row g-3 float-end align-items-center justify-content-end">
                                
                                {{-- Search field --}}
                                <div class="col-md-6">
                                    <input type="text" name="search" id="search" value="{{ Request()->search }}" class="form-control" placeholder="Search Code, Name">
                                </div>
                                <div class="col-md-6">
                                    <select name="per_page" id="per_page" class="form-select w-auto me-2" onchange="this.form.submit()">
                                        @foreach([25, 50, 100, 250, 500] as $option)
                                            <option value="{{ $option }}" {{ Request()->per_page == $option ? 'selected' : '' }}>
                                                {{ is_numeric($option) ? "Show " . $option . " entries" : 'All' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>  
                            </div>
                        </form>
                    </div>

                </div>
              
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Code</th>
                    <th scope="col">Name_KH</th>
                    <th scope="col">Name_EN</th>
                    <th scope="col">Latitude</th>
                    <th scope="col">Longtitude</th>
                    <th scope="col">Province</th>
                    <th scope="col">Postal</th>
                    <th scope="col">Note</th>
                    <th scope="col">Created_at</th>
                    <!-- <th scope="col">Updated_at</th> -->

                    <th scope="col">Action</th>

                  </tr>
                </thead>
                <tbody>
                  @foreach($getRecord as $value)
                  <tr>
                    <th scope="row">{{ $value->id }}</th>
                    <td>{{ $value->code }}</td>
                    <td>{{ $value->name_kh }}</td>
                    <td>{{ $value->name_en }}</td>
                    <td>{{ $value->latitude }}</td>
                    <td>{{ $value->longtitude }}</td>
                    <td>{{ $value->province }}</td>
                    <td>{{ $value->postal }}</td>
                    <td>{{ $value->note }}</td>
                    <td>{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                    <!-- <td>{{ date('d-m-Y', strtotime($value->updated_at)) }}</td> -->
                    <td>

                        <a href="{{ url('backend/district/edit/' . $value->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>


                    </td>

                  @endforeach
                </tbody>
              </table>
                        <div class="d-flex justify-content-between">
                        @if ($getRecord instanceof \Illuminate\Pagination\LengthAwarePaginator)
                          <div class="pagination-info">
                              Showing 
                              <strong>{{ $getRecord->firstItem() }}</strong> 
                              to 
                              <strong>{{ $getRecord->lastItem() }}</strong> 
                              of 
                              <strong>{{ $getRecord->total() }}</strong> 
                              entries
                          </div>
                        
                          <div class="pagination-links ">
                              {!! $getRecord->appends(Illuminate\Support\Facades\Request::except('page'))->links() !!}
                          </div>
                        @endif
                      </div>
            </div>

          </div>

        </div>

    </section>
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('search');
    if (input) {
        input.focus();

        // Move cursor to the end of existing text
        const val = input.value;
        input.value = '';
        input.value = val;
    }
});
</script>
@endsection