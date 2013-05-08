'use strict';

define([
    'ng-finder',
    '/Components/Files/assets/js/info.js'
], function() {

    angular.module('Component.Files.Admin', ['ngFinder'])
    .config(function($routeProvider, $locationProvider) {
        $routeProvider
            .when('/files', {
                controller: 'FilesCtrl',
                templateUrl: '/Components/Files/views/admin/files.html',
                reloadOnSearch: false
            });

    })
    .run(function(dashboard) {
        dashboard.mainMenu.push({
            component: 'Files',
            url: '#!/files',
            title: 'Files',
            icon: 'icon-file'
        });
    })
    .controller('FilesCtrl', function($scope, $rootScope, $filter) {
        $scope.activateMenu('Files'); // activate admin menu

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