define(['BazaltCMS', 'ckeditor/ckeditor'], function(bazaltCMS) {

    bazaltCMS.directive('ckeditor', function($timeout) {
        var index = 0;
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                var expression = attrs.ckeditor;

                var el = $(element);
                var instance = CKEDITOR.instances[attrs.id];
                if (instance) {
                    instance.destroy(false);
                }
                instance = CKEDITOR.replace(el.get(0));
                instance.setUiColor('#FAFAFA');

                scope.$watch(expression, function (val) {
                    if (!instance) return;
                    if (scope[expression] == instance.getData()) return;
                    instance.setData(scope[expression]);
                });

                instance.on("change", function () {
                    scope[expression] = instance.getData();
                    scope.$root.$eval();
                });
            }
        };
    })

});