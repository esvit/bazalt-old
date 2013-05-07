bazaltCMS.directive('autoFillSync', ['$timeout', function($timeout) {
    return {
        require: '?ngModel',
        restrict: 'A',
        link: function(scope, element, attrs, ngModel) {
            var checkAutoFill = function() {
                if (ngModel.$viewValue !== element.val()) {
                    ngModel.$setViewValue(element.val());
                }
                $timeout(checkAutoFill, 100);
            };
            checkAutoFill();
        }
    };
}]);