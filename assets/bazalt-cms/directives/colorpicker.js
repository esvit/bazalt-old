'use strict';

bazaltCMS.directive('colorpicker', function() {

    return {
        require: '?ngModel',
        link: function(scope, element, attrs, controller) {
            var updateModel;

            if(controller != null) {
                updateModel = function(value) {
                    return scope.$apply(function() {
                        return controller.$setViewValue(value);
                    });
                };

                controller.$render = function() {
                    if (controller.$viewValue != null) {
                        var color = element.data('colorpicker').color;
                        color.setColor(controller.$viewValue);
                        element.val(color.toHex());
                    }
                    /*return element.colorpicker().on('changeColor', function(e) {
                        if(updateModel) updateModel(e.color.toHex());
                    });*/
                };
            }

            return element.colorpicker().on('changeColor', function(e) {
                if(updateModel) updateModel(e.color.toHex());
            });

        }
    };
});

