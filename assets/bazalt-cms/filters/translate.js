'use strict';

bazaltCMS.filter('translate', ['$locale', function($locale) {
    return function(str, domain) {
        return str;
    }
}]);