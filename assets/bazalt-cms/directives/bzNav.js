bazaltCMS
.value('$page', {
    'title': null,
    'keywords': null,
    'description': null
})
.directive('title', ['$page', '$rootScope', function($page, $rootScope) {
    return {
        restrict: 'E',
        terminal: true,
        compile: function(element, attr) {
            if ($page.title != element.text) {
                $page.title = element.text();
                $rootScope.$emit('$pageChangeTitle', $page.title);
            }
        }
    };
}])
.directive('meta', ['$page', function($page) {
    return {
        restrict: 'E',
        terminal: true,
        compile: function(element, attr) {
            if (attr.name) {
                $page[attr.name] = attr.content;
            }
        }
    };
}]);