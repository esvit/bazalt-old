'use strict';

define([
    'ng-editable-tree'
], function() {

    angular.module('Component.Menu.Admin', ['ngEditableTree']).
        config(['$routeProvider', function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/menu', {
                    controller: 'MenusCtrl',
                    templateUrl: '/Components/Menu/views/admin/menu.html',
                    reloadOnSearch: false
                });
        }])
        .run(function(dashboard) {
            // add item to admin main menu
            dashboard.mainMenu.push({
                component: 'Menu',
                url: '#!/menu',
                title: 'Menu',
                icon: 'icon-reorder'
            });
        })
        .directive('focusMe', function($timeout, $parse) {
                return {
                    scope: false,
                    link: function(scope, element, attrs) {
                        var fn = $parse(attrs.focusMe);
                        scope.$watch(attrs.focusMe, function(value) {
                            if (value === true) {
                                $timeout(function() {
                                    element.select();
                                }, 0);
                                fn.assign(scope, false);
                            }
                        });
                    }
                };
            })
    .factory('MenuElementsService', function(ngNestedResource) {
        var MenuElementsService = ngNestedResource('/rest.php/menu/:id', { 'id': '@' }, {
            update: { method: 'POST' },
            getSettings: { method: 'POST', params: { 'action': 'getSettings' }, isArray: false }
        });
        return MenuElementsService;
    })
    .factory('MenuService', function($resource, MenuElementsService) {
        var MenuService = $resource('/rest.php/menu', {}, {
            create: { method: 'PUT' },
            getTypes: { method: 'GET', params: { 'action': 'getTypes' }, isArray: true }
        });
        MenuService.prototype.getElements = function(cb) {
            cb = cb || angular.noop;
            MenuElementsService.getTree({ 'id': this.id }, cb);
        };
        return MenuService;
    })
    .controller('MenusCtrl', function($scope, $rootScope, $filter, $location, $routeParams, $timeout, MenuService, MenuElementsService) {
        $scope.activateMenu('Menu'); // activate admin menu

        $scope.loading = {
            menus: false,
            elements: false
        };
        $scope.menuTypes = MenuService.getTypes();

        $scope.$on('$locationChangeSuccess', function(event) {
            $scope.selectMenu($routeParams.id);
        });

        $scope.selectMenu = function(menuId) {
            if (menuId) {
                angular.forEach($scope.menus, function(item) {
                    if (item.id == menuId) {
                        $scope.menu = item;
                    }
                });
            } else {
                $scope.menu = null;
            }
            if ($scope.menu) {
                $scope.loading.elements = true;
                $scope.menu.getElements(function(result) {
                    $scope.loading.elements = false;
                    $scope.menu = result;
                });

                $rootScope.breadcrumbs = [
                    {
                        'title' : 'Dashboard',
                        'url': '#!/'
                    },
                    {
                        'title' : 'Menu',
                        'url': '#!/menu'
                    },
                    {
                        'title' : $filter('language')($scope.menu.title)
                    }
                ];
            } else {
            
                $rootScope.breadcrumbs = [
                    {
                        'title' : 'Dashboard',
                        'url': '#!/'
                    },
                    {
                        'title' : 'Menu',
                        'url': '#!/menu'
                    }
                ];
            }
        };
        $scope.loading.menus = true;
        $scope.menus = MenuService.query(function() {
            $scope.selectMenu($routeParams.id);
            $scope.loading.menus = false;
        });

        $rootScope.breadcrumbs = [
            {
                'title' : 'Dashboard',
                'url': '#!/'
            },
            {
                'title' : 'Menu',
                'url': '#!/menu'
            }
        ];

        /**
         * Create new menu
         */
        $scope.createMenu = function (menu) {
            if ($scope.newMenu.$invalid) {
                return;
            }
            menu.busy = true;
            MenuService.create(menu, function(menu) {
                $scope.nMenu = { busy: false };
                menu.children = [];
                $scope.menus.push(new MenuElementsService(menu));
                $location.search({ id: menu.id });
            });
        };

        /**
         * Create new menu elements
         */
        $scope.addChild = function (child) {
            $scope.loading.elements = true;
            child.$insertItem(function(item) {
                $scope.loading.elements = false;
                item.focus = true;
                item.$settings = true;
            });
        };

        $scope.saveItem = function (item) {
            var menu = new MenuElementsService(item);
            menu.$save();
        };

        $scope.remove = function (child) {
            function walk(target) {
                var children = target.children,
                i;
                if (children) {
                    i = children.length;
                    while (i--) {
                        if (children[i] === child) {
                            return children.splice(i, 1);
                        } else {
                            walk(children[i])
                        }
                    }
                }
            }
            $scope.$apply(function() {
                walk($scope.menu);
            });
        }

        $scope.move = function(item, before, index) {
            $scope.loading.elements = true;
            item.$moveItem(before, index, function() {
                $scope.loading.elements = false;
            });
        };
        $scope.saveItem = function (item) {
            var menu = new MenuElementsService(item);
            menu.children = [];

            $scope.loading.elements = true;
            menu.$save(function(result) {
                $scope.loading.elements = false;
                //item = new MenuElementsService(result);
            });
        };
    })
    .controller('MenuSettingsCtrl', function($scope, $rootScope, $filter, $location, $routeParams, $timeout, MenuService, MenuElementsService, menuItem) {
        $scope.activateMenu('Menu'); // activate admin menu

        $scope.loading = false;
        $scope.saveItem = function (item) {
            console.info(item);
            var menu = new MenuElementsService(item);
            menu.children = [];

            $scope.loading = true;
            menu.$save(function(result) {
                $scope.loading = false;
                //item = new MenuElementsService(result);
            });
        };

        $scope.addDescription = function(item, language) {
            if (!item.description) {
                item.description = {};
            }
            item.description[language] = '';
        };

        $scope.changeMenuType = function(item) {
            $scope.loading = true;
            var menu = new MenuElementsService(item);
            menu.$getSettings({ 'menuType': item.menuType }, function(result) {
                $scope.loading = false;
                item.menuType = result.menuType;
                item.component_id = result.component_id;
                item.typeTitle = result.typeTitle;
                item.settings = result.settings;
            });
        };
    })

});