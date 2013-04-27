"use strict";

require.config({
    baseUrl: "/assets/components",
    paths: {
        'admin': '../../App/Admin/assets/js/app',
        'admin-login': '../../App/Admin/assets/js/loginApp',
        'elfinder': '../modules/elfinder/elfinder.min'
        /*Angular : 'angular/module',
        BazaltCMS : 'bazalt/module',
        UIBootstrap : 'ui-bootstrap/module',
        Bootstrap : 'bootstrap.min',
        bootstrap : 'bootstrap.min',
        'load-image' : 'bootstrap-image-gallery/load-image.min',
        "underscore": "underscore/underscore-min"*/
    },
    urlArgs: 'v=1.0',
    shim: {
        "jquery-ui": {
            exports: "$",
            deps: ['jquery']
        },
        "bazalt-cms": {
            deps: ['angular']
        },
        "jquery.ui.nestedSortable": {
            deps: [
                'jquery-ui'
            ]
        },
        "ng-editable-tree": {
            deps: [
                'jquery.ui.touch-punch',
                'jquery.ui.nestedSortable'
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