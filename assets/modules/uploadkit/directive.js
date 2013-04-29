angular.module('bzUploader', [])
    .directive("bzUploader", ['$parse', '$rootScope',function($parse, $rootScope) {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, element, attrs, ngModel) {
                var callback = $parse(attrs.bzUploader);

                /*var onChange = function() {
                    ngModel.$setViewValue(checkbox.bootstrapSwitch('status'));
                    callback(scope);
                };
                var checkbox = $(element).wrap('<div class=""></div>').parent().bootstrapSwitch();
                ngModel.$render = function(value) {
                    checkbox.unbind('change');
                    checkbox.bootstrapSwitch('setState', value);
                    checkbox.change(onChange);
                }
                scope.$watch(attrs.ngModel, ngModel.$render);
                checkbox.change(onChange);*/

                new UploadKit(element);
                $(element).hide();
                attrs.$observe('url', function(value) {
                    $(element).data('uploader').settings.url = value;
                });
                $(element).data('uploader').bind('FileUploaded', function(uploader, file, response) {
                    var result = $parse(response.response)();
                    callback(scope, { '$file': result });
                });


            }
        };
    }]);