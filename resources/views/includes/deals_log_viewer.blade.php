
@section('table')
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
@endsection

<div class='card my-3'>
    <div class='card-header'>
        <h2>Deals Log Table {{ $now }}</h2>
    </div>
    <div class='card-body'>
        @php  $form = 'load_deals_log_form' @endphp
        <form name='{{ $form }}' id='{{ $form }}' method="POST">
            <div class='form-row align-items-end'>
                @include('fields.date',['name'=> 'from','label'=>'From'])
                @include('fields.date',['name'=> 'to','label'=>'To'])
                <div class='col-12'></div>
                @include('fields.text',['name'=> 'client','label'=>'Client Search'])
                @include('fields.text',['name'=> 'deal','label'=>'Deal Search'])
                <div class='col-12 col-md-auto ml-auto'>
                    <button class='copy_search_link btn btn-primary align-self-center'>Copy Search Link</button>
                </div>
                <div class='col-auto '>
                    <button class='reload_table btn btn-primary align-self-end'>Load Logs</button>
                </div>
                <div class='col-12 my-2'></div>
                <div class='col-12 '>@yield('table')</div>
                <div class='col-12'></div>
                <div class='col-auto mr-auto'></div>
                <div class='col-auto ml-auto'><button class='reload_table btn btn-primary'>Load Logs</button></div>
                <div class='col-12'></div>
            </div>
        </form>
    </div>
</div>

<script type="application/javascript">
    window.dealsLogViewer = {};
    dealsLogViewer.fn = {};
    dealsLogViewer.el = {};

    $(function() {
        /* Every time the page loads, check if its URL has GET with search params */
        let landingUrl = new URL(location);
        let landingParams = qs.parse(landingUrl.search.replace(/^\?/, ''));
        if (landingParams.hasOwnProperty('deals_log_table_length')) {
            window.searchDealLogsParams = landingParams;
            window.history.pushState({}, document.title, location.origin + location.pathname);
        }
        dealsLogViewer.fn.init();
        if (window.searchDealLogsParams) {
            dealsLogViewer.fn.initDealLogsFromLandingParams();
        }
        dealsLogViewer.fn.initializeDealsLogTable();
        dealsLogViewer.fn.initCopyLinkBtn();
        dealsLogViewer.fn.initReloadTableBtn();
    });


    dealsLogViewer.fn.init = function() {
        dealsLogViewer.el.form= document.querySelector('form[name="load_deals_log_form"]');
        dealsLogViewer.el.reloadTableBtns = Array.from(document.querySelectorAll('.reload_table.btn'));
        dealsLogViewer.el.dealLogsTable = document.querySelector('.deals_log_table');
        dealsLogViewer.el.jDealLogsTable = $(dealsLogViewer.el.dealLogsTable);
        dealsLogViewer.el.logsForm = document.querySelector('form[name="load_deals_log_form"]');
        dealsLogViewer.el.from = document.querySelector('form[name="load_deals_log_form"] input[name="from"]');
        dealsLogViewer.el.from.value = '';
        dealsLogViewer.el.to = document.querySelector('form[name="load_deals_log_form"] input[name="to"]');
        dealsLogViewer.el.to.value = '';
        dealsLogViewer.el.copyLinkBtn = document.querySelector('form[name="load_deals_log_form"] button.copy_search_link');
    };

    dealsLogViewer.fn.initReloadTableBtn = function() {
        dealsLogViewer.el.reloadTableBtns.forEach(b => {
            b.addEventListener('click', (ev) => {
                if (ev instanceof Event) {
                    ev.preventDefault();
                }
                dealsLogViewer.el.dDealLogsTable.ajax.reload(null, false);
            });
        });
    };


    dealsLogViewer.fn.initializeDealsLogTable = function() {
        /** @type {DataTables} */
        let initConfig = {
            "processing": true,
            "serverSide": true,
            'searching': false,
            ajax: {
                data: dealsLogViewer.fn.dealLogsData,
                beforeSend: dealsLogViewer.fn.dealsLogsBeforeSend,
                url: 'deals_log',
                type: "GET",
                dataType: "json",
                complete:function(){
                    let u = new URL(this.url);
                    window.lastDealLogsUrl = location.origin + location.pathname + u.search;
                }
            },
            columns: [{
                    data: 'client',
                },
                {
                    data: 'deal',
                },
                {
                    data: 'time',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }
                },
                {
                    data: 'accepted',
                },
                {
                    data: 'refused',
                },
            ]
        };
        if (window.searchDealLogsParams) {
            let {
                start,
                length,
                order
            } = window.searchDealLogsParams;
            if (length) {
                initConfig.pageLength = parseInt(length);
            }
            if (order) {
                let formattedOrder = [];
                order.forEach(o => {
                    formattedOrder.push([o.column, o.dir]);
                })
                initConfig.order = formattedOrder;
            }
            if (start) {
                initConfig.displayStart = parseInt(start) * initConfig.pageLength;
            }
        }
        delete window.searchDealLogsParams;

        dealsLogViewer.el.jDealLogsTable.on('xhr.dt',function(e,settings,json,xhr){
            let api = new $.fn.dataTable.Api(settings);
            console.log(api);
            console.log(xhr);
        });

        dealsLogViewer.el.jDealLogsTable.DataTable(initConfig);
        dealsLogViewer.el.dDealLogsTable = dealsLogViewer.el.jDealLogsTable.DataTable();
        dealsLogViewer.el.logsForm.addEventListener('keydown', ev => {
            if (ev.key == 'Enter') {
                ev.preventDefault;
                dealsLogViewer.el.dDealLogsTable.ajax.reload(null, false);
            }
        });
    };

    dealsLogViewer.fn.dealLogsData = function(req) {
        let f = new FormData(dealsLogViewer.el.logsForm);
        for (var pair of f.entries()) {
            req[pair[0]] = pair[1];
        }
    };

    dealsLogViewer.fn.dealsLogsBeforeSend = function(req, settings) {
        window.lastDealLogsUrl = settings.url;
    }
    /**
     * @function dealsLogViewer.fn.initDealLogsFromLandingParams 
     */
    dealsLogViewer.fn.initDealLogsFromLandingParams = function() {
        let {
            deal,
            client,
            from,
            to
        } = window.searchDealLogsParams;
        [{
            deal
        }, {
            client
        }, {
            from
        }, {
            to
        }].forEach(param => {
            let el = dealsLogViewer.el.logsForm.querySelector('*[name="' + Object.keys(param)[0] + '"]');
            if (!el) return;
            el.value = Object.values(param)[0];
        });
    }
    dealsLogViewer.fn.initCopyLinkBtn = function(){
            dealsLogViewer.el.copyLinkBtn.addEventListener('click',function(ev){
            if(ev instanceof Event){
                ev.preventDefault();
            }
            let success= false;
            if(document.queryCommandSupported('copy') && document.queryCommandEnabled('copy')){
                let ta = document.createElement('ta');
                ta.classList.add('d-none');
                document.body.appendChild(ta);
                ta.textContent = window.lastDealLogsUrl;
                ta.focus();
                ta.select();
                success = document.execCommand('copy');
                if(success){
                    toastr.success('Link copied to clipboard');
                }
            }
            if(!success){
                window.history.pushState({},document.title,window.lastDealLogsUrl);
                toastr.warning('We are sorry, your browser does not support automatic copying. Please, copy the linke from the browser address bar.')
            }
        });
    };
</script>