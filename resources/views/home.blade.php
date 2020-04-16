@extends('layouts.app')


@push('scripts')
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" defer></script>
<script src="{{ asset('js/homepage.js') }}" defer></script>
@endpush

@prepend('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
@endprepend

@include('home.sections')

@section('content')
<div class='container'>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 ">
                    @yield('tabulator_navs')
                </div>
                <div class="col-12 col-md-8">
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="tab_tables" role="tabpanel" aria-labelledby="nav-tables">
                            @yield('table_management')
                        </div>
                        <div class="tab-pane fade" id="tab_csv" role="tabpanel" aria-labelledby="nav-csv">
                            @yield('csv_upload')
                        </div>
                        <div class="tab-pane fade" id="tab_deals_log" role="tabpanel" aria-labelledby="nav-deals-log">
                            @include('includes.deals_log_viewer',['now' => $m['now']])
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    @yield('logs_viewer')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- This section contains html elements that will be re-used by JS as kind of components - with help of cloneNode(true) --}}
@section('hidden_templates')
<div class='d-none'>
    <div class='card col-12 its-a-template single-log-message my-2'>
        <div class='card-header row'>
            <div class='col'></div>
            <div class='col-auto ml-auto'><button class='btn btn-danger btm-sm text-white font-weight-bold'>X</button>
            </div>
        </div>
        <div class='card-body'></div>
    </div>
</div>
@endsection