var bazaltCMS = angular.module('bazalt-cms', ['ngResource']).
    config(['$interpolateProvider', function($interpolateProvider){
        $interpolateProvider.startSymbol('{[').endSymbol(']}');
    }]);

bazaltCMS.addMessages = function(locale, domain, messages) {
    window._locales = window._locales || {};

    window._locales[locale] = messages;
    //console.info(locale, domain, messages);
};
bazaltCMS.controller('bazaltGlobalController', ['$scope','$rootScope', 'languages',function($scope, $rootScope, languages) {
    $rootScope.languages = languages;
}]);