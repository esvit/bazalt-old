var components = $('body').attr('bazalt-cms-components').split(',');

var angularComponents = [],
    modules = [];

for (var i = 0; i < components.length; i++) {
    var component = components[i];
    modules.push('/Components/' + component + '/component.js');
    angularComponents.push('Component.' + component);
}

require([].concat(modules), function() {
    var app = angular.module('main', ['bazalt-cms'].concat(angularComponents)).
        config(['$routeProvider', '$locationProvider', '$httpProvider', function($routeProvider, $locationProvider, $httpProvider) {
            $routeProvider.
            //when('/', {controller: 'IndexCtrl', templateUrl:'/'}).
            otherwise({
                //redirectTo:'/',
                controller: ['$scope', 'content', function($scope, content) {
                    $scope.content = content;
                }],
                template: '<div compile="content"></div>',
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
    }]).directive('compile', function($compile) {
        // directive factory creates a link function
        return function(scope, element, attrs) {
            scope.$watch(function(scope) {
                // watch the 'compile' expression for changes
                return scope.$eval(attrs.compile);
            }, function(value) {
                // when the 'compile' expression changes
                // assign it into the current DOM
                element.html(value);

                // compile the new DOM and link it to the current
                // scope.
                // NOTE: we only compile .childNodes so that
                // we don't get into infinite loop compiling ourselves
                $compile(element.contents())(scope);
            });
        }
    });

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