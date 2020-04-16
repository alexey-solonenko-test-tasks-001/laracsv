window.dealsLogViewer = {};
dealsLogViewer.fn = {};
dealsLogViewer.el = {};

$(function () {
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


dealsLogViewer.fn.init = function () {
    dealsLogViewer.el.form = document.querySelector('form[name="load_deals_log_form"]');
    dealsLogViewer.el.reloadTableBtns = Array.from(document.querySelectorAll('.reload_table.btn'));
    dealsLogViewer.el.dealLogsTable = document.querySelector('.deals_log_table');
    dealsLogViewer.el.jDealLogsTable = $(dealsLogViewer.el.dealLogsTable);
    dealsLogViewer.el.logsForm = document.querySelector('form[name="load_deals_log_form"]');
    dealsLogViewer.el.from = document.querySelector('form[name="load_deals_log_form"] input[name="from"]');
    dealsLogViewer.el.from.value = '';
    dealsLogViewer.el.to = document.querySelector('form[name="load_deals_log_form"] input[name="to"]');
    dealsLogViewer.el.to.value = '';
    dealsLogViewer.el.copyLinkBtn = document.querySelector('form[name="load_deals_log_form"] button.copy_search_link');
    dealsLogViewer.el.handyOrdering = document.querySelector('form[name="load_deals_log_form"] button.handy_ordering');
};

dealsLogViewer.fn.initReloadTableBtn = function () {
    dealsLogViewer.el.reloadTableBtns.forEach(b => {
        b.addEventListener('click', (ev) => {
            if (ev instanceof Event) {
                ev.preventDefault();
            }
            dealsLogViewer.el.dDealLogsTable.ajax.reload(null, false);
        });
    });
};


dealsLogViewer.fn.initializeDealsLogTable = function () {
    /** @type {DataTables} */
    let initConfig = {
        "processing": true,
        "serverSide": true,
        'searching': false,
        ajax: {
            data: dealsLogViewer.fn.dealLogsData,
            url: 'deals_log',
            type: "GET",
            dataType: "json",
            complete: function () {
                let u = new URL(this.url);
                window.lastDealLogsUrl = location.origin + location.pathname + u.search;
            }
        },
        columns: [{
            data: 'client',
            defaultContent: '-',
        },
        {
            data: 'deal',
            defaultContent: '-',
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
            initConfig.displayStart = parseInt(start);
        }
        dealsLogViewer.el.jDealLogsTable.on('preXhr.dt', function () {
            document.querySelector('#nav_deals_log').click();
        });
    }

    delete window.searchDealLogsParams;

    /**
     * @function
     * @param {Object} ev
     * @param {DataTables.Settings} settings
     * @param {string} json
     * @param {jQuery.Xhr} xhr
     */
    dealsLogViewer.el.jDealLogsTable.on('xhr.dt', function (ev, settings, json, xhr) {
        let api = new $.fn.dataTable.Api(settings);
        api.table().node().tFoot.rows[0].cells[3].textContent = json.resPayload.totalAccepted;
        api.table().node().tFoot.rows[0].cells[4].textContent = json.resPayload.totalRefused;
        let visibleRows = Array.from(dealsLogViewer.el.dDealLogsTable.rows({page:'curren'})[0]);
        if(visibleRows.length === 0 && api.page() > 1){
            api.ajax.reload();
        };
    });

    dealsLogViewer.el.dDealLogsTable = dealsLogViewer.el.jDealLogsTable.DataTable(initConfig);

    dealsLogViewer.el.logsForm.addEventListener('keydown', ev => {
        if (ev.key == 'Enter') {
            ev.preventDefault;
            dealsLogViewer.el.dDealLogsTable.ajax.reload(null, false);
        }
    });

    dealsLogViewer.el.handyOrdering.addEventListener('click', function (ev) {
        if (ev instanceof Event) {
            ev.preventDefault();
        }
        dealsLogViewer.el.dDealLogsTable.order([0, 'asc'], [1, 'asc'], [2, 'desc'], [3, 'desc']).ajax.reload(null, false);
    });
};

dealsLogViewer.fn.dealLogsData = function (req) {
    let f = new FormData(dealsLogViewer.el.logsForm);
    for (var pair of f.entries()) {
        req[pair[0]] = pair[1];
    }
};

/**
 * @function dealsLogViewer.fn.initDealLogsFromLandingParams 
 */
dealsLogViewer.fn.initDealLogsFromLandingParams = function () {
    let {
        by_client,
        by_deal,
        deal,
        group_by,
        client,
        from,
        to
    } = window.searchDealLogsParams;
    [
        {
            by_client
        },
        {
            by_deal
        },
        {
            group_by
        },
        {
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
            let val = Object.values(param)[0];

            if (el.type.toLowerCase() === 'checkbox') {
                if (val) {
                    el.checked = true;
                } else {
                    el.removeAttribute('checked');
                }
            } else {
                el.value = val;
            }
        });
}

/**
 *  @function
 */
dealsLogViewer.fn.initCopyLinkBtn = function () {
    dealsLogViewer.el.copyLinkBtn.addEventListener('click', async function (ev) {
        if (ev instanceof Event) {
            ev.preventDefault();
        }
        let success = false;
        if (document.queryCommandSupported('copy') && document.queryCommandEnabled('copy')) {
            let ta = document.createElement('textarea');
            ta.classList.add('d-none');
            document.body.appendChild(ta);
            ta.textContent = window.lastDealLogsUrl;
            ta.focus();
            ta.select();
            success = document.execCommand('copy');
            if (success) {
                toastr.success('Link copied to clipboard');
            }
        }
        if (!success) {
            let result = await navigator.permissions.query({
                name: "clipboard-write"
            });
            if (result.state == "granted" || result.state == "prompt") {
                try {
                    let res = await navigator.clipboard.writeText(window.lastDealLogsUrl);
                    success = true;
                } catch (e) { }
            }
        }
        if (!success) {
            window.history.pushState({}, document.title, window.lastDealLogsUrl);
            toastr.warning('We are sorry, your browser does not support automatic copying. Please, copy the linke from the browser address bar.')
        }
    });
};