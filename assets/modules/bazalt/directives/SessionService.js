    bazaltCMS.factory('$session', function($rootScope, $resource, $location, AuthService, $route) {
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
    });