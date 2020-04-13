@extends('layouts.app')


@push('scripts')
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" defer></script>
@endpush

@prepend('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
@endprepend

@section('content')
<div class='container'>
        <div class='card my-3 messages-log-container' >
            <div class='card-header'>
                <div class='row'>
                    <div class='col-auto'><h2>Messages log</h2></div>
                    <div class='col-auto ml-auto'><button class='btn btn-info clear-all-btn'>Clear All</button></div>
                </div>
            </div>
            <div class='card-body'style=' max-height:35vh;overflow-y: scroll;'>
            </div>
        </div>
        <div class='card my-3'>
            <div class='card-header'>
                <h2>Table Management</h2>
            </div>
            <div class='card-body'>
                <div class='form-row'>
                    <div class='col-auto mr-auto'><button data-btn-self-init-ajax='create_tables' class=' btn btn-success'><b>+</b> Create All Tables </button></div>
                    <div class='col-auto ml-auto'><button data-btn-self-init-ajax='drop_tables' class=' btn btn-danger'><b>X</b> Delete All Tables</button></div>
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
        @include('includes.deals_log_viewer',['now' => $m['now']])
    </div>
@endsection

@section('hidden_templates')
<div class='d-none'>
    <div class='card col-12 its-a-template single-log-message my-2'>
        <div class='card-header row'>
            <div class='col'></div>
            <div class='col-auto ml-auto'><button class='btn btn-danger btm-sm text-white font-weight-bold'>X</button></div>
        </div>
        <div class='card-body'></div>
    </div>
</div>
@endsection
