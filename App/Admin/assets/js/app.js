require([
    'jquery/jquery-migrate-1.1.1',
    'BazaltCMS', 'Bootstrap', 'UIBootstrap',
    '/Components/Users/component.js',
    '/Components/Gallery/admin.js',
    '/Components/News/admin.js',
    '/Components/Themes/admin.js',
    '/Components/Menu/admin.js',
    '/Components/Files/admin.js',
    '/App/Admin/assets/js/jquery-scrolltofixed-min.js'], function(bazaltCMS) {
    
    app = angular.module('bazalt-cms', ['ui.bootstrap', 'bazaltCMS',
        'Component.Users', 'Component.Gallery.Admin', 'Component.News.Admin',
        'Component.Themes.Admin',
        'Component.Menu.Admin',
        'Component.Files.Admin'
        ]).
    config(function($routeProvider, $locationProvider, $httpProvider) {
        $routeProvider.
        when('/', {controller: 'IndexCtrl', templateUrl:'/App/Admin/views/index.html'}).
        when('/settings', {controller: 'SettingsCtrl', templateUrl:'/App/Admin/views/settings.html'}).
        otherwise({redirectTo:'/'});

        //$locationProvider.html5Mode(true);
        $locationProvider.hashPrefix('!');
    });

    app.directive('switch', function() {
        return {
            restrict: 'C',
            link: function(scope, elem, attrs) {
                $(elem).bootstrapSwitch();
            }
        }
    });

    app.directive('authDemoApplication', function() {
    return {
    restrict: 'C',
    link: function(scope, elem, attrs) {
    //once Angular is started, remove class:
    elem.removeClass('waiting-for-angular');

    var login = elem.find('#login-holder');
    var main = elem.find('#content');

    login.modal({
    keyboard: false,
    backdrop: 'static',
    show: false
    });

    scope.$on('event:auth-loginRequired', function() {
    login.modal('show')
    });
    scope.$on('event:auth-loginConfirmed', function() {
    login.modal('hide')
    });
    }
    }
    });

	app.directive('pieChart', function ($timeout) {
	  return {
		restrict: 'EA',
		scope: {
		  title:    '@title',
		  width:    '@width',
		  height:   '@height',
		  data:     '=data',
		  selectFn: '&select',
		  hoverFn: '&hover'
		},
		link: function($scope, $elm, $attr) {
			google.load('visualization', '1', { packages: ['corechart'], 'callback': function() {
			
		  
		  // Create the data table and instantiate the chart
		  var data = new google.visualization.DataTable();
		  data.addColumn('string', 'Label');
		  data.addColumn('number', 'Value');
		  var chart = new google.visualization.PieChart($elm[0]);

		  draw();
		  
		  // Watches, to refresh the chart when its data, title or dimensions change
		  $scope.$watch('data', function() {
			draw();
		  }, true); // true is for deep object equality checking
		  $scope.$watch('title', function() {
			draw();
		  });
		  $scope.$watch('width', function() {
			draw();
		  });
		  $scope.$watch('height', function() {
			draw();
		  });

		  // Chart selection handler
		  google.visualization.events.addListener(chart, 'select', function () {
			var selectedItem = chart.getSelection()[0];
			if (selectedItem) {
			  $scope.$apply(function () {
				$scope.selectFn({selectedRowIndex: selectedItem.row});
			  });
			}
		  });
			google.visualization.events.addListener(chart, 'onmouseover', function (e) {
				$scope.$apply(function () {
					$scope.hoverFn({hoveredRowIndex: e.row});
				});
			});
			google.visualization.events.addListener(chart, 'onmouseout', function (e) {
				$scope.$apply(function () {
					$scope.hoverFn({hoveredRowIndex: null});
				});
			});
			
			  function draw() {
				if (!draw.triggered) {
				  draw.triggered = true;
				  $timeout(function () {
					draw.triggered = false;
					var label, value;
					data.removeRows(0, data.getNumberOfRows());
					angular.forEach($scope.data, function(row) {
					  label = row[0];
					  value = parseFloat(row[1], 10);
					  if (!isNaN(value)) {
						data.addRow([row[0], value]);
					  }
					});
					var options = {'title': $scope.title,
								   'width': $scope.width,
								   'height': $scope.height};
					chart.draw(data, options);
					// No raw selected
					$scope.selectFn({selectedRowIndex: undefined});
				  }, 0, true);
				}
			  }
			} });
		}
	  };
	  
	  
	});


	app.directive('help', function ($timeout) {
	  return {
		restrict: 'E',
		scope: {
		  title:        '@title',
		  placement:    '@placement',
		  height:   '@height',
		  data:     '=data',
		  selectFn: '&select',
		  hoverFn: '&hover'
		},
		link: function($scope, $elm, $attr) {
			var html = $('<a href="javascript:;"><i class="glyphicon glyphicon-question-sign"></i></a>');
			var content = $elm.html();
			$elm.replaceWith(html);

			html.popover({
				html: true,
				placement: $attr.placement || 'bottom',
				trigger: 'click',
				//delay: {hide: '300'},
				content: content,
				title: $attr.title,
				container: 'body'
			});
		}
	  };
	});

    app.controller('IndexCtrl', function ($scope, $rootScope) {
        $rootScope.breadcrumbs = [
            {
                'title' : 'Dashboard',
                'url': '#!/'
            }
        ];
    });
    app.controller('SettingsCtrl', function ($scope, $rootScope) {
        $rootScope.breadcrumbs = [
            {
                'title' : 'Dashboard',
                'url': '#!/'
            },
            {
                'title' : 'Settings',
                'url': '#!/settings'
            }
        ];
    });
    app.controller('MenuCtrl', function ($scope, $location, $session, $window) {
        $(window).bind('resize', function(){
            $scope.wHeight = $(window).innerHeight();
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        }).trigger('resize');

        $scope.more = { width: 0, menu: [], show: false };
        $scope.menu = [
            {
                title: 'Галерея',
                url: '#!/gallery',
                icon: 'ico-picture'
            },
            {
                title: 'News',
                url: '#!/news',
                icon: 'ico-edit'
            },
            {
                title: 'Menu',
                url: '#!/menu',
                icon: 'ico-reorder'
            },
            {
                title: 'Themes',
                url: '#!/themes',
                icon: 'ico-leaf'
            },
            {
                title: 'Files',
                url: '#!/files',
                icon: 'ico-file'
            }/*,
            {
                title: 'Notification',
                icon: 'ico-fighter-jet',
                notification: 6
            },
            {
                title: 'Setting',
                icon: 'ico-cogs'
            },
            {
                title: 'Меню',
                icon: 'ico-reorder'
            },
            {
                title: 'Help',
                icon: 'ico-question-sign'
            },
            {
                title: 'User',
                icon: 'ico-group'
            },
            {
                title: 'Report',
                icon: 'ico-bullhorn'
            }*/
        ];
        $scope.sideMenu = $scope.menu;

        $scope.$watch('wHeight', function(value) {
            var height = 62 * 2 + 40, i = 0, menu = [];
            var sidebar = $('.sidebar');

            if ($(window).innerWidth() <= 768) {
                $scope.sideMenu = $scope.menu;
                $scope.more.menu = [];
                $scope.more.show = false;
                return;
            }

            do {
                height += 62;
                menu.push($scope.menu[i++]);
                if ($scope.menu.length == menu.length) {
                    break;
                }
            } while (height < sidebar.height())
            $scope.sideMenu = menu;
            menu = [];
            for (var j = i, max = $scope.menu.length, width = 0; j < max; j++, width += 60) {
                menu.push($scope.menu[j]);
            }
            $scope.more.width = width;
            $scope.more.menu = menu;
        });
    })


    angular.bootstrap(document, ['bazalt-cms']);

});