window.homeP = {};
homeP.el = {};
homeP.fn = {};
toastr.options.timeOut = 15000;
toastr.options.extendedTimeOut = 15000;

console.log('start up');
console.log('start up');

/* Every time the page loads, check if its URL has GET with search params */
let landingUrl = new URL(location);
let landingParams = qs.parse(landingUrl.search.replace(/^\?/,''));
if(landingParams.hasOwnProperty('deals_log_table_length')){
    window.searchDealLogsParams = landingParams;
    window.history.pushState({},document.title,location.origin + location.pathname);
}

$(function () {
    homeP.fn.init();
    homeP.fn.initSelfInittedAjaxButtons();
    if(window.searchDealLogsParams){
        homeP.fn.initDealLogsFromLandingParams();
    }
    homeP.fn.initializeDealsLogTable();

    homeP.fn.initReloadTableBtn();
    homeP.fn.initializeUploadCsvBtn();
    homeP.fn.initializeDropzone();
});

/**
 * @function homeP.fn.initDealLogsFromLandingParams 
 */
homeP.fn.initDealLogsFromLandingParams = function(){
    let {deal,client,from,to} = window.searchDealLogsParams;
    [{deal},{client},{from},{to}].forEach(param => {
        let el = homeP.el.logsForm.querySelector('*[name="'+Object.keys(param)[0]+'"]');
        if(!el) return;
        el.value = Object.values(param)[0];
    });
}


homeP.fn.initializeDealsLogTable = function () {
    /** @type {DataTables} */
    let initConfig = {
        "processing": true,
        "serverSide": true,
        'searching': false,
        ajax: {
            data: homeP.fn.dealLogsData,
            beforeSend: homeP.fn.dealsLogsBeforeSend,
            url: 'deals_log',
            type: "GET",
            dataType: "json",
        },
        columns: [
            {
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
    if(window.searchDealLogsParams) {
        let {start,length,order} = window.searchDealLogsParams;
        if(length){
            initConfig.pageLength= parseInt(length);
        }
        if(order){
            let formattedOrder = [];
            order.forEach(o  => {
                formattedOrder.push([o.column,o.dir]);
            })
            initConfig.order = formattedOrder;
        }
        if(start){
            initConfig.displayStart = parseInt(start) * initConfig.pageLength;
        }
    }
    delete window.searchDealLogsParams;

    homeP.el.jDealLogsTable.DataTable(initConfig);
    homeP.el.dDealLogsTable = homeP.el.jDealLogsTable.DataTable();
    homeP.el.logsForm.addEventListener('keydown', ev => {
        if (ev.key == 'Enter') {
            ev.preventDefault;
            homeP.el.dDealLogsTable.ajax.reload(null, false);
        }
    });
};

homeP.fn.dealLogsData = function (req) {
    let f = new FormData(homeP.el.logsForm);
    for (var pair of f.entries()) {
        req[pair[0]] = pair[1];
    }
};

homeP.fn.dealsLogsBeforeSend = function(req, settings) {
    window.lastDealLogsUrl = settings.url;
}


homeP.fn.init = function () {
    homeP.el.reloadTableBtns = Array.from(document.querySelectorAll('.reload_table.btn'));
    homeP.el.dealLogsTable = document.querySelector('.deals_log_table');
    homeP.el.jDealLogsTable = $(homeP.el.dealLogsTable);
    homeP.el.logsForm = document.querySelector('form[name="load_deals_log_form"]');
    homeP.el.from = document.querySelector('form[name="load_deals_log_form"] input[name="from"]');
    homeP.el.from.value = '';
    homeP.el.to = document.querySelector('form[name="load_deals_log_form"] input[name="to"]');
    homeP.el.to.value = '';
    homeP.el.uploadCsvBtn = document.querySelector('button[name="uploadCsv"]');
    homeP.el.csvFileInp = document.querySelector('input[name="csv"]');
    homeP.el.dropzone = document.querySelector('.dropzone');
    homeP.el.defaultUrl = document.querySelector('.default-url');

}

homeP.fn.initSelfInittedAjaxButtons = function () {
    let btns = Array.from(document.querySelectorAll('button[data-btn-self-init-ajax]'));
    btns.forEach(b => {
        b.addEventListener('click', (ev) => {
            let method = b.getAttribute('data-btn-self-init-ajax');
            ev.preventDefault();
            $.ajax({ url: method });
        });
    });
};

homeP.fn.initializeUploadCsvBtn = function () {
    homeP.el.uploadCsvBtn.addEventListener('click', ev => {
        ev.preventDefault();
        function uploadFromRemoteServer() {
            $.ajax({ url: 'upload_csv' });
            homeP.el.dDealLogsTable.ajax.reload(null, false);
            return;
        }
        if (homeP.el.csvFileInp.value.length > 2 && homeP.el.csvFileInp.files.length > 0) {
            let fileName = homeP.el.csvFileInp.files[0].name.split('.');
            let ext = fileName[fileName.length - 1];
            if (ext != 'csv') {
                uploadFromRemoteServer();
            } else {
                let f = new FormData();
                f.append('csv', homeP.el.csvFileInp.files[0]);

                $.ajax({
                    type: "POST",
                    url: "upload_csv",
                    completed: function (data) {
                        homeP.el.dDealLogsTable.ajax.reload(null, false);
                    },
                    async: true,
                    data: f,
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000
                });
            }
        } else {
            uploadFromRemoteServer();
        }
    });
    homeP.el.csvFileInp.addEventListener('click', () => {
        homeP.el.csvFileInp.value = '';
        homeP.el.csvFileInp.files.length = 0;
    })
};

homeP.fn.initializeDropzone = function () {
    window.addEventListener("dragover", function (e) {
        if (e instanceof Event) {
            e.preventDefault();
        }
    }, false);
    window.addEventListener("drop", function (e) {
        if (e instanceof Event) {
            e.preventDefault();
        }
    }, false)
    homeP.el.dropzone.addEventListener('dragover', function () {
        homeP.el.dropzone.classList.add('bg-secondary');
    });
    homeP.el.dropzone.addEventListener('dragleave', function () {
        homeP.el.dropzone.classList.remove('bg-secondary');
    });
    homeP.el.dropzone.addEventListener('drop', function (ev) {
        ev.preventDefault();
        homeP.el.dropzone.classList.remove('bg-secondary');
        homeP.el.csvFileInp.files = ev.dataTransfer.files;
    });
    homeP.el.defaultUrl.addEventListener('drop', function (ev) {
        ev.preventDefault();
        homeP.el.dropzone.classList.remove('bg-secondary');
        homeP.el.csvFileInp.files = ev.dataTransfer.files;
    });

};

$(document).ajaxComplete((event, xhr, settings) => {
    let res = xhr.responseJSON;
    if (!res) return;
    //toastr.clear();
    if (res.errors && res.errors.length > 0) {
        res.errors.forEach(e => toastr.error(e));
    }
    if (res.confirms && res.confirms.length > 0) {
        res.confirms.forEach(c => toastr.success(c));
    }
});

$(document).ajaxSend((event, xhr, settings) => {
    settings.url = getAjaxUrl() + settings.url;

});

$.ajaxSetup({
    type: "POST",
    dataType: "json",
    headers: {
        'Authorization': 'Bearer ' + document.querySelector('input[name="api_token"]').value,
    }
});

function getAjaxUrl() {
    let pn = location.pathname.split('/');
    pn = pn.slice(0, pn.length - 1).join('/');

    return location.origin + pn + '/api/';
}

homeP.fn.initReloadTableBtn = function () {
    homeP.el.reloadTableBtns.forEach(b => {
        b.addEventListener('click', (ev) => {
            if (ev instanceof Event) {
                ev.preventDefault();
            }
            homeP.el.dDealLogsTable.ajax.reload(null, false);
        });
    });
};


