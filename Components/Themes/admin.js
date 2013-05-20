'use strict';

define([
    'ace/ace',
    'ng-ace',
    'colorpicker'
], function() {

    angular.module('Component.Themes.Admin', ['ace']).
        config(['$routeProvider', function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/themes', {
                    controller: 'ThemesCtrl',
                    templateUrl: '/Components/Themes/views/admin/list.html',
                    reloadOnSearch: false
                })
                .when('/themes/:theme', {
                    controller: 'FileCtrl',
                    templateUrl: '/Components/Themes/views/admin/theme.html',
                    reloadOnSearch: false
                });
        }])
        .run(function(dashboard) {
            // add item to admin main menu
            dashboard.mainMenu.push({
                component: 'Themes',
                url: '#!/themes',
                title: 'Themes',
                icon: 'icon-leaf'
            });
        })
    .factory('ThemeService', function($resource) {
            return $resource('/rest.php/themes/:id/', { 'id': '@id' }, {
                updateOrder: { method: 'PUT', data: { 'orders': '@orders' }, isArray: true }
            });
    })
    .factory('FileService', function($resource) {
            return $resource('/rest.php/themes/:theme_id/file', { 'theme_id': '@' });
    })
    .factory('LayoutService', function($resource) {
            return $resource('/rest.php/themes/layout', {});
    })
    .controller('ThemesCtrl', function($scope, ThemeService) {
        $scope.activateMenu('Themes'); // activate admin menu

        $scope.themes = ThemeService.query();
    })
    .controller('FileCtrl', function($scope, $routeParams, ThemeService, FileService, $location, LayoutService) {
        $scope.activateMenu('Themes'); // activate admin menu

        $scope.layouts = LayoutService.get();

        $scope.files = FileService.query({ 'theme_id': 1 }, function(files) {
            if (typeof $routeParams.file != 'undefined') {
                angular.forEach(files, function(item) {
                    if (item.file == $routeParams.file) {
                        $scope.openFile(item);
                    }
                });
            } else {
                $scope.settings_file = '/themes/default/settings.html';
                $scope.showSettings();
            }
        });

        $scope.openFile = function(file) {
            if ($scope.file && $scope.file.file == file.file) {
                $scope.settings_file = '/themes/default/settings.html';
                return;
            }
            $scope.settings_file = null;
            $scope.file = file;
            $location.search({ 'file': file.file });
            FileService.get({ 'theme_id': 'default', 'file': file.file, 'action': 'loadFile'   }, function(file) {
                $scope.file.content = file.content;
                $scope.file.contentType = file.contentType;
            });
            return false;
        }
        $scope.saveFile = function(file) {
            file.$save({ 'theme_id': file.theme_id });
        }
        $scope.saveSettings = function() {
            $scope.theme.$save();
        }
        $scope.showSettings = function(file) {
            $location.search({});
            $scope.file = null;
            ThemeService.query(function(data) {
                $scope.theme = data[0];
            });
            
            $scope.settings_file = '/themes/default/settings.html';
        }
    });

});