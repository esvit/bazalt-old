'use strict';

define([], function() {

    angular.module('Component.Seo', [])
    .run(['$rootScope', '$compile', function($rootScope, $compile) {
        $rootScope.$on('adminPanelInit', function(e, panelScope) {
            panelScope.menu.push({
                'title': 'SEO',
                'action': function() {
                    $rootScope.showSeo = !$rootScope.showSeo;
                }
            });
        });
        $('body').append($compile('<seo-panel />')($rootScope));
    }])
    .factory('SeoPageService', function($resource) {
        return $resource('/rest.php/seo/pages/:id', { 'id': '@' });
    })
    .factory('SeoRouteService', function($resource) {
        return $resource('/rest.php/seo/routes/', { 'route': '@' }, {
        });
    })
    .directive('seoPanel', function() {
        return { 
            restrict:'E',
            replace:false,
            templateUrl:'/Components/Seo/views/admin/panel.html',
            controller: ['$rootScope', '$location', '$scope', '$parse', 'SeoPageService', 'SeoRouteService', 
                 function($rootScope,   $location,   $scope,   $parse,   SeoPageService,   SeoRouteService) {

                $rootScope.$watch('page.seo_vars', function() {
                    $scope.metainfo = angular.copy($scope.page);
                    if ($scope.metainfo.seo_vars) {
                        $scope.metainfo.seo_vars = $parse($scope.metainfo.seo_vars)();
                    }
                });
                $rootScope.$watch('page.route_name', function(route) {
                    if (!route) {
                        return;
                    }
                    $scope.route = SeoRouteService.get({ name: route }, function(result) {
                        
                    });
                });
                $rootScope.$on('$routeChangeSuccess', function() {
                    $scope.pageSeo = SeoPageService.get({ url: $location.url() }, function(result) {
                        
                    });
                });
                $scope.saveRoute = function() {
                    $scope.route.$save();
                }
                $scope.savePage = function() {
                    $scope.pageSeo.$save();
                }
            }],
            link: function() {
            }
        }
    })
});