$.widget('bz.bazaltDialog', $.ui.dialog, {
    lockedDiv: [],
    paging: null,
    options: {
        panelButtons: []
    },
    _create: function() {
        var self = this;
        $.ui.dialog.prototype._create.call(this);

        var el = $(this.element).parent();
        var buttonPane = $('.ui-dialog-buttonpane', el);
        var closeButton = $('.ui-dialog-titlebar-close', el);

        var buttons = this.option('panelButtons');
        for (var i = 0; i < buttons.length; i++) {
            var b = buttons[i];
            var btn = $('<a href="#" class="ui-dialog-titlebar-close ui-corner-all" title="' + b.text + '" role="button"><span class="ui-icon ui-icon-refresh">' + b.text + '</span></a>');
            closeButton.before(btn);
            btn.css({
                right: '2.6em'
            })
            .hover(function() { $(this).toggleClass('ui-state-hover'); })
            .click(function() { b.click(self); });
        }
        
        this.paging = $('<span class="bz-paging"></span>');
        buttonPane.append(this.paging);
    },
    setPager: function(current, pageCount) {
        var self = this;
        var html = '';
        for (var i = 1; i < pageCount + 1; i++) {
            if (i == current) {
                html += '<span class="fg-button ui-button ui-state-default ui-state-disabled ui-corner-all">' + i + '</span>';
            } else {
                html += '<span class="fg-button ui-button ui-state-default ui-corner-all">' + i + '</span>';
            }
        }
        this.paging.html(html);
        $('span', this.paging).click(function() {
            self._trigger("onPageChange", null, { page: $(this).text(), dialog: self });
        });
    },
    lockDialog: function(text) {
        var dlg = $(this.element).parent();
        var loading = $('<div class="bz-widgetselector-loading-overlay"></div>');
        var loadingText = $('<div class="bz-widgetselector-loading-text">' + text + '</div><div class="bz-widgetselector-loading"></div>');
        loading.css({
            opacity: .5
        });
        loading.appendTo(dlg);
        loadingText.appendTo(dlg);
        
        this.lockedDiv.push(loading);
        this.lockedDiv.push(loadingText);
    },
    unlockDialog: function() {
        while (this.lockedDiv.length > 0) {
            var div = this.lockedDiv.pop();
            div.remove();
        }
    }
});