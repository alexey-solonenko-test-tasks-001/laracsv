/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/homepage.js":
/*!**********************************!*\
  !*** ./resources/js/homepage.js ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports) {

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
};

homeP.fn.initializeUploadCsvBtn = function () {
  homeP.el.uploadCsvBtn.addEventListener('click', function (ev) {
    ev.preventDefault();

    function uploadFromRemoteServer() {
      $.ajax({
        url: 'upload_csv'
      });
      return;
    }

    if (homeP.el.csvFileInp.value.length > 2 && homeP.el.csvFileInp.files.length > 0) {
      var fileName = homeP.el.csvFileInp.files[0].name.split('.');
      var ext = fileName[fileName.length - 1];

      if (ext != 'csv') {
        uploadFromRemoteServer();
      } else {
        var f = new FormData();
        f.append('csv', homeP.el.csvFileInp.files[0]);
        $.ajax({
          type: "POST",
          url: "upload_csv",
          completed: function completed(data) {
            $.fn.dataTable.tables()[0].ajax.reload(null, false);
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
  homeP.el.csvFileInp.addEventListener('click', function () {
    homeP.el.csvFileInp.value = '';
    homeP.el.csvFileInp.files.length = 0;
  });
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
  }, false);
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

/***/ }),

/***/ 1:
/*!****************************************!*\
  !*** multi ./resources/js/homepage.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\code\laracsv\resources\js\homepage.js */"./resources/js/homepage.js");


/***/ })

/******/ });