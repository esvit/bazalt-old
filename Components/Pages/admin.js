'use strict';

define([
    'ng-editable-tree',
    'ckeditor/ckeditor',
    'ng-table',
    'uploader'
], function() {

    angular.module('Component.Pages.Admin', ['ngEditableTree', 'ngTable', 'bzUploader']).
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
            component: 'Pages',
            url: '#!/pages',
            title: 'Pages',
            icon: 'icon-file-alt'
        });
    })
    .directive("pagesCollection", function($document, $parse, $timeout, PagesService) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                PagesService.get({}, function(result) {
                    scope.loading.pages = false;
                    scope.pages = result.data;
                });
            }
        };
    })
    .factory('PagesService', function($resource) {
        return $resource('/rest.php/pages/', { 'id': '@id' });
    })
    /*
    .factory('AuthorsService', function($resource) {
            return $resource('/rest.php/news/authors/');
    })*/
    .factory('PagesCategoryService', function(ngNestedResource) {
        var PagesCategoryService = ngNestedResource('/rest.php/pages/categories/');

        return PagesCategoryService;
    })
    .controller('PagesCtrl', function($scope, $rootScope, $filter, $location, $routeParams, $q, PagesService, PagesCategoryService) {
        $scope.activateMenu('Pages'); // activate admin menu

        // for access from other controller
        $rootScope.toggleEdit = function() {
            $scope.edit_categories = !$scope.edit_categories;
        }

        $scope.loading = {
            category: true,
            pages: true
        };

        // get categories
        $scope.category = PagesCategoryService.getTree(function(res) {
            var parents = [];
            $scope.loading.category = false;

            // select active category
            $scope.activeCategory = PagesCategoryService.find(res, function(item) { return item.id == $routeParams.category_id; }, parents);

            // open all nodes to active category
            if ($scope.activeCategory) {
                angular.forEach(parents, function(node) { node.$expanded = true; });
            }
        });

        $scope.filterByCategory = function(category) {
            $scope.activeCategory = category;
            $scope.params = $scope.params || {};
            $scope.params.category_id = (category) ? category.id : null;
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
        $scope.update($routeParams);

        $scope.selected = function() {
            var selected = [];
            if (angular.isUndefined($scope.pages)) {
                return selected;
            }
            angular.forEach($scope.pages.data, function(item) {
                if (item.$selected) {
                    selected.push(item.id);
                }
            });
            return selected;
        }
        $scope.delete = function(ids) {
            PagesService.delete({ 'ids[]': ids }, {}, function(){
                $scope.update();
            });
        }
        $scope.togglePublished = function(page) {
            var service = new PagesService(page);
            $scope.$apply(function() {
                service.$save();
            });
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

        $rootScope.$watch('tr', function() {
            $rootScope.breadcrumbs = [
                {
                    'title' : $filter('translate')('Dashboard', 'Pages'),
                    'url': '#!/'
                },
                {
                    'title' : $filter('translate')('Pages', 'Pages'),
                    'url': '#!/pages'
                }
            ];
        });
        $rootScope.breadcrumbs = [
            {
                'title' : $filter('translate')('Dashboard', 'Pages'),
                'url': '#!/'
            },
            {
                'title' : $filter('translate')('Pages', 'Pages'),
                'url': '#!/pages'
            }
        ];
    })
    .controller('CategoriesCtrl', function($scope, $rootScope, $filter, $routeParams, PagesCategoryService) {
        $scope.activateMenu('Pages'); // activate admin menu

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
    .controller('PageCtrl', function($scope, $rootScope, $routeParams, $filter, $location, $timeout, PagesService, PagesCategoryService) {
        $scope.activateMenu('Pages'); // activate admin menu

        $scope.loading = true;
        var pageTitle = 'New page';
        if ($routeParams.id) {
            var pageTitle = 'Edit page';
            $scope.page = PagesService.get({ id: $routeParams.id }, function() {
                $scope.loading = false;
            });
        } else {
            $scope.page = new PagesService({
                title: {},
                body: {}
            });
            $scope.loading = false;
        }

        $scope.savePage = function(page) {
            $scope.loading = true;
            page.$save(function() {
                $scope.loading = false;
                $location.url('/pages');
            });
        }

        $scope.cancel = function(page) {
            $location.url('/pages');
        }

        $scope.addImage = function(page, file) {
            if (angular.isUndefined(!page.images)) {
                page.images = [];
            }
            $scope.$apply(function(){
                page.images.push(file);
            });
        }

        $scope.categories = [];

        // get categories
        PagesCategoryService.getTree(function(res) {
            var walk = function(items) {
                for (var i = 0; i < items.length; i++) {
                    $scope.categories.push({
                        id:    items[i].id,
                        title: $filter('language')(items[i].title)
                    });
                    walk(items[i].children);
                }
            }
            walk(res.children);
        });


        $rootScope.$watch('tr', function() {
            $rootScope.breadcrumbs = [
                {
                    'title' : $filter('translate')('Dashboard', 'Pages'),
                    'url': '#!/'
                },
                {
                    'title' : $filter('translate')('Pages', 'Pages'),
                    'url': '#!/pages'
                },
                {
                    'title' : $filter('translate')(pageTitle, 'Pages')
                }
            ];
        });
        $rootScope.breadcrumbs = [
            {
                'title' : $filter('translate')('Dashboard', 'Pages'),
                'url': '#!/'
            },
            {
                'title' : $filter('translate')('Pages', 'Pages'),
                'url': '#!/pages'
            },
            {
                'title' : $filter('translate')(pageTitle, 'Pages')
            }
        ];
    })

});