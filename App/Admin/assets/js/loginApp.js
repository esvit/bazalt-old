require(['angular', 'bazalt-cms'], function() {

    app = angular.module('loginApp', ['bazalt-cms']).
        config(['$locationProvider', function($locationProvider) {
            $locationProvider.hashPrefix('!');
        }]);

    app.controller('LoginCtrl', ['$scope', '$location', '$session', function ($scope, $location, $session) {
        $scope.login = function() {
            $scope.is_submitting = true;
            $location.path('/');
            location.reload();
        }
    }]);

    angular.bootstrap(document.documentElement, ['loginApp']);

});