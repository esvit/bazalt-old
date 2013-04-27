bazaltCMS.directive('translate', ['$filter', '$interpolate', '$rootScope', function($filter, $interpolate, $rootScope) {

    var translate = $filter('translate');

    return {
        restrict: 'A',
        scope: true,
        link: function linkFn(scope, element, attr) {

            attr.$observe('translate', function (translationDomain) {
                scope.translationId = $interpolate(element.text())(scope.$parent);
                if (translationDomain != '') {
                    scope.translationDomain = translationDomain;
                }
            });

            attr.$observe('values', function (interpolateParams) {
                scope.interpolateParams = interpolateParams;
            });

            scope.$watch('translationDomain + interpolateParams', function () {
                element.html(translate(scope.translationId, scope.translationDomain, scope.interpolateParams));
            });
            $rootScope.$watch('tr', function () {
                element.html(translate(scope.translationId, scope.translationDomain, scope.interpolateParams));
            });
        }
    };

}]);