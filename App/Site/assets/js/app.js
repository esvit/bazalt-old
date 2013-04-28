require([], function() {

    app = angular.module('main', ['bazalt-cms']).
        config(['$routeProvider', '$locationProvider', '$httpProvider', function($routeProvider, $locationProvider, $httpProvider) {
            $routeProvider.
            //when('/', {controller: 'IndexCtrl', templateUrl:'/'}).
            otherwise({
                //redirectTo:'/',
                controller: ['$scope', 'content', function($scope, content) {
                    $scope.content = content;
                }],
                template: '<div ng-bind-html-unsafe="content"></div>',
                resolve: {
                    content: ['$q','$http', '$location', 'appLoading', function($q, $http, $location, appLoading) {
                        var deferred = $q.defer();
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
    app.run(['$rootScope', '$templateCache', function($rootScope, $templateCache) {
       $rootScope.$on('$routeChangeStart', function() {
            //console.info('start');
       });
       $rootScope.$on('$routeChangeSuccess', function() {
            //console.info('success');
       });
       $rootScope.$on('$viewContentLoaded', function() {
         // $templateCache.removeAll();
       });
    }]);

    angular.bootstrap(document, ['main']);

});