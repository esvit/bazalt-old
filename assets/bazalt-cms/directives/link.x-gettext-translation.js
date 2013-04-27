bazaltCMS.directive('link', ['$rootScope',function($rootScope) {
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
}]);