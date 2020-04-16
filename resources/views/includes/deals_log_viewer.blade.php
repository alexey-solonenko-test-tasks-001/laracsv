@section('table')

@push('scripts')
<script src="{{ asset('js/deals_log_viewer.js') }}" defer></script>
@endpush

<table class='table table-striped table-bordered deals_log_table w-100' id='deals_log_table'>
    <thead>
        <tr>
            <th >Client</th>
            <th >Deal</th>
            <th >Time</th>
            <th >Accepted</th>
            <th >Refused</th>
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
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td class='text-left font-weight-bold'></td>
            <td class='text-left font-weight-bold'></td>
        </tr>
    </tfoot>
</table>
@endsection

<div class='card my-3'>
    <div class='card-header'>
        <h2>Deals Log Table</h2>
    </div>
    <div class='card-body'>
        @php $form = 'load_deals_log_form' @endphp
        <form name='{{ $form }}' id='{{ $form }}' method="POST">
            <div class='form-row align-items-end'>
                @include('fields.date',['name'=> 'from','label'=>'From'])
                @include('fields.date',['name'=> 'to','label'=>'To'])
                @include('fields.text',['name'=> 'client','label'=>'Client Search'])
                @include('fields.text',['name'=> 'deal','label'=>'Deal Search'])
                <div class='col-12'></div>
                @include('fields.cb',['name'=>'by_client','label' => 'Group By Client',])
                @include('fields.cb',['name'=>'by_deal','label' => 'Group By Deal',])
                @include('fields.select',['name'=>'group_by','label'=>'Time Grouping','options' => [ ['text' => ''],['text' => 'Month', 'val' => 'm'], ['text' => 'Day', 'val' => 'd'], ['text' => 'Hour', 'val' => 'H']]])
                <div class='col-12 my-2'></div>
                @include('fields.btn',['btnCls' => 'empty_logs','label'=> 'Delete All Logs','ajaxCall' => 'empty_deal_logs','btnBaseCls' => 'btn-block btn btn-danger'])
                @include('fields.btn',['btnCls' => 'random_data','label'=> 'Add Random Data','ajaxCall' => 'random_deal_logs','btnBaseCls' => 'btn-block btn btn-warning'])
                @include('fields.btn',['btnCls' => 'copy_search_link','label'=> 'Copy Search Link'])
                @include('fields.btn',['btnCls' => 'reload_table','label'=> 'Load Logs','btnBaseCls' => 'btn-block btn btn-success'])
                <div class='col-12 my-2'></div>
                <div class='col-12 col-md-6'>
                    <p>You can select multiple ordering by holding down <kbd>Shift</kbd> key and clicking a corrsponding header</p>
                    <p><b>Or</b>, you can apply a default multi-column ordering</p>
                </div>
                @include('fields.btn',['btnCls' => 'handy_ordering','label'=> 'Handy Ordering'])
                <div class='col-12 '>@yield('table')</div>
                <div class='col-12'></div>
                <div class='col-auto mr-auto'></div>
                @include('fields.btn',['btnCls' => 'reload_table','label'=> 'Load Logs'])
                <div class='col-12'></div>
            </div>
        </form>
    </div>
</div>
