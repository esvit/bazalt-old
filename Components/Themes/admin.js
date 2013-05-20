'use strict';

define([
    'ace/ace',
    'ng-ace'
], function() {

    angular.module('Component.Themes.Admin', ['ace']).
        config(['$routeProvider', function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/themes', {
                    controller: 'FileCtrl',
                    templateUrl: '/Components/Themes/views/admin/list.html',
                    reloadOnSearch: false
                })
                .when('/themes/edit:id', {
                    controller: 'FileCtrl',
                    templateUrl: '/Components/News/views/admin/edit.html',
                    reloadOnSearch: false
                });
        }])
        .run(function(dashboard) {
            // add item to admin main menu
            dashboard.mainMenu.push({
                component: 'Themes',
                url: '#!/themes',
                title: 'Themes',
                icon: 'icon-reorder'
            });
        })
    .factory('ThemeService', function($resource) {
            return $resource('/rest.php/themes/:id/', { 'id': '@id' }, {
                updateOrder: { method: 'PUT', data: { 'orders': '@orders' }, isArray: true }
            });
    })
    .factory('FileService', function($resource) {
            return $resource('/rest.php/themes/:theme_id/file/:id', { 'id': '@', 'theme_id': '@' });
    })
    .factory('LayoutService', function($resource) {
            return $resource('/rest.php/themes/layout', {});
    })
    .controller('FileCtrl', function($scope, $routeParams, FileService, $location, LayoutService) {
        $scope.activateMenu('Themes'); // activate admin menu

        $scope.layouts = LayoutService.get();

        $scope.files = FileService.query({ 'theme_id': 1 }, function(files) {
            if (typeof $routeParams.name != 'undefined') {
                angular.forEach(files, function(item) {
                    if (item.name == $routeParams.name) {
                        $scope.openFile(item);
                    }
                });
            }
        });

        $scope.openFile = function(file) {
            if ($scope.selectedFile && $scope.selectedFile.name == file.name) {
                return;
            }
            $scope.selectedFile = file;
            $location.search({ 'name': file.name });
            $scope.file = FileService.get({ 'theme_id': 1, 'id': file.id }, function(file) {
                
            });
            return false;
        }
        $scope.saveFile = function(file) {
            file.$save({ 'theme_id': file.theme_id, 'id': file.id });
        }
    });

});