'use strict';

define([], function() {

    angular.module('Component.Menu', [])
    .directive('bzNav', ['$rootScope', '$location', function($rootScope, $location) {
    
        return {
            link: function(scope, element, attrs) {
                $rootScope.$on('$routeChangeSuccess', function(e, route) {
                    var li = $('li', element);
                    li.each(function(i, item) {
                        var a = $('a:first', item);
                        $(item).toggleClass('active', (a.attr('href') == $location.url()));
                    });
                });
            }
        };
    }]);

});