'use strict';

define([
    'ng-editable-tree',
    'ckeditor/ckeditor',
    'ng-table',
    'uploader'
], function() {

    angular.module('Component.Shop.Admin', ['ngEditableTree', 'ngTable', 'bzUploader']).
        config(function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/shop', {
                    controller: 'ProductsCtrl',
                    templateUrl: '/Components/Shop/views/admin/list.html',
                    reloadOnSearch: false
                })
                .when('/shop/edit:id', {
                    controller: 'ProductCtrl',
                    templateUrl: '/Components/Shop/views/admin/edit.html'
                })
                .when('/shop/create', {
                    controller: 'ProductCtrl',
                    templateUrl: '/Components/Shop/views/admin/edit.html'
                });
        })
    .run(function(dashboard) {
        // add item to admin main menu
        dashboard.mainMenu.push({
            component: 'Shop',
            url: '#!/shop',
            title: 'Shop',
            icon: 'icon-shopping-cart'
        });
    })
    .directive("productsCollection", function($document, $parse, $timeout, ProductsService) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                ProductsService.get({}, function(result) {
                    scope.loading.products = false;
                    scope.products = result.data;
                });
            }
        };
    })
    .factory('ProductsService', function($resource) {
        return $resource('/rest.php/shop/', { 'id': '@id' });
    })
    /*
    .factory('AuthorsService', function($resource) {
            return $resource('/rest.php/news/authors/');
    })*/
    .factory('CategoryService', function(ngNestedResource) {
        var CategoryService = ngNestedResource('/rest.php/shop/categories/');

        return CategoryService;
    })
    .controller('ProductsCtrl', function($scope, $rootScope, $filter, $location, $routeParams, $q, ProductsService, CategoryService) {
        $scope.activateMenu('Shop'); // activate admin menu

        // for access from other controller
        $rootScope.toggleEdit = function() {
            $scope.edit_categories = !$scope.edit_categories;
        }

        $scope.loading = {
            category: true,
            products: true
        };

        // get categories
        $scope.category = CategoryService.getTree(function(res) {
            var parents = [];
            $scope.loading.category = false;

            // select active category
            $scope.activeCategory = CategoryService.find(res, function(item) { return item.id == $routeParams.category_id; }, parents);

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
            $scope.loading.products = true;
            $location.search(params);
            ProductsService.get(params, function(result) {
                $scope.loading.products = false;
                $scope.products = result;
            });
        }
        $scope.update($routeParams);

        $scope.selected = function() {
            var selected = [];
            if (angular.isUndefined($scope.products)) {
                return selected;
            }
            angular.forEach($scope.products.data, function(item) {
                if (item.$selected) {
                    selected.push(item.id);
                }
            });
            return selected;
        }
        $scope.delete = function(ids) {
            ProductsService.delete({ 'ids[]': ids }, {}, function(){
                $scope.update();
            });
        }
        $scope.togglePublished = function(page) {
            var service = new ProductsService(page);
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
                    'title' : $filter('translate')('Dashboard', 'Shop'),
                    'url': '#!/'
                },
                {
                    'title' : $filter('translate')('Shop', 'Shop'),
                    'url': '#!/shop'
                }
            ];
        });
        $rootScope.breadcrumbs = [
            {
                'title' : $filter('translate')('Dashboard', 'Shop'),
                'url': '#!/'
            },
            {
                'title' : $filter('translate')('Shop', 'Shop'),
                'url': '#!/shop'
            }
        ];
    })
    .controller('ShopCategoriesCtrl', function($scope, $rootScope, $filter, $routeParams, CategoryService) {
        $scope.activateMenu('Shop'); // activate admin menu

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
    .controller('ProductCtrl', function($scope, $rootScope, $routeParams, $filter, $location, $timeout, ProductsService, CategoryService) {
        $scope.activateMenu('Shop'); // activate admin menu

        $scope.loading = true;
        var pageTitle = 'New product';
        if ($routeParams.id) {
            var pageTitle = 'Edit product';
            $scope.page = ProductsService.get({ id: $routeParams.id }, function() {
                $scope.loading = false;
            });
        } else {
            $scope.page = new ProductsService({
                title: {},
                description: {},
                images: [],
                category_id: $routeParams.category_id
            });
            $scope.loading = false;
        }

        $scope.savePage = function(page) {
            $scope.loading = true;
            page.$save(function() {
                $scope.loading = false;
                $location.url('/shop?category_id=' + $scope.page.category_id);
            });
        }

        $scope.cancel = function(page) {
            $location.url('/shop?category_id=' + $scope.page.category_id);
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
        CategoryService.getTree(function(res) {
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
                    'title' : $filter('translate')('Dashboard', 'Shop'),
                    'url': '#!/'
                },
                {
                    'title' : $filter('translate')('Shop', 'Shop'),
                    'url': '#!/shop'
                },
                {
                    'title' : $filter('translate')(pageTitle, 'Shop')
                }
            ];
        });
        $rootScope.breadcrumbs = [
            {
                'title' : $filter('translate')('Dashboard', 'Shop'),
                'url': '#!/'
            },
            {
                'title' : $filter('translate')('Shop', 'Shop'),
                'url': '#!/pages'
            },
            {
                'title' : $filter('translate')(pageTitle, 'Shop')
            }
        ];
    })

});