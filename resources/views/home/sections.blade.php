@section('csv_upload')
<div class='card my-3'>
    <div class='card-header'>
        <h2>Upload CSV</h2>
    </div>
    <div class='card-body'>
        <form name='upload_csv_form'>
            <div class='form-row p-2 dropzone' style="border:2px dashed black;">
                <div class='col-12 bg-ligth'>You don't need to upload a file. If a file input is empty, the file
                    will be downloaded from a server.</div>
                <div class='col-12 bg-ligth '>Attach a file using input, or drag and drop.</div>
                <div class='col-12 mr-auto default-url'><input type='text' class='w-100 form-control mb-3' disabled
                        value={{ config('csvhandler.csv_backup_url') }} /></div>
                <div class='col-auto mr-auto'><label>Optional file<input type='file' class='form-control-file'
                            name='csv' /></label></div>
                <div class='col-auto align-self-center'><button name='uploadCsv' class=' btn btn-success'><b>+</b>
                        Upload CSV from URL</button></div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('logs_viewer')
<div class='card my-3 messages-log-container'>
    <div class='card-header'>
        <div class='row'>
            <div class='col-auto'>
                <h2>Messages log</h2>
            </div>
            <div class='col-auto ml-auto'><button class='btn btn-info clear-all-btn'>Clear All</button></div>
        </div>
    </div>
    <div class='card-body' style=' max-height:65vh;overflow-y: scroll;'>
    </div>
</div>
@endsection

@section('table_management')
<div class='card my-3'>
    <div class='card-header'>
        <h2>Table Management</h2>
    </div>
    <div class='card-body'>
        <form name='tables_management'>
            <div class='form-row'>
                <div class='col-auto mr-auto'><button data-btn-self-init-ajax='create_tables'
                        class=' btn btn-success'><b>+</b> Create All Tables </button></div>
                <div class='col-auto ml-auto'><button data-btn-self-init-ajax='drop_tables'
                        class=' btn btn-danger'><b>X</b> Delete All Tables</button></div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('tabulator_navs')
<ul class=" nav nav-tabs " id="tabulator_csv" role="tablist">
    @include('includes.tabulator.nav_item',['id' => 'nav_tables','href' => '#tab_tables', 'label' => 'Manage Tables','ariaC' => 'tables','active' => true])
    @include('includes.tabulator.nav_item',['id' => 'nav_csv','href' => '#tab_csv', 'label' => 'CSV Upload','ariaC' => 'csvUpload'])
    @include('includes.tabulator.nav_item',['id' => 'nav_deals_log','href' => '#tab_deals_log', 'label' => 'Deals Log','ariaC' => 'dealsLogs'])
</ul>
@endsection