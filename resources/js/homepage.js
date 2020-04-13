window.homeP = {};
homeP.el = {};
homeP.fn = {};


$(function () {
    homeP.fn.init();
    homeP.fn.initializeUploadCsvBtn();
    homeP.fn.initializeDropzone();
});


homeP.fn.init = function () {
    homeP.el.uploadCsvBtn = document.querySelector('button[name="uploadCsv"]');
    homeP.el.csvFileInp = document.querySelector('input[name="csv"]');
    homeP.el.dropzone = document.querySelector('.dropzone');
    homeP.el.defaultUrl = document.querySelector('.default-url');
}

homeP.fn.initializeUploadCsvBtn = function () {
    homeP.el.uploadCsvBtn.addEventListener('click', ev => {
        ev.preventDefault();
        function uploadFromRemoteServer() {
            $.ajax({ url: 'upload_csv' });
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



