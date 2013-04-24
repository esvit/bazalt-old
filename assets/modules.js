"use strict";

require.config({
    baseUrl: "/assets/modules",
    paths: {
        Angular : 'angular/module',
        BazaltCMS : 'bazalt/module',
        UIBootstrap : 'ui-bootstrap/module',
        Bootstrap : 'bootstrap.min',
        bootstrap : 'bootstrap.min',
        'load-image' : 'bootstrap-image-gallery/load-image.min',
        "underscore": "underscore/underscore-min"
    },
    urlArgs: 'v=1.0',
    shim: {
        "jquery-ui": {
            exports: "$",
            deps: ['jquery']
        },
        "underscore": {
            exports: "_"
        },
        "bootstrap": ['jquery']
    }
});