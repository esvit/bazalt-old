var angularComponents = [],
    modules = [];

for (var componentName in components) {
    var file = components[componentName];
    modules.push(file);
    angularComponents.push(componentName);
}

define('site', [].concat(modules), function() {
    var app = angular.module('main', ['bazalt-cms'].concat(angularComponents)).
        config(['$routeProvider', '$locationProvider', '$httpProvider', function($routeProvider, $locationProvider, $httpProvider) {
            // load content at first time
            var page = $('[ng-view],.ng-view,ng-view').html();

            $routeProvider.
                //when('/', {controller: 'IndexCtrl', templateUrl:'/'}).
                otherwise({
                    //redirectTo:'/',
                    controller: ['$scope', 'content', function($scope, content) {
                        $scope.content = content;
                    }],
                    template: '<div raw="content"></div>',
                    resolve: {
                        content: ['$q','$http', '$location', 'appLoading', function($q, $http, $location, appLoading) {
                            var deferred = $q.defer();
                            // if this is first call get content from variable else ajax
                            if (page) {
                                deferred.resolve(page);
                                page = null;
                                return deferred.promise;
                            }
                            appLoading.loading();

                            $http({method: 'GET', url: $location.url()})
                                .success(function(data) {
                                    appLoading.ready();
                                    deferred.resolve(data)
                                })
                                .error(function(data){
                                    appLoading.ready();
                                    //$location.url('/error/404/');
                                    deferred.resolve(data);
                                });

                            return deferred.promise;
                        }]
                    }
                });

            $locationProvider.html5Mode(true);
            $locationProvider.hashPrefix('!');
        }]);

    app.controller('IndexCtrl', function() {

    });
    app.run(['$rootScope', '$templateCache', '$document', '$page', function($rootScope, $templateCache, $document, $page) {
        $rootScope.$on('$pageChangeTitle', function(e, title) {
            $document.attr('title', title);
        });
        $rootScope.$on('$routeChangeStart', function() {
            //console.info('start');
        });
        $rootScope.$on('$routeChangeSuccess', function() {
        });
        $rootScope.$on('$viewContentLoaded', function() {
            // $templateCache.removeAll();
        });
    }]);


    app.factory('WishListsResource', ['$resource', '$q', function ($resource, $q) {
        return $resource('/rest.php/wish-lists', {}, {});
    }]);

    app.controller('ProductCtrl', function($scope, WishListsResource) {
        $scope.wishList = function(productId, inList) {
            if(typeof (Android) != "undefined") {
                Android.playSound('sounds/wow.mp3');
                if(inList) {
                    Android.showToast('Удалено из закладок');
                } else {
                    Android.showToast('Добавлено в закладки');
                }
            }

            if(inList) {
                WishListsResource.delete({
                    'product_id': productId
                }, {}, function(res) {
                    $('p.b-like span').text(' +'+res.count);
                    console.log(res);
                    //$scope.article.comments.push(res);
                });
                $scope.inList = false;
            } else {
                var listItm = new WishListsResource({
                    'product_id': productId
                });
                listItm.$save(function(res) {
                    $('p.b-like span').text(' +'+res.count);
                    console.log(res);
                    //$scope.article.comments.push(res);
                });
                $scope.inList = true;
            }
        }
        return false;
    });

    angular.bootstrap(document.documentElement, ['main']);
    return app;
});