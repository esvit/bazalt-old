var bazaltCMS = angular.module('bazalt-cms', ['ngResource']).
    config(['$interpolateProvider', function($interpolateProvider){
        //$interpolateProvider.startSymbol('{[').endSymbol(']}');
    }]).
    run(['$rootScope', '$page', function($rootScope, $page) {
        $rootScope.page = $page;
    }]);

bazaltCMS.editor = new function() {
    var editor = {
        instance: null,
        insertImage: function(inst) {
            $('#elfinder').show();
            editor.instance = inst;
        },
        imageCallback: function(file) {
            var img = $('<img />').attr({
                width: file.width,
                height: file.height,
                src: file.url,
                'data-size': file.size,
                alt: file.name
            })
            editor.instance.insertHtml($('<div>').append(img).html());
        }
    };
    return editor;
};
bazaltCMS.factory('LanguageService', ['$resource', function($resource) {
    return $resource('/rest.php/app/language');
}]);
bazaltCMS.controller('bazaltGlobalController', ['$scope','$rootScope', 'languages', '$filter', '$compile',function($scope, $rootScope, languages, $filter, $compile) {
    $rootScope.languages = languages;

    var html = '<link rel="stylesheet" type="text/css" href="/less.php/Components/Files/assets/less/elfinder.less" media="all"><div id="elfinder" class="modal fade in" style="z-index: 400001; display: none"> \
                                    <div class="modal-dialog"> \
                                        <div class="modal-content"> \
                                            <div class="modal-header"> \
                                                <button type="button" class="close" ng-click="close()" data-dismiss="modal" aria-hidden="true">×</button> \
                                                ' + $filter('translate')('Select file', 'Files') + ' \
                                            </div> \
                                            <div class="modal-body el-finder" select="selectImage($file)"></div> \
                                        </div> \
                                    </div> \
                                </div>';
    $scope.selectImage = function(file) {
        bazaltCMS.editor.imageCallback(file);
    }
    $('body').append(html);
    $compile($('#elfinder'))($scope);
}]);