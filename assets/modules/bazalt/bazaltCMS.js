bazaltCMS = angular.module('bazaltCMS', ['ngResource']).
    config(function($interpolateProvider){
        $interpolateProvider.startSymbol('{[').endSymbol(']}');
    });

bazaltCMS.addMessages = function(locale, domain, messages) {
    window._locales = window._locales || {};

    window._locales[locale] = messages;
    //console.info(locale, domain, messages);
};
bazaltCMS.controller('bazaltGlobalController', function($scope, $rootScope, languages) {
    $rootScope.languages = languages;
});