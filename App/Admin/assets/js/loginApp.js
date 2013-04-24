require(['BazaltCMS', '/Components/Users/component.js'], function(bazaltCMS) {

    app = angular.module('loginApp', ['bazaltCMS']).
        config(function($routeProvider, $locationProvider, $httpProvider) {
            //$locationProvider.html5Mode(true);
            $locationProvider.hashPrefix('!');
        });

    app.controller('LoginCtrl', function ($scope, $location, $session) {
        $scope.login = function() {
            $location.path('/');
            location.reload();
        }
    });

    angular.bootstrap(document, ['loginApp']);

});