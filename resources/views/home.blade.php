@extends('layouts.app')


@push('scripts')
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" defer></script>
@endpush

@prepend('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
@endprepend

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
<div class='container'>
        <div class='card my-3'>
            <div class='card-header'>
                <h1>Actual Sales CSV upload, parsing and loading from DB App</h1>
            </div>
        </div>
        <div class='card my-3'>
            <div class='card-header'>
                <h2>Table Management</h2>
            </div>
            <div class='card-body'>
                <div class='form-row'>
                    <div class='col-auto mr-auto'><button data-btn-self-init-ajax='createTable' class=' btn btn-success'><b>+</b> Create All Tables </button></div>
                    <div class='col-auto ml-auto'><button data-btn-self-init-ajax='deleteTable' class=' btn btn-danger'><b>X</b> Delete All Tables</button></div>
                </div>
            </div>
        </div>
        <div class='card my-3'>
            <div class='card-header'>
                <h2>Upload CSV</h2>
            </div>
            <div class='card-body'>
                <div class='form-row p-2 dropzone' style="border:2px dashed black;">
                    <div class='col-12 bg-ligth'>You don't need to upload a file. If a file input is empty, the file will be downloaded from a server.</div>
                    <div class='col-12 bg-ligth '>Attach a file using input, or drag and drop.</div>
                    <div class='col-12 mr-auto default-url'><input type='text' class='w-100 form-control mb-3' disabled value={{ config('csvhandler.csv_backup_url') }}/></div>
                    <div class='col-auto mr-auto'><label>Optional file<input type='file' class='form-control-file' name='csv' /></label></div>
                    <div class='col-auto align-self-center'><button name='uploadCsv' class=' btn btn-success'><b>+</b> Upload CSV from URL</button></div>
                </div>
            </div>
        </div>
        <div class='card my-3'>
            <div class='card-header'>
                <h2>Deals Log Table</h2>
            </div>
            <div class='card-body'>
                <form name='load_deals_log_form' method="POST">
                    <div class='form-row'>
                        <label class="col-12 col-md-3">From<input class='form-control' value='{{ $m['now'] }}' type='date' name='from' /></label>
                        <label class="col-12 col-md-3">To<input value='{{ $m['now'] }}' type='date' name='to' class='form-control ' /></label>
                        <div class='col-12'></div>
                        <label class='col-12 col-md-3'>Client Search <input class='form-control' type='text' name='client' /></label>
                        <label class='col-12 col-md-3 mr-auto'>Deal Search <input class='form-control' type='text' name='deal' /></label>   
                        <div class='col-auto ml-auto'><button class='reload_table btn btn-primary align-self-end'>Load Logs</button></div>
                        <div class='col-12'></div>
                        <div class='col-12 '>
                            <table class=' deals_log_table w-100' id='deals_log_table'>
                                <thead>
                                    <tr>
                                        <th data-data='client'>Client</th>
                                        <th data-data='deal'>Deal</th>
                                        <th data-data='time_string' data-sort="time">Time</th>
                                        <th data-data='accepted'>Accepted</th>
                                        <th data-data='refused'>Refused</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class='col-12'></div>
                        <div class='col-auto mr-auto'></div>
                        <div class='col-auto ml-auto'><button class='reload_table btn btn-primary'>Load Logs</button></div>
                        <div class='col-12'></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
