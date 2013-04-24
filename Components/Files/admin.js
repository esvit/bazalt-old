'use strict';

define([
    'elfinder/elfinder.min',
    '/Components/Files/assets/js/info.js'
], function() {

    angular.module('Component.Files.Admin', []).
        config(function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/files', {
                    controller: 'FilesCtrl',
                    templateUrl: '/Components/Files/views/admin/files.html',
                    reloadOnSearch: false
                });

        })
    .directive('elFinder', function() {
        return {
            restrict: 'C',
            scope: {
                'file'      : '=',
                'onSelect'  : '&select'
            },
            controller: function($scope) {
                $scope.selectFile = function(url) {
                    var aFieldName = $scope.$parent.fieldName, 
                        aWin = $scope.$parent.window;

                    aWin.document.forms[0].elements[aFieldName].value = url;
                    $('#elfinder').hide();
                }
            },
            link: function(scope, element, attrs) {
                var options = {
                    // lang: 'ru',             // language (OPTIONAL)
                    rememberLastDir : true,
                    uiOptions: {
                        toolbar : [
                            //['back', 'forward'],
                            // ['reload'],
                            // ['home', 'up'],
                            ['mkdir', 'mkfile', 'upload'],
                            ['open', 'download', /*'quicklook', 'getfile'*/],
                            ['info'],
                            ['copy', 'cut', 'paste'],
                            ['rename','rm'],
                            //[/*'duplicate',*/  /*'edit', 'resize'*/],
                            //['extract', 'archive'],
                            //['search'],
                            ['view'],
                            ['help']
                        ]
                    },
                    contextmenu : {
                        // navbarfolder menu
                        navbar : ['open', '|', 'copy', 'cut', 'paste', '|', 'rm', '|', 'info'],

                        // current directory menu
                        cwd    : ['reload', 'back', '|', 'upload', 'mkdir', 'mkfile', 'paste', '|', 'info'],

                        // current directory file menu
                        files  : [
                            'open', /*'quicklook',*/ 'download', '|', 'copy', 'cut', 'paste', '|',
                            'rm', 'edit', 'rename', '|', 'info'
                        ]
                    },
                    url : '/elfinder'
                };
                if (attrs.select) {
                    options.contextmenu.files.unshift('getfile');
                    options.getFileCallback = function(url) {
                        scope.selectFile(url);
                    }
                }
                var elFinder = $(element).elfinder(options).elfinder('instance');

                $('.elfinder-toolbar', element).addClass('btn-toolbar');
                $('.elfinder-buttonset', element).addClass('btn-group');
                $('.elfinder-button', element).addClass('btn');

                $('.elfinder-button-icon-back', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-arrow-left');
                $('.elfinder-button-icon-forward', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-arrow-right');

                $('.elfinder-button-icon-mkdir', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-plus-sign');
                $('.elfinder-button-icon-mkfile', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-file');
                $('.elfinder-button-icon-upload', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-upload');

                $('.elfinder-button-icon-open', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-folder-open');
                $('.elfinder-button-icon-download', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-download-alt');
                $('.elfinder-button-icon-getfile', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-download');

                $('.elfinder-button-icon-info', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-info-sign');

                $('.elfinder-button-icon-quicklook', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-eye-open');

                $('.elfinder-button-icon-copy', element).removeClass('elfinder-button-icon').addClass('ico-cut');
                $('.elfinder-button-icon-cut', element).removeClass('elfinder-button-icon').addClass('ico-copy');
                $('.elfinder-button-icon-paste', element).removeClass('elfinder-button-icon').addClass('ico-paste');
                
                $('.elfinder-button-icon-rm', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-trash');

                $('.elfinder-button-icon-duplicate', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-eye-open');
                $('.elfinder-button-icon-rename', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-pencil');
                $('.elfinder-button-icon-edit', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-pencil');
                $('.elfinder-button-icon-resize', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-fullscreen');

                $('.elfinder-button-icon-extract', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-eye-open');
                $('.elfinder-button-icon-archive', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-eye-open');

                $('.elfinder-button-icon-view', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-th');
                $('.elfinder-button-icon-sort', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-sort');
                
                $('.elfinder-button-icon-help', element).removeClass('elfinder-button-icon').addClass('glyphicon glyphicon-question-sign');
            }
        };
    })
    .run(['$rootScope', '$compile', 'ui.config', '$filter', function($rootScope, $compile, uiConfig, $filter) {
        if (uiConfig.tinymce) {
            var scope = $rootScope.$new();
            uiConfig.tinymce.file_browser_callback = function(field_name, url, type, win) {
                scope.fieldName = field_name;
                scope.window = win;
                scope.close = function() {
                    $('#elfinder').hide();
                }

                if($('#elfinder').length == 0) {
                    var html = '<div id="elfinder" class="modal fade in" style="z-index: 400001; display: block"> \
                                    <div class="modal-dialog"> \
                                        <div class="modal-content"> \
                                            <div class="modal-header"> \
                                                <button type="button" class="close" ng-click="close()" data-dismiss="modal" aria-hidden="true">×</button> \
                                                ' + $filter('tr')('Select file', 'Files') + ' \
                                            </div> \
                                            <div class="modal-body el-finder" select="selectFile()" ></div> \
                                        </div> \
                                    </div> \
                                </div>';
                    
                    $('body').append(html);
                    $compile($('#elfinder'))(scope);
                } else {
                    $('#elfinder').show();
                }
            }
        }
    }])
    .controller('FilesCtrl', function($scope, $rootScope, $filter) {
        $rootScope.$watch('tr', function() {
            $rootScope.breadcrumbs = [
                {
                    'title' : $filter('translate')('Dashboard', 'Files'),
                    'url': '#!/'
                },
                {
                    'title' : $filter('translate')('Files', 'Files'),
                    'url': '#!/files'
                }
            ];
        });
        $rootScope.breadcrumbs = [
            {
                'title' : $filter('translate')('Dashboard', 'Files'),
                'url': '#!/'
            },
            {
                'title' : $filter('translate')('Files', 'Files'),
                'url': '#!/files'
            }
        ];
    });

});