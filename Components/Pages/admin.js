'use strict';

define([
    'ng-editable-tree',
    'ckeditor/ckeditor',
    'ng-table'
], function() {

    angular.module('Component.Pages.Admin', ['ngEditableTree', 'ngTable']).
        config(function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/pages', {
                    controller: 'PagesCtrl',
                    templateUrl: '/Components/Pages/views/admin/list.html',
                    reloadOnSearch: false
                })
                .when('/pages/edit:id', {
                    controller: 'PageCtrl',
                    templateUrl: '/Components/Pages/views/admin/edit.html'
                })
                .when('/pages/create', {
                    controller: 'PageCtrl',
                    templateUrl: '/Components/Pages/views/admin/edit.html'
                });
        })
    .run(function(dashboard) {
        // add item to admin main menu
        dashboard.mainMenu.push({
            url: '#!/pages',
            title: 'Pages',
            icon: 'icon-file-alt'
        });
    })
    .factory('PagesService', function($resource) {
        return $resource('/rest.php/pages/', { 'id': '@id' });
    })
    /*
    .factory('AuthorsService', function($resource) {
            return $resource('/rest.php/news/authors/');
    })*/
    .factory('CategoryService', function(ngNestedResource) {
        var CategoryService = ngNestedResource('/rest.php/pages/categories/');

        return CategoryService;
    })
    .controller('PagesCtrl', function($scope, $location, $routeParams, $q, PagesService, CategoryService) {
        $scope.loading = {
            category: true,
            pages: true
        };
        $scope.category = CategoryService.getTree(function() {
            $scope.loading.category = false;
        });
        $scope.activeCategory = null;
        $scope.params = {};
        $scope.filterByCategory = function(category) {
            $scope.activeCategory = category;
            $scope.params.category_id = category.id;
            $scope.update($scope.params);
        }

        $scope.update = function(params) {
            $scope.params = params;
            $scope.loading.pages = true;
            $location.search(params);
            PagesService.get(params, function(result) {
                $scope.loading.pages = false;
                $scope.pages = result;
            });
        }
        $scope.update();

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

        /*$scope.users = function() {
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
        }*/
    })
    .controller('CategoriesCtrl', function($scope, $routeParams, CategoryService) {
        $scope.addCategory = function (child) {
            child.$insertItem(function(item) {
                item.focus = true;
                item.$settings = true;
            });
        };

        $scope.move = function(item, before, index) {
            item.$moveItem(before, index);
        };
    })
    .controller('PageCtrl', function($scope, $routeParams, $location, $timeout, PagesService) {
        if ($routeParams.id) {
            $scope.page = PagesService.get({ id: $routeParams.id });
        } else {
            $scope.page = new PagesService({
                title: {},
                body: {}
            });
        }

        $scope.savePage = function(page) {
            page.$save(function() {
                $timeout(function() {
                $location.url('/pages')
                }, 10)
            });
        }

        $scope.cancel = function(page) {
            $location.url('/pages');
        }
    })

});