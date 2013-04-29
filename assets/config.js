"use strict";

require.config({
    baseUrl: "/assets/components",
    paths: {
        //'site': '../../App/Site/assets/js/app',

        'admin': '../../App/Admin/assets/js/app',
        'admin-login': '../../App/Admin/assets/js/loginApp',

        'elfinder': '../modules/elfinder/elfinder.min'
    },
    urlArgs: 'v=1.0',
    shim: {
        "site": {
            deps: ['angular', 'bazalt-cms']
        },
        "jquery-ui": {
            exports: "$",
            deps: ['jquery']
        },
        "uploader": {
            deps: ['jquery-ui']
        },
        "bazalt-cms": {
            deps: ['angular']
        },
        "ng-editable-tree": {
            deps: [
                'jquery-ui',
                'jquery.ui.touch-punch'
            ]
        },
        "ng-finder": {
            deps: ['jquery-ui','elfinder']
        },
        "underscore": {
            exports: "_"
        },
        "bootstrap": ['jquery','angular']
    }
});