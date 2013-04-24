bazaltCMS.directive('serverSubmit', function ($http) {
        function IllegalArgumentException(message) {
            this.message = message;
        }

        var forEach = angular.forEach,
            noop = angular.noop;

        return {
            'restrict':'A',
            'scope':false,
            'controller':function ($scope, $element, $attrs) {
                var self = this;
                self.formComponents = {};
                self.registerFormComponent = function (name, ngModel) {
                    self.formComponents[name] = ngModel;
                };
                self.hasFormComponent = function (name) {
                    return self.formComponents[name] != undefined;
                };
                self.getFormComponent = function (name) {
                console.info(self.formComponents[name]);
                    return self.formComponents[name];
                };

                /**
                 * Every submit should reset the form component, because its possible
                 * that the error is gone, but the form is still not valid
                 */
                self.resetFormComponentsValidity = function () {
                    forEach(self.formComponents, function (component) {
                        component.$setValidity('server', true);
                    });
                }
            },

            'link':function (scope, element, attrs, ctrl) {
                scope.remoteForm = {
                    data: {},
                    errors: {},
                    result: null,
                    target: attrs['target'],
                    success: attrs['serverSubmit'],
                    method: attrs['method'] || 'post'
                };
                if (scope.remoteForm.target == undefined) {
                    throw new IllegalArgumentException('target must be defined');
                }
                scope.is_submitting = false;
                element.bind('submit', function () {
                    scope.$apply(function() {
                        ctrl.resetFormComponentsValidity();

                        scope.remoteForm.data = {};
                        scope.remoteForm.errors = {};
                        forEach(ctrl.formComponents, function (component) {
                            scope.remoteForm.data[component.$name] = component.$viewValue;
                        });
                        scope.remoteForm.result = null;
                        scope.is_submitting = true;

                        $http({method:scope.remoteForm.method, url: scope.remoteForm.target, data: scope.remoteForm.data})
                            .success(function(result) {
                                scope.is_submitting = false;
                                if((typeof scope[scope.remoteForm.success]) == 'function') {
                                    scope[scope.remoteForm.success](result);
                                }
                            })
                            .error(function (data, status) {
                                scope.is_submitting = false;
                                if (status == 400) {
                                    forEach(data, function(errors, name) {
                                        if (ctrl.hasFormComponent(name)) {
                                            var component = ctrl.getFormComponent(name);
                                            forEach(errors, function(valid, name) {
                                                component.$setValidity(name, valid);
                                            });
                                            component.$setValidity('server', false);
                                            scope.remoteForm.errors[name] = errors;
                                        }
                                    });
                                }
                            });
                    });
                });
            }
        }
    })
    .directive('remoteFormComponent', function () {
        return {
            'restrict':'A',
            'require':['^serverSubmit', 'ngModel'],

            'link':function (scope, element, attrs, ctrls) {
                var formCtrl = ctrls[0];
                var ngModel = ctrls[1];
                formCtrl.registerFormComponent(attrs.name, ngModel);
                scope.$watch(function() {
                    return ngModel.$viewValue;
                }, function(model) {
                    ngModel.$setValidity('server', true);
                });
            }
        }
    });