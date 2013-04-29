(function () {
    CKEDITOR.plugins.add('bazalt-cms', {init:function (editor) {
        editor.addCommand('bazalt-image', {
            exec:function (editor) {
                bazaltCMS.editor.insertImage(editor);
            }
        });
        editor.ui.addButton && editor.ui.addButton('bazalt-image', {label:'Insert image', command:'bazalt-image', icon:this.path + 'newplugin.png'});
    }});
})();