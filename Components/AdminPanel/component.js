'use strict';

define(['jquery-ui', 'angular-cookies'], function() {

    angular.module('Component.AdminPanel', ['ngCookies'])
    .run(['$rootScope', '$compile', function($rootScope, $compile) {
        $('body').append($compile('<admin-panel />')($rootScope));
    }])
    .directive('adminPanel', function() {
        return { 
            restrict:'E',
            replace:false,
            templateUrl:'/Components/AdminPanel/views/admin/panel.html',
            controller: ['$scope', '$cookieStore', 'WidgetsService', function($scope, $cookieStore, WidgetsService) {
                $scope.$watch('showWidgets', function(value) {
                    $('body').toggleClass('cms-manage-widgets', value);
                    $cookieStore.put('showWidgets', value);
                    if (value) {
                        $('body').css('marginLeft', '200px');
                    } else {
                        $('body').css('marginLeft', '0px');
                    }
                });
                $scope.showWidgets = !!$cookieStore.get('showWidgets');
                $scope.manageWidgets = function() {
                    $scope.showWidgets = !$scope.showWidgets;
                }
                $scope.widgets = WidgetsService.query();
            }],
            link: function() {
                // body and bootstrap nav fixed
                $('body, .navbar-fixed-top').css('marginTop', 40);
            }
        }
    })
    .factory('WidgetsService', function($resource) {
            return $resource('/rest.php/app/widgets/:id', { 'id': '@' }, {
                create: { method: 'PUT' },
                changeOrder: { method: 'GET', params: { 'action': 'changeOrder' } },
                getSettings: { method: 'GET', params: { 'action': 'getSettings' } }
            });
    })

    .directive('widgets', function($compile, WidgetsService) {
        return {
            link: function(scope, element,attrs) {
                scope.isBase = attrs.widgets == 'base';

                scope.addWidget = function(id, widget) {
                    if (scope.isBase) {
                        widget.draggable({
                            connectToSortable: '.cms-widgets-border-around',
                            helper: "clone",
                            revert: "invalid"
                        });
                    }
                }

                var opts = {
                    revert: 'invalid',
                    scroll: true,
                    helper: "clone",
                    opacity: 0.5,
                    handle: '.bz-overlay',
                    placeholder: 'bz-widget_placeholder',
                    connectWith: '.cms-widgets-border-around',
                    //appendTo: $('#cms_widgets_panel'),
                    update: function(event, ui) {
                        var el = $(this);
                        var order = el.sortable('serialize');
                        var template = el.data('template');
                        var position = el.data('position');

                        WidgetsService.changeOrder({
                            template: template,
                            position: position,
                            order: order
                        }, function() {
                        
                        });
                        
                        if (!scope.$$phase) {
                            scope.$apply();
                        }
                        /*ComPanel_Webservice_Widget.changePosition(template, position, order, {
                            success: function() {

                            }
                        })*/
                        /*var request = new jsonRpcCall('changePosition', [template, position, order]);
                        requests.push(request);
                        if (requests.length == 1) {
                            $.widgetsTimer = setTimeout(function() {
                                jsonRpc.callLocal(ComPanel_Webservice_Widget.scriptUrl, requests);
                                requests = [];
                                //ComPanel_Webservice_Widget.changePosition(template, position, order);
                            }, 100);
                        }*/
                    },
                    stop: function(event, ui) {
                        var el = ui.item,
                            id = el.attr('id'),
                            container = el.closest('.cms-widgets-border-around');

                        if (!container.size() || id) {
                            return;
                        }


                        el.attr('id', 'bz-widget-new');

                        var template = container.data('template'),
                            position = container.data('position'),
                            widgetId = el.data('id'),
                            sorting = container.sortable('serialize');
            //            console.log('stop', event, el, container.sortable('serialize'));

                        el.addClass('loading');

                        WidgetsService.create({ id: widgetId, template: template, position: position, sorting: sorting }, function(result) {
                            el.after(result.content);
                            var widget = $('#bz-widget-' + result.id)
                            $compile(widget)(scope);
                            el.remove();
                        });
                        if (!scope.$$phase) {
                            scope.$apply();
                        }
                    }
                };
                scope.$on('$routeChangeSuccess', function(next, current) { 
                    //console.info($('.cms-widgets-border-around'));
                    //element.sortable(opts);
                });
                element.sortable(opts);
            }
        };
    })
    .directive('widget', function($compile, WidgetsService) {
        return {
            scope: true,
            controller: function($scope) {
                $scope.deleteWidget = function() {
                    if (confirm('Are you sure want delete widget?')) {
                        WidgetsService.remove({ id: $scope.widget.id }, function() {
                            $scope.element.slideUp(function() {
                                $scope.element.remove();
                            });
                        });
                    }
                }
                $scope.close = function(widget) {
                    $scope.dialog.remove();
                }
                $scope.saveSettings = function(widget) {
                    $scope.loading = true;
                    widget.$save(function(result) {
                        $scope.loading = false;
                        $scope.element.after(result.content);
                        $scope.element.remove();
                        var widget = $('#bz-widget-' + result.id)
                        $scope.dialog.remove();
                        $compile(widget)($scope.$parent);
                    });
                }
                $scope.showSettings = function() {
                    var dialog = '<div class="modal fade in"> \
                                      <div class="modal-dialog" id="bz-widget_dialog"> \
                                        <div class="modal-content"> \
                                          <div class="modal-header"> \
                                            <button type="button" class="close" ng-click="close()" data-dismiss="modal" aria-hidden="true">&times;</button> \
                                            <h4 class="modal-title">Widget settings</h4> \
                                          </div> \
                                          <div class="modal-body" id="bz-widget_settings"></div> \
                                          <div class="modal-footer"> \
                                            <a href="#" ng-click="close()" class="btn">Close</a> \
                                            <a href="#" ng-disabled="loading" ng-click="saveSettings(widget)" class="btn btn-primary">Save changes</a> \
                                          </div> \
                                        </div><!-- /.modal-content --> \
                                      </div><!-- /.modal-dalog --> \
                                    </div><!-- /.modal -->';
                    $scope.dialog = $(dialog).appendTo('body');

                    WidgetsService.getSettings({ id: $scope.widget.id }, function(result) {
                        $scope.widget = new WidgetsService(result.widget);
                        $scope.templates = result.templates;
                        $('#bz-widget_settings').empty().append(result.content);
                        $compile($('#bz-widget_dialog'))($scope)
                    });
                }
            },
            link: function(scope, element, attrs) {
                var widgets = angular.element(element).closest('[widgets]').scope();
                if (widgets) {
                    widgets.addWidget(attrs.widget, element);
                }

                scope.element = element;
                scope.widget = {
                    id: attrs.widget
                };
                if (!attrs.widgetTitle) {
                    return;
                }
                var title = '<div class="bz-widget_title">' + attrs.widgetTitle + '</div>';
                var buttons = '<div class="bz-widget_buttons"><div> \
                                <a href="javascript:;" ng-click="showSettings()" class="btn">Settings</a> \
                                <a href="javascript:;" ng-click="deleteWidget()" class="btn">Delete</a></div> \
                              </div>';
                var overlay = '<div class="bz-overlay"><div class="bg">' + buttons + '</div></div>';
                element.append($compile(title + overlay)(scope));
            }
        };
    })
});