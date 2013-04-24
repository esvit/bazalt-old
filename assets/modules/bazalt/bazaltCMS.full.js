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
});/**
 * @license HTTP Auth Interceptor Module for AngularJS
 * (c) 2012 Witold Szczerba
 * License: MIT
 */
bazaltCMS.provider('AuthService', function () {
    /**
     * Holds all the requests which failed due to 401 response,
     * so they can be re-requested in future, once login is completed.
     */
    var buffer = [];

    /**
     * Holds a list of functions that define rules for ignoring
     * the addition of requests to the buffer.
     */
    var ignoreUrlExpressions = [];

    /**
     * Adds functions to the `ignoreUrlExpressions` array.
     * The fn function takes a URL as a response as an argument and returns
     * `true` (to ignore the URL) or `false` (to allow the URL). When `true` is
     * returned no other expressions will be tested.
     */
    this.addIgnoreUrlExpression = function (fn) {
      if (angular.isFunction(fn)) { ignoreUrlExpressions.push(fn); }
      return this;
    };

    /**
     * Executes each of the ignore expressions to determine whether the URL
     * should be ignored.
     * 
     * Example:
     *
     *     angular.module('mod', ['http-auth-interceptor'])
     *       .config(function (authServiceProvider) {
     *         authServiceProvider.addIgnoreUrlExpression(function (response) {
     *           return response.config.url === "/api/auth";
     *         });
     *       });
     */
    this.shouldIgnoreUrl = function (response) {
      var fn, i, j = ignoreUrlExpressions.length;

      for (i = 0; i < j; i++) {
        fn = ignoreUrlExpressions[i];
        if (fn(response) === true) { return true; }
      }

      return false;
    };

    /**
     * Required by HTTP interceptor.
     * Function is attached to provider to be invisible for regular users of this service.
     */
    this.pushToBuffer = function (config, deferred) {
      buffer.push({
        config: config,
        deferred: deferred
      });
    };

    this.$get = ['$rootScope', '$injector', function ($rootScope, $injector) {
      var $http; //initialized later because of circular dependency problem
      function retry(config, deferred) {
        $http = $http || $injector.get('$http');
        $http(config).then(function (response) {
          deferred.resolve(response);
        });
      }
      function retryAll() {
        var i;

        for (i = 0; i < buffer.length; ++i) {
          retry(buffer[i].config, buffer[i].deferred);
        }
        buffer = [];
      }

      return {
        loginConfirmed: function () {
          $rootScope.$broadcast('event:auth-loginConfirmed');
          retryAll();
        }
      };
    }];
  })

  /**
   * $http interceptor.
   * On 401 response - it stores the request and broadcasts 'event:angular-auth-loginRequired'.
   */
  .config(['$httpProvider', 'AuthServiceProvider', function ($httpProvider, AuthServiceProvider) {

    var interceptor = ['$rootScope', '$q', function ($rootScope, $q) {
      function success(response) {
        return response;
      }

      function error(response) {
        if (response.status === 401) {
          var deferred = $q.defer();

          if (!AuthServiceProvider.shouldIgnoreUrl(response)) {
            AuthServiceProvider.pushToBuffer(response.config, deferred);
          }

          $rootScope.$broadcast('event:auth-loginRequired');
          return deferred.promise;
        }
        // otherwise
        return $q.reject(response);
      }

      return function (promise) {
        return promise.then(success, error);
      };

    }];
    $httpProvider.responseInterceptors.push(interceptor);
  }]);bazaltCMS.directive('compareValidate', function() {

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, elm, attrs, ctrl) {

            function validateEqual(myValue, otherValue) {
                if (myValue === otherValue) {
                    ctrl.$setValidity('equal', true);
                    return myValue;
                } else {
                    ctrl.$setValidity('equal', false);
                    return undefined;
                }
            }

            scope.$watch(attrs.compareValidate, function(otherModelValue) {
                validateEqual(ctrl.$viewValue, otherModelValue);               
            });

            ctrl.$parsers.unshift(function(viewValue) {
                return validateEqual(viewValue, scope.$eval(attrs.compareValidate));
            });

            ctrl.$formatters.unshift(function(modelValue) {
                return validateEqual(modelValue, scope.$eval(attrs.compareValidate));                
            });
        }
    };
});
bazaltCMS.directive('grid', function($compile, $parse) {
    var template = '<thead> \
                        <tr> \
                            <th class="header" \
                                ng-class="{\'sortable\': column.sortable,\'sort-down\': column.sort==\'down\', \'sort-up\': column.sort==\'up\'}" \
                                ng-click="sortBy(column)" \
                                ng-repeat="column in columns"><div>{[column.title]}</div></th> \
                        </tr> \
                        <tr ng-show="filter.active"> \
                            <th class="filter" ng-repeat="column in columns"> \
                                <div ng-repeat="(name, filter) in column.filter">\
                                    <input type="text" ng-model="grid.filter[name]" class="input-filter" ng-show="filter == \'text\'" /> \
                                    <select class="filter filter-select" ng-options="data.id as data.title for data in column.data" ng-model="grid.filter[name]" ng-show="filter == \'select\'"></select> \
                                    <input type="text" date-range ng-model="grid.filter[name]" ng-show="filter == \'date\'" /> \
                                    <button class="btn btn-primary btn-block" ng-click="doFilter()" ng-show="filter == \'button\'">Filter</button> \
                                </div>\
                            </th> \
                        </tr> \
                        </thead>';
    var pager = '<ul class="pagination ng-cloak" ng-show="pager && pager.count > 1"> \
                      <li ng-class="{\'disabled\':pager.current == 1}"><a ng-click="goToPage(pager.current-1)" href="javascript:;">&laquo;</a></li> \
                      <li ng-show="pager.current > 3"><a ng-click="goToPage(1)" href="javascript:;">1</a></li> \
                      <li class="disabled" ng-show="pager.current > 4"><span>...</span></li> \
                      <li ng-repeat="page in pager.pages" ng-class="{\'disabled\':pager.current == page}"><a href="javascript:;" ng-click="goToPage(page)">{[page]}</a></li> \
                      <li class="disabled" ng-show="pager.current + 3 < pager.count"><span>...</span></li> \
                      <li ng-show="pager.current + 2 < pager.count"><span><a ng-click="goToPage(pager.count)" href="javascript:;">{[pager.count]}</a></span></li> \
                      <li ng-class="{\'disabled\':pager.current == pager.count}"><a ng-click="goToPage(pager.current+1)" href="javascript:;">&raquo;</a></li> \
                    </ul> \
                    <div class="btn-group pull-right"> \
                        <button type="button" ng-class="{\'active\':grid.count == 10}" ng-click="goToPage(pager.current, 10)" class="btn btn-mini">10</button> \
                        <button type="button" ng-class="{\'active\':grid.count == 25}" ng-click="goToPage(pager.current, 25)" class="btn btn-mini">25</button> \
                        <button type="button" ng-class="{\'active\':grid.count == 50}" ng-click="goToPage(pager.current, 50)" class="btn btn-mini">50</button> \
                        <button type="button" ng-class="{\'active\':grid.count == 100}" ng-click="goToPage(pager.current, 100)" class="btn btn-mini">100</button> \
                    </div>';
    return {
        restrict: 'A',
        priority: 1001,
        controller: function($scope, $timeout) {
            $scope.goToPage = function(page, count) {
                var data = $scope.grid;
                if (((page > 0 && data.page != page && $scope.pager.count >= page) || angular.isDefined(count)) && $scope.callback) {
                    data.page = page;
                    data.count = count || data.count;
                    $scope.callback(data);
                }
            }
            $scope.doFilter = function() {
                $scope.grid.page = 1;
                var data = $scope.grid;
                if ($scope.callback) {
                    $scope.callback(data);
                }
            }
            $scope.grid = {
                page: 1,
                count: 10,
                filter: {},
                sorting: [],
                sortingDirection: []
            };
            $scope.sortBy = function(column) {
                if (!column.sortable) {
                    return;
                }
                var sorting = $scope.grid.sorting.length && ($scope.grid.sorting[0] == column.sortable) && $scope.grid.sortingDirection[0];
                $scope.grid.sorting = [column.sortable];
                $scope.grid.sortingDirection = [!sorting];

                angular.forEach($scope.columns, function(column) {
                    column.sort = false;
                });
                column.sort = sorting ? 'up' : 'down';

                if ($scope.callback) {
                    $scope.callback($scope.grid);
                }
            }
        },
        compile: function(element, attrs) {
            element.addClass('table');
            var i = 0;
            var columns = [];
            angular.forEach(element.find('td'), function(item) {
                var el = $(item);
                columns.push({
                    id: i++,
                    title: el.attr('title') || el.text(),
                    sortable: el.attr('sortable') ? el.attr('sortable') : false,
                    filter: el.attr('filter') ? $parse(el.attr('filter'))() : false,
                    filterData: el.attr('filter-data') ? el.attr('filter-data') : null
                });
            });
            return function(scope, element, attrs) {
                scope.callback = scope[attrs.grid];
                scope.columns = columns;

                var getInterval = function(page, numPages) {
                    var midRange = 5;
                    var neHalf, upperLimit, start, end;
                    neHalf = Math.ceil(midRange / 2);
                    upperLimit = numPages - midRange;
                    start = page > neHalf ? Math.max(Math.min(page - neHalf, upperLimit), 0) : 0;
                    end = page > neHalf ?
                        Math.min(page + neHalf - (midRange % 2 > 0 ? 1 : 0), numPages) :
                        Math.min(midRange, numPages);
                    return {start: start,end: end};
                };

                scope.$watch(attrs.pager, function(value) {
                    if (angular.isUndefined(value)) {
                        return;
                    }
                    var interval = getInterval(value.current, value.count);
                    value.pages = [];
                    for (var i = interval.start + 1; i < interval.end + 1; i++) {
                        value.pages.push(i);
                    }
                    scope.pager = value;
                    scope.grid.count = value.countPerPage;
                });

                angular.forEach(columns, function(column) {
                    if (!column.filterData) {
                        return;
                    }
                    var promise = scope[column.filterData](column);
                    delete column['filterData'];
                    promise.then(function(data) {
                        if (!angular.isArray(data)) {
                            data = [];
                        }
                        data.unshift({ title: '-' });
                        column.data = data;
                    });
                });
                if (!element.hasClass('ng-table')) {
                    var html = $compile(template)(scope);
                    var pagination = $compile(pager)(scope);
                    element.filter('thead').remove();
                    element.prepend(html).addClass('ng-table');
                    element.after(pagination);
                }
            };
        }
    };
});bazaltCMS.directive('link', function($rootScope) {
  return {
    restrict: 'E',
    terminal: true,
    compile: function(element, attr) {
      if (attr.type == 'text/x-gettext-translation') {
        var domain = attr.id,
            url = attr.href;

        Pomo.unescapeStrings = true;
        Pomo.returnStrings = true;
        Pomo.load(url, {
            translation_domain: domain,
            format:'po',
            mode:'ajax'
        }).ready(function(){
            $rootScope.tr = new Date().getTime();
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        });
      }
    }
  };
});bazaltCMS.directive('regexValidate', function() {
    return {
        // restrict to an attribute type.
        restrict: 'A',
        
        // element must have ng-model attribute.
        require: 'ngModel',
        
        // scope = the parent scope
        // elem = the element the directive is on
        // attr = a dictionary of attributes on the element
        // ctrl = the controller for ngModel.
        link: function(scope, elem, attr, ctrl) {
            
            //get the regex flags from the regex-validate-flags="" attribute (optional)
            var flags = attr.regexValidateFlags || '';
            
            // create the regex obj.
            var regex = new RegExp(attr.regexValidate, flags);            
                        
            // add a parser that will process each time the value is 
            // parsed into the model when the user updates it.
            ctrl.$parsers.unshift(function(value) {
                // test and set the validity after update.
                var valid = regex.test(value);
                ctrl.$setValidity('regexValidate', valid);
                
                // if it's valid, return the value to the model, 
                // otherwise return undefined.
                return valid ? value : undefined;
            });
            
            // add a formatter that will process each time the value 
            // is updated on the DOM element.
            ctrl.$formatters.unshift(function(value) {
                // validate.
                ctrl.$setValidity('regexValidate', regex.test(value));
                
                // return the value or nothing will be written to the DOM.
                return value;
            });
        }
    };
});bazaltCMS.directive('serverSubmit', function ($http) {
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
    });    bazaltCMS.factory('$session', function($rootScope, $resource, $location, AuthService, $route) {
        var Session = $resource('/rest.php/app/auth/', {}, {
          'get': { method: 'GET' },
          'login': { method: 'POST' },
          'logout': { method: 'DELETE' }
        });
        Session.prototype.login = function(cb) {
            this.$login({}, function(user) {
                $rootScope.$user = new Session(user);
                AuthService.loginConfirmed();
                $rootScope.$broadcast('event:loginConfirmed');
                $route.reload();
            }, function(err) {
                cb = cb || function() {};
                cb(err);
            });
        }
        Session.prototype.logout = function() {
            this.$logout(function() {
                $rootScope.$user = new Session();
                $location.path('/');
                $route.reload();
            });
        }
        Session.prototype.authorized = function() {
            return this.id != null;
        }

        if (!$rootScope.$user) {
            $rootScope.$user = new Session();
            $rootScope.$user = Session.get(function(user) {
                if (user.authorized()) {
                    $rootScope.$broadcast('event:loginConfirmed');
                    $route.reload();
                }
            });
        }
        $rootScope.$session = Session;
        return Session;
    });bazaltCMS.directive('translate', function($filter, $interpolate, $rootScope) {

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

});bazaltCMS.directive('ngUpload', function($parse) {
    var counter = 0;

    return {
        restrict: 'A',
        'scope':false,
        link: function(scope, element, attrs) {
            var name = attrs['name'];
            var target = attrs['ngUpload'];
            var model = attrs['model'];

            scope.$watch(function() {
                return attrs['ngUpload'];
            }, function(value) {
                target = value;
            });
            var onChange = function(e) {
                var form = angular.element('<form style="display:none;"></form>');
                //var file = e.target.files !== undefined ? e.target.files[0] : (e.target.value ? { name: e.target.value.replace(/^.+\\/, '') } : null)

                // build iframe
                var iframe = angular.element(
                                '<iframe src="javascript:false;" name="iframe-transport-' +
                                    (counter += 1) + '"></iframe>'
                            )// add iframe to app
                form
                    .attr('accept-charset', 'UTF-8')
                    .prop('target', iframe.prop('name'))
                    .prop('action', target)
                    .prop('method', 'POST');

                // attach function to load event
                iframe.bind('load', function() {
                    var response;
                    // Wrap in a try/catch block to catch exceptions thrown
                    // when trying to access cross-domain iframe contents:
                    try {
                        response = iframe.contents();
                        // Google Chrome and Firefox do not throw an
                        // exception when calling iframe.contents() on
                        // cross-domain requests, so we unify the response:
                        if (!response.length || !response[0].firstChild) {
                            throw new Error();
                        }
                        response = JSON.parse($(response[0].body).text());
                    } catch (e) {
                        response = undefined;
                    }
                    if (!response) {
                        return;
                    }
                    var result = response[name];
                    if (model) {
                        scope.$apply(function() {
                            var fn = $parse(model);
                            fn.assign(scope, result);
                        });
                    }
                    // Fix for IE endless progress bar activity bug
                    // (happens on form submits to iframe targets):
                    frame = angular.element('<iframe src="javascript:false;"></iframe>');
                    form.append(frame);
                    form.remove();
                });

                var clone = element.clone();
                    clone.bind('change', onChange);
                    element.replaceWith(clone);

                form.append(element)
                    .prop('enctype', 'multipart/form-data')
                    // enctype must be set as encoding for IE:
                    .prop('encoding', 'multipart/form-data')
                    .append(iframe);

                element = clone;

                form.appendTo('body');
                form.submit();
            };
            element.bind('change', onChange);
        }
    }
});bazaltCMS.controller('FileUploadCtrl', function($scope) {
    //============== DRAG & DROP =============
    // source for drag&drop: http://www.webappers.com/2011/09/28/drag-drop-file-upload-with-html5-javascript/
    var dropbox = document.getElementById("dropbox")
    scope.dropText = 'Drop files here...'

    // init event handlers
    function dragEnterLeave(evt) {
        evt.stopPropagation()
        evt.preventDefault()
        scope.$apply(function(){
            scope.dropText = 'Drop files here...'
            scope.dropClass = ''
        })
    }
    dropbox.addEventListener("dragenter", dragEnterLeave, false)
    dropbox.addEventListener("dragleave", dragEnterLeave, false)
    dropbox.addEventListener("dragover", function(evt) {
        evt.stopPropagation()
        evt.preventDefault()
        var clazz = 'not-available'
        var ok = evt.dataTransfer && evt.dataTransfer.types && evt.dataTransfer.types.indexOf('Files') >= 0
        scope.$apply(function(){
            scope.dropText = ok ? 'Drop files here...' : 'Only files are allowed!'
            scope.dropClass = ok ? 'over' : 'not-available'
        })
    }, false)
    dropbox.addEventListener("drop", function(evt) {
        console.log('drop evt:', JSON.parse(JSON.stringify(evt.dataTransfer)))
        evt.stopPropagation()
        evt.preventDefault()
        scope.$apply(function(){
            scope.dropText = 'Drop files here...'
            scope.dropClass = ''
        })
        var files = evt.dataTransfer.files
        if (files.length > 0) {
            scope.$apply(function(){
                scope.files = []
                for (var i = 0; i < files.length; i++) {
                    scope.files.push(files[i])
                }
            })
        }
    }, false)
    //============== DRAG & DROP =============

    scope.setFiles = function(element) {
    scope.$apply(function(scope) {
      console.log('files:', element.files);
      // Turn the FileList object into an Array
        scope.files = []
        for (var i = 0; i < element.files.length; i++) {
          scope.files.push(element.files[i])
        }
      scope.progressVisible = false
      });
    };

    scope.uploadFile = function() {
        var fd = new FormData()
        for (var i in scope.files) {
            fd.append("uploadedFile", scope.files[i])
        }
        var xhr = new XMLHttpRequest()
        xhr.upload.addEventListener("progress", uploadProgress, false)
        xhr.addEventListener("load", uploadComplete, false)
        xhr.addEventListener("error", uploadFailed, false)
        xhr.addEventListener("abort", uploadCanceled, false)
        xhr.open("POST", "/fileupload")
        scope.progressVisible = true
        xhr.send(fd)
    }

    function uploadProgress(evt) {
        scope.$apply(function(){
            if (evt.lengthComputable) {
                scope.progress = Math.round(evt.loaded * 100 / evt.total)
            } else {
                scope.progress = 'unable to compute'
            }
        })
    }

    function uploadComplete(evt) {
        /* This event is raised when the server send back a response */
        alert(evt.target.responseText)
    }

    function uploadFailed(evt) {
        alert("There was an error attempting to upload the file.")
    }

    function uploadCanceled(evt) {
        scope.$apply(function(){
            scope.progressVisible = false
        })
        alert("The upload has been canceled by the user or the browser dropped the connection.")
    }
});

bazaltCMS.factory('appLoading', function($rootScope) {
    var timer;
    return {
        loading : function() {
            clearTimeout(timer);
            $rootScope.status = 'loading';
            if (!$rootScope.$$phase) $rootScope.$apply();
        },
        ready : function(delay) {
            function ready() {
                $rootScope.status = 'ready';
                if(!$rootScope.$$phase) $rootScope.$apply();
            }

            clearTimeout(timer);
            delay = delay == null ? 500 : false;
            if (delay) {
                timer = setTimeout(ready, delay);
            } else {
                ready();
            }
        }
    };
});bazaltCMS.filter('default', function() {
    return function(value, defaultValue) {
        return value ? value : defaultValue;
    }
});bazaltCMS
.value('languages', {
        all: [
            {
                title: 'English',
                alias: 'en'
            }/*,
            {
                title: 'UA',
                alias: 'ukr'
            }*/
        ],
        current: 'en'
    })
.run(function(languages, $rootScope) {
    $(document).keydown(function(e) {
        var shiftNums = {
            "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&", "8": "*", "9": "(", "0": ")"
        }
    
      if(e.ctrlKey && e.altKey) {
        var character = parseInt(String.fromCharCode(e.which).toLowerCase());
        if (character > 0 && character <= languages.all.length) {
            e.preventDefault();
            languages.current = languages.all[character - 1].alias;
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        }
      }
    });
})
.filter('language', function(languages) {
    return function(value, language) {
        if (typeof value == 'undefined' || value == null) {
            return value;
        }
        language = language || languages.current;
        if (!value[language] && value['orig']) {
            return value[value['orig']] + " (" + value['orig'] + ")";
        }
        return value[language];
    }
});'use strict';

/*
 * фильтр для локализации.
 * использование:
 *
 *  обычный текст:
 *  - {{'Привет, мир'|i18n}}
 *    в файле locales.js:
 *    var _locales = { 'ru-ru': { 'Привет, мир': 'Привет, мир' }, 'en-us': { 'Привет, мир': 'Hello, world' } };
 *
 *  переменные:
 *  - {{'%1 яблоко. Весёлый %2'|i18n:'Красное':'мальчик'}}
 *    в файле locales.js:
 *    var _locales = { 'ru-ru': { '%1 яблоко. Весёлый %2': '%1 яблоко. Весёлый %2' }, 'en-us': { '%1 яблоко. Весёлый %2': 'Apple is %1. Happy %2' } };
 *
 *  склонения:
 *  - {{'Всего %1 яблоко в %2 корзине'|i18n:'plural':4:'моей'}}
 *    в файле locales.js:
 *    var _locales = {
 *        'ru-ru': {
 *            'Всего %1 яблоко в %2 корзине': [
 *                'Всего %1 яблоко в %2 корзине',
 *                'Всего %1 яблока в %2 корзине',
 *                'Всего %1 яблок в %2 корзине'
 *            ]
 *        },
 *        'en-us': {
 *            'Всего %1 яблоко в %2 корзине': [
 *                'There is %1 apple in %2 basket',
 *                'There are %1 apples in %2 basket',
 *            ]
 *        }
 *    }
 *
 *  в js:
 *  - $filter('i18n')('Строка в js');
 *    в файле locales.js:
 *    var _locales = { 'ru-ru': { 'Строка в js': 'Строка в js' }, 'en-us': { 'Строка в js': 'String in js' } };
 *
 *  - $filter('i18n')('Текущая локаль: %1', $locale.id);
 *    var _locales = { 'ru-ru': { 'Текущая локаль: %1': 'Текущая локаль: %1' }, 'en-us': { 'Текущая локаль: %1': 'Current locale: %1' } };
 *
 */
bazaltCMS.filter('translate', function($locale) {
    return function(str, domain) {
        try {
            var tr = Pomo.getText(str, {
                domain: domain
            });
        } catch (e) {
            return str;
        }
        return tr || str;
        var offset = 1;
        if (arguments[1] && arguments[1] === 'plural') {
            var n = arguments[2],
                    plural;

            switch ($locale.id) {
                case 'en-us':
                case 'de-de':
                case 'es-es':
                    plural = 0 + (n != 1);
                    break;
                case 'ru-ru':
                    plural = (n % 10 == 1 && n % 100 != 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2);
                    break;
                case 'ar':
                    plural = n == 0 ? 0 : n == 1 ? 1 : n == 2 ? 2 : n % 100 >= 3 && n % 100 <= 10 ? 3 : n % 100 >= 11 ? 4 : 5;
                default:
                    plural = 0 + (n != 1);
            }

            if (_locales[$locale.id][str]) {
                str = _locales[$locale.id][str][plural] || str;
            }
            offset = 2;
        } else if (typeof _locales[$locale.id][str] != 'undefined') {
            str = _locales[$locale.id][str];
        }

        for (var i = offset; i < arguments.length; i++) {
            str = str.split('%' + (i - offset + 1)).join(arguments[i]);
        }

        return str;
    }
});