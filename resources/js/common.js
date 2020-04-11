
window.common = {};
common.fn = {};
common.el = {};
toastr.options.timeOut = 15000;
toastr.options.extendedTimeOut = 15000;


$(function () {
    /**
     * Do use function to keep the global namespace clean.
     */
    (function () {

        $(document).ajaxSend((event, xhr, settings) => {
            settings.url = getAjaxUrl() + settings.url;

        });

        function getAjaxUrl() {
            let pn = location.pathname.split('/');
            pn = pn.slice(0, pn.length - 1).join('/');

            return location.origin + pn + '/api/';
        }


        $.ajaxSetup({
            type: "POST",
            dataType: "json",
            headers: {
                'Authorization': 'Bearer ' + document.querySelector('input[name="api_token"]').value,
            }
        });

        /**
         * @function
         * @param {JQuery.Event} event
         * @param {JQuery.jqXHR} xhr
         * @param {Object} settings
         */
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
            if (res.logs && res.logs.length > 0) {
                common.fn.populateLogsContainers(res.logs);
            }
        });



        function initializeSingleMessageTemplate(el) {
            el.classList.add('initialization-completed');
            console.log(el);
            let btn = el.querySelector('button');
            if (!btn) {
                return;
            }
            btn.addEventListener('click', (function (el) {
                el.remove();
            }).bind(null, el));
        }
        /**
         * 
         * @param {MutationRecord[]} mutationsList 
         * @param {MutationObserver} observer 
         */
        const callback = function (mutationsList, observer) {
            for (let mutation of mutationsList) {
                if (mutation.type !== 'childList' || mutation.addedNodes.length === 0) {
                    continue;
                }
                /** @type {HTMLElement[]} */
                let nodes = Array.from(mutation.addedNodes);
                for (let nd of nodes) {
                    if (!nd.classList) {
                        continue;
                    }
                    if (nd.classList.contains('its-a-template') || nd.classList.contains('initialization_completed')) {
                        continue;
                    }
                    if (nd.classList.contains('single-log-message')) {
                        initializeSingleMessageTemplate(nd);
                    }
                }
            }
        };
        /* LAUNCH */
        const observer = new MutationObserver(callback);
        observer.observe(document, { childList: true, subtree: true });
        common.fn.initSelfInittedAjaxButtons();
    })();


    let logContainers = Array.from(document.querySelectorAll('.messages-log-container'));
    logContainers.forEach(c => {
        c.querySelector('.card-header button').addEventListener('click', (function (c) {
            c.querySelector('.card-body').textContent = '';
        }).bind(null, c));

    });

});

/**
* @function
* @param {Array<Object>} logs 
*/
common.fn.populateLogsContainers = function (logs) {
    let logContainers = Array.from(document.querySelectorAll('.messages-log-container'));
    let tmpl = document.querySelector('.single-log-message.its-a-template');
    for (const log of logs) {
        /** @type {HTMLDivElement} */
        let tmplCopy = tmpl.cloneNode(true);
        tmplCopy.classList.remove('its-a-template');
        let body = tmplCopy.querySelector('.card-body');
        let classes = ['bg-light', 'bg-success', 'bg-warning', 'bg-danger'];
        let messages = [log.infos, log.confirms, log.warnings, log.errors];
        messages.forEach((messagesOfOnetype, idx) => {
            if (!messagesOfOnetype || messagesOfOnetype.length === 0) {
                return;
            }
            for (let msg of messagesOfOnetype) {
                let p = document.createElement('p');
                p.textContent = msg;
                p.classList.add(classes[idx], 'rounded', 'p-2');
                body.appendChild(p);
            }
        });

        let timeSpan = document.createElement('span');
        timeSpan.textContent = log.time;
        tmplCopy.querySelector('.card-header').children[0].appendChild(timeSpan);
        logContainers.forEach(c => {
            window.setTimeout((function (parent, el) {
                parent.querySelector('.card-body').appendChild(el);
            }).bind(null, c, tmplCopy.cloneNode(true)), 0);
        });
    }

};

/**
 * @function
 */
common.fn.initSelfInittedAjaxButtons = function () {
    let btns = Array.from(document.querySelectorAll('button[data-btn-self-init-ajax]'));
    btns.forEach(b => {
        b.addEventListener('click', (ev) => {
            let method = b.getAttribute('data-btn-self-init-ajax');
            ev.preventDefault();
            $.ajax({ url: method });
        });
    });
};