(function () {
    CKEDITOR.plugins.add('bazalt-cms', {init:function (editor) {
        editor.addCommand('bazalt-image', {
            exec:function (editor) {
                bazaltCMS.editor.insertImage(editor);
            },
            canUndo : true
        });
        editor.ui.addButton && editor.ui.addButton('bazalt-image', {
            label:'File manager',
            command:'bazalt-image',
            icon: this.path + 'filemanager.png'
        });
    }});
    $('<style></style>').html('.cke_button__bazalt-image,.cke_button__bazalt-image_label {display: inline}').appendTo('body');
})();