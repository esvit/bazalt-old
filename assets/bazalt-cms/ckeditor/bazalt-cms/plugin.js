(function () {
    CKEDITOR.plugins.add('bazalt-cms', {init:function (editor) {
        editor.addCommand('bazalt-image', {
            exec:function (editor) {
                angular.module('bazalt-cms').editorInsertImage(editor);
            }
        });
        editor.ui.addButton && editor.ui.addButton('bazalt-image', {label:'Insert image', command:'bazalt-image', icon:this.path + 'newplugin.png'});
    }});
})();