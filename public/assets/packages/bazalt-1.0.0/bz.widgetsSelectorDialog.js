$.widget('bz.widgetsSelectorDialog', $.bz.selectDialog, {
    options: {
        template: null, // cms template
        position: null, // cms position
        widgets: null,  // cached widgets list
        element: null,  // widgets container
        autoOpen: false,
        height: 400,
        width: 500,
        modal: true,
        resizable: false,
        buttons: {
            'Cancel': function() {
                $(this).widgetsSelectorDialog('close');
            },
            'Add': function() {
                $(this).widgetsSelectorDialog('selectItem');
            }
        }
    },
    _create: function() {
        $.bz.selectDialog.prototype._create.call(this);
    },
    /**
     * Add widget to selected position
     */
    onSelect: function() {
        var self = this;
        var template = this.option('template');
        var position = this.option('position');

        var widg = $('.ui-state-active', this.element);
        if (widg.size() > 0) {
            var widgetId = widg.attr('rel');
            var el = this.option('element');
            var dlg = $(this.element).parent();

            this.lockDialog('Adding widget');

            Site_Webservice_Widget.createInstance(widgetId, template, 'widget_id=', position, 1, function(res){
                var html = $(res.html);
                html.appendTo(el.parent().parent());
                self.close();
                self.unlockDialog();

                var dlg = html.cmsBazaltWidgetSettings();
                if (dlg != null) {
                    dlg.dialog('open');
                }
            });
        }
    },
    /**
     * Update widgets list
     */
    updateList: function(page) {
        var self = this;
        var cont = $(this.element);
        this.lockDialog('Load widgets list');

        Site_Webservice_Widget.widgets(page, function(res){
            self.setPager(res.page, res.pagesCount)
            cont.widgetsSelectorDialog('option', 'widgets', res.data);

            cont.empty();
            $('#widgetTpl').tmpl({ widgets: res.data }).appendTo(cont);
            var li = cont.find('li');
            li.click(function() {
                li.removeClass('ui-state-active');
                $(this).addClass('ui-state-active');
            }).dblclick(function() {
                self.selectItem();
            });
            self.unlockDialog();
        });
    },

    /**
     * On open dialog
     */
    open: function() {
        var widgets = this.option('widgets');
        var cont = $(this.element);

        if (widgets == null) {
            this.updateList(1);
        }
        cont.find('li').removeClass('ui-state-active');
        $.ui.dialog.prototype.open.call(this);
    }
});