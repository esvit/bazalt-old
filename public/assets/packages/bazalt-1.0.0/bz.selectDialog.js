$.widget('bz.selectDialog', $.bz.bazaltDialog, {
    options: {
        panelButtons: [
            {
                text: 'Update',
                click: function(dlg) { dlg.updateList(1); return false; }
            }
        ],
        item: null,  // item
        element: null,  // container
        autoOpen: false,
        height: 400,
        width: 500,
        modal: true,
        resizable: false,
        buttons: {
            'Cancel': function() {
                $(this).selectDialog('close');
            }
        },
        onPageChange: function(e, ui) {
            ui.dialog.updateList(ui.page);
        }
    },

    _create: function() {
        $.bz.bazaltDialog.prototype._create.call(this);
        this.element.parent().addClass('bz-selectdialog');
    },

    selectItem: function() {
        var item = $('.ui-state-active', this.element);
        this.onSelect(item);
    },

    /**
     * Add widget to selected position
     */
    onSelect: function(item) {
    },

    /**
     * Update widgets list
     */
    updateList: function(page) {
    },

    /**
     * On open dialog
     */
    open: function() {
        $.ui.dialog.prototype.open.call(this);
    }
});