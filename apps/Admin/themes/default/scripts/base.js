Function.prototype.extend = function(Parent) {
    if (typeof Parent != 'function') {
        throw new Error('Second argument of "extend" must be function');
        return;
    }
    var F = function() { };
    F.prototype = Parent.prototype;
    this.prototype = new F();
    this.prototype.constructor = this;
    this.superclass = Parent.prototype;
}

jQuery.fn.log = function (msg) {
    if (typeof(console) != 'undefined' && console != null) {
        console.log("%s: %o", msg, this);
    }
    return this;
};

var bazalt = new function() {
    var self = this;

    this.loadingLayer = null;

    this.loadingText = 'Loading...';

    this.bindingKeys = [];

    this.countPerPage = 25;

    this.ajaxing = false;

    this.onError = function(msg, url, lineNo) {
        if (typeof(console) == 'undefined' || console == null) {
            alert(
                'JavaScript error occurred! \n'
                + 'The error was handled by '
                + 'a customized error handler.\n'
                + '\nError description: \t' + msg
                + '\nScript address:    \t' + url
                + '\nLine number:       \t' + lineNo
            );
            return true;
        }
    };

    this.onLanguageChange = function(alias) {
        ComI18N_Webservice_I18N.setLocale(alias, function(lang) {
            $('#bz-current-language').val(lang.data.alias);
            $('body').trigger('bazalt_language_change', [lang.data]);
        });
    }

    /**
     * Повертає поточну мову сайт
     */
    this.getCurrentLanguage = function() {
        return $('#bz-current-language').val();
    }

    this.getScrollTop = function() {
        return document.body.scrollTop  || document.documentElement.scrollTop;
    };

    this.scrollHandler = function() {
        $('.bz-admin-loading').css('top', self.getScrollTop());
    };

    /**
     * Show preloader
     */
    this.startLoading = function() {
        var loadingLayer = $('.bz-admin-loading');
        loadingLayer.show();

        if (loadingLayer.css('position') == 'absolute') {
            $(window).scroll(self.scrollHandler);
            self.scrollHandler();
        }

        self.ajaxing = true;

        window.onbeforeunload = function() {
            if (typeof(self.ajaxing) != 'undefined' && self.ajaxing) {
                return "Page elements are still loading!";
            }
        };
    };

    /**
     * Hide preloader
     */
    this.stopLoading = function() {
        var loadingLayer = $('.bz-admin-loading');
        loadingLayer.hide();
        $(window).unbind('scroll', self.scrollHandler);

        self.ajaxing = false;
    };

    this.showMetaContent = function(id) {
        var links = $('.adm-meta .adm-meta-links .adm-meta-link:not(.active)');
        if(!$('#' + id).hasClass('adm-meta-content-open')) {
            links.css('visibility', 'hidden')
        }
        $('#' + id).slideToggle('fast', function() {
            if ($(this).hasClass('adm-meta-content-open')) {
                links.css('visibility', '');
                $(this).removeClass('adm-meta-content-open');
            } else {
                $(this).addClass('adm-meta-content-open');
            }
        });
    }

    this.hideAllMetaContent = function() {
        $('.adm-meta-link').css('visibility', '')
                           .removeClass('active');
        var contents = $('.adm-meta-content-open');
        contents.slideUp('fast', function() {
            $(this).removeClass('adm-meta-content-open');
        });
    }

    /**
     * Init admin layout controls
     */
    this.initLayout = function() {
        // toggle left menu
        $('.separator').click(function() {
            if ($('body').toggleClass('folded').hasClass('folded')) {
                $('#adminmenu .submenu').removeClass('sub-open');
            }
            $.cookie('AdminMenu_Folded', ($('body').hasClass('folded') ? '1' : '0'), { expires: 30, path: '/' });
            return false;
        });

        $('.menu-toggle').click(function() {
            var parent = $(this).parent();
            if (parent.hasClass('menu-has-submenu-active')) {
                return false;
            }
            parent.toggleClass('menu-preopen');
            parent.find('.submenu').slideToggle(200, function() { $(this).removeAttr('style'); parent.toggleClass('menu-open'); });
            return false;
        });

        $('#adminmenu li').hover(function() {
            $(this).find('.submenu').toggleClass('sub-open');
            return false;
        });

        // tooltips
        $('[rel="tooltip"]').tooltip({
            animation: true
        });

        // checkboxes
        if ($.browser.msie) {
            $('.iphone label').click(function() {
                var el = $(this);
                el.toggleClass('checked');
                el.parent()
                  .find('input')
                  .click()
                  .change();
                console.info(el.parent().find('input').is(':checked'));
            });
        } else {
            $('.iphone label').removeClass('checked');
        }
        // change language
        /*$('#bz_languages').selectmenu({
            icons: [{ find: '.ico-flag', appendIcon: 'ico-flags' }]
        }).change(function() {
            var alias = $(this).val();
            self.onLanguageChange(alias);
        });*/

        $('.adm-meta .adm-meta-links .adm-meta-link')
            .addClass('ui-state-default ui-priority-secondary ui-corner-bottom')
            .click(function() {
                var el = $(this);
                var id = el.attr('href').substring(1);
                if(!$('#' + id).hasClass('adm-meta-content-open')) {
                    self.hideAllMetaContent();
                }
                el.toggleClass('active');
                self.showMetaContent(id);
                return false;
            });
    };

    this.bindKey = function(key, func) {
        $(document).bind('keydown', key, function(evt) {
            if ($.browser.msie) {
                evt.keyCode = 0;
                evt.returnValue = false;
                evt.cancelBubble = true;
            } else {
                evt.stopPropagation();
                evt.preventDefault();
            }
            func();
            $('body').removeClass('ui-show-hotkey');
        });
    };
    this.alert = function(message, title) {
        if (typeof title == 'undefined') {
            title = 'Alert';
        }
        var dialog = $('<div id="dialog-confirm" class="ui-helper-hidden" title="' + title + '">' + 
                       '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' + message + '</p>' + 
                       '</div>');
        dialog.dialog({
            resizable: false,
            height: 150,
            modal: true,
            buttons: {
                'OK': function() { $(this).dialog('close'); }
            },
            open: function(event, ui) {
                $('.ui-dialog-buttonpane').parent().addClass('dialog-confirm');
                $('.ui-dialog-buttonpane').find('button:contains("OK")')
                                          .prepend('<span style="float:left;" class="ui-icon ui-icon-trash"></span>');
            }
        });
    };
    this.confirm = function(message, title, onSuccess, onCancel) {
        var dialog = $('<div id="dialog-confirm" class="ui-helper-hidden" title="' + title + '">' + 
                       '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' + message + '</p>' + 
                       '</div>');

        if (onSuccess == undefined || onSuccess == null) {
            onSuccess = function() { };
        }
        if (onCancel == undefined || onCancel == null) {
            onCancel = function() { };
        }
        dialog.dialog({
            resizable: false,
            height: 150,
            modal: true,
            buttons: {
                'No': function() { onCancel(); $(this).dialog('close'); },
                'Yes': function() { onSuccess(); $(this).dialog('close'); }
            },
            open: function(event, ui) {
                $('.ui-dialog-buttonpane').parent().addClass('dialog-confirm');
                $('.ui-dialog-buttonpane').find('button:contains("Yes")')
                                          .addClass('ui-priority-primary')
                                          .prepend('<span style="float:left;" class="ui-icon ui-icon-check"></span>');
                $('.ui-dialog-buttonpane').find('button:contains("No")')
                                          .addClass('ui-priority-secondary')
                                          .prepend('<span style="float:left;" class="ui-icon ui-icon-cancel"></span>');
            }
        });
    };

    this.confirmDelete = function(message, title, onSuccess, onCancel) {
        var dialog = $('<div id="dialog-confirm" class="ui-helper-hidden" title="' + title + '">' + 
                       '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' + message + '</p>' + 
                       '</div>');

        if (onSuccess == undefined || onSuccess == null) {
            onSuccess = function() { };
        }
        if (onCancel == undefined || onCancel == null) {
            onCancel = function() { };
        }
        dialog.dialog({
            resizable: false,
            height: 150,
            modal: true,
            buttons: {
                'Cancel': function() { onCancel(); $(this).dialog('close'); },
                'Delete': function() { onSuccess(); $(this).dialog('close'); }
            },
            open: function(event, ui) {
                $('.ui-dialog-buttonpane').parent().addClass('dialog-confirm');
                $('.ui-dialog-buttonpane').find('button:contains("Delete")')
                                          .addClass('ui-state-error')
                                          .prepend('<span style="float:left;" class="ui-icon ui-icon-trash"></span>');
                $('.ui-dialog-buttonpane').find('button:contains("Cancel")')
                                          .prepend('<span style="float:left;" class="ui-icon ui-icon-cancel"></span>');
            }
        });
    };
    this.error = function(text, title) {
        if (typeof title == undefined || title == null) {
            title = 'Error';
        }
        $.pnotify({
            pnotify_title: title,
            pnotify_text: text,
            pnotify_type: 'error'
        });
    };
    window.onerror = this.onError;
}

$(function() {
    // Add preloader for ajax
    $(document).ajaxStart(function() { bazalt.startLoading(); })
               .ajaxStop(function() { bazalt.stopLoading(); });

    if(typeof BAZALTScriptService != 'undefined') {
        BAZALTScriptService.onBeforeSend = bazalt.startLoading;
        BAZALTScriptService.onAfterRecive = bazalt.stopLoading;

        BAZALTScriptService.onUnauthorizedAccess = function(result, context, method) {
            alert('Время сесии истекло! Залогиньтесь!');
            window.location = '/admin/';
        };

        BAZALTScriptService.onSuccess = function(result, context, method) {
            $('#errdlg').hide();
        }

        BAZALTScriptService.onFailure = function(result, context, method) {
            bazalt.error(result.message);
        }
    }

    bazalt.initLayout();

    $('.no-action').click( function() { return false; } );
});