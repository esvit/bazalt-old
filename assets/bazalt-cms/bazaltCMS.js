var bazaltCMS = angular.module('bazalt-cms', ['ngResource']).
    config(['$interpolateProvider', function($interpolateProvider){
        $interpolateProvider.startSymbol('{[').endSymbol(']}');
    }]);

bazaltCMS.editorInsertImage = function(editor) {
    $('#elfinder').show();
    editor.insertHtml('The current date and time is: <em>rqrqwrqw</em>');
};
bazaltCMS.editorInsertImageCallback = function() {
    $('#elfinder').show();
    editor.insertHtml('The current date and time is: <em>rqrqwrqw</em>');
};
bazaltCMS.controller('bazaltGlobalController', ['$scope','$rootScope', 'languages', '$filter', '$compile',function($scope, $rootScope, languages, $filter, $compile) {
    $rootScope.languages = languages;

    var html = '<link rel="stylesheet" type="text/css" href="/less.php/Components/Files/assets/less/elfinder.less" media="all"><div id="elfinder" select="selectImage()" class="modal fade in" style="z-index: 400001; display: none"> \
                                    <div class="modal-dialog"> \
                                        <div class="modal-content"> \
                                            <div class="modal-header"> \
                                                <button type="button" class="close" ng-click="close()" data-dismiss="modal" aria-hidden="true">×</button> \
                                                ' + $filter('translate')('Select file', 'Files') + ' \
                                            </div> \
                                            <div class="modal-body el-finder" select="selectFile()" ></div> \
                                        </div> \
                                    </div> \
                                </div>';
    $scope.selectImage = function() {

    }
    $('body').append(html);
    $compile($('#elfinder'))($scope);
}]);