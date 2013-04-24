'use strict';

define([
    'angular-nestedtree/module',
    'angular-ui/angular-ui',
    'jquery-ui/jquery-ui.min',
    '/App/Admin/assets/js/bootstrapSwitch.js',
    'angular-ckeditor/module',
    'bootstrap-tree/bootstrap-tree'
], function() {

    angular.module('Component.Pages.Admin', ['ui']).
        config(function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/pages', {
                    controller: 'PagesCtrl',
                    templateUrl: '/Components/Pages/views/admin/list.html',
                    reloadOnSearch: false
                })
                .when('/pages/edit:id', {
                    controller: 'NewsArticleCtrl',
                    templateUrl: '/Components/News/views/admin/edit.html'
                })
                .when('/pages/create', {
                    controller: 'NewsArticleCtrl',
                    templateUrl: '/Components/News/views/admin/edit.html'
                });
        })
    .run(function(dashboard) {
        dashboard.mainMenu.push({
            url: '#!/pages',
            title: 'Pages',
            icon: 'ico-file-alt'
        });
    })
    .factory('NewsService', function($resource) {
            return $resource('/rest.php/news/:id/', { 'id': '@id' }, {
                updateOrder: { method: 'PUT', data: { 'orders': '@orders' }, isArray: true }
            });
    })
    .factory('LanguageService', function($resource) {
            return $resource('/rest.php/app/language');
    })
    .factory('AuthorsService', function($resource) {
            return $resource('/rest.php/news/authors/');
    })
    .factory('CategoriesService', function($resource) {
            return $resource('/rest.php/news/categories/');
    })
    .controller('PagesCtrl', function($scope, $location, $routeParams, $q, NewsService, AuthorsService, CategoriesService) {
        return;
        $scope.activeCategory = null;
        $scope.params = {};
        $scope.filterByCategory = function(category) {
            $scope.activeCategory = category;
            $scope.params.category_id = category.id;
            $scope.update($scope.params);
        }
        $scope.news = NewsService.get($routeParams);
        $scope.category = CategoriesService.get();

        $scope.update = function(params) {
            $scope.params = params;
            $scope.loading = true;
            $location.search(params);
            NewsService.get(params, function(result) {
                $scope.loading = false;
                $scope.news = result;
            });
        }
        $scope.checked = function() {
            var checked = [];
            angular.forEach($scope.news, function(item) {
                if (item.checked) {
                    checked.push(item.id);
                }
            });
            return checked;
        }
        $scope.delete = function() {
            console.info('delete');
        }

        $scope.users = function() {
            var q = $q.defer();
            AuthorsService.query(function(authors) {
                var result = [];
                angular.forEach(authors, function(author) {
                    result.push({
                        'id': author.id,
                        'title': author.login
                    });
                });
                q.resolve(result);
            });
            return q.promise;
        }
    })
    .controller('NewsArticleCtrl', function($scope, $routeParams, NewsService, LanguageService) {
        $scope.languages = LanguageService.query();
        $scope.article = NewsService.get({ id: $routeParams.id });
    });

});