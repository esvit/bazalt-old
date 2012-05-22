$(function(){
    $('.cms-widgets-border-around').sortable({
        revert: true,
        scroll: true,
        opacity: 0.5,
        handle: '.cms-widget-title-text',
        placeholder: 'cms-widget-placeholder',
        connectWith: '.cms-widgets-border-around',
        update: function(event, ui) {
            var el = $(this);
            var order = el.sortable('serialize');
            var template = el.data('template');
            var position = el.data('position');

            Site_Webservice_Widget.changePosition(template, position, order);
        }
    });

    $('.cms-widgets-add-widget').click(function(){
        var el = $(this);
        var template = el.attr('template');
        var position = el.attr('position');
        var cont = $('#widgetsSelector');

        cont.widgetsSelectorDialog('option', 'template', template); // set template
        cont.widgetsSelectorDialog('option', 'position', position); // set position
        cont.widgetsSelectorDialog('option', 'element', el);        // set widgets container
        
        cont.widgetsSelectorDialog('open');
    });
    $('.btn-save-widget').live('click', function(){
        var btn = $(this);
        var form = $(this).parent();
        var loading = $('.loading', form);
        form.ajaxSubmit(function(res){
            form.parents('.cms-widget-container').find('.cms-widget').html(res.result);
            btn.show();
            loading.hide();
        });
        btn.hide();
        loading.show();
        return false;
    });
    $('.btn-delete').live('click', function(){
        var el = $(this).parents('.cms-widget-container');
        var id = el.attr('id').slice(11);
        if (confirm('Are you sure want delete widget?')) {
            Site_Webservice_Widget.deleteInstance(id);
            el.hide('slow', function() {
                el.remove();
            });
        }
        return false;
    });

    $.fn.cmsBazaltWidgetSettings = function() {
        var el = $(this).find('.cms-widgets-settings');

        return el.dialog({
            title: 'Widget settings',
            autoOpen: false,
            modal : true,
            width:  500,
            height: 400,
            buttons: [
                {
                    text: "Close",
                    click: function() { $(this).dialog("close"); }
                },
                {
                    text: "Apply",
                    click: function() {
                        var settingsForm = $(this);
                        var widgetId = settingsForm.attr('rel');
                        var btn = settingsForm.parent().find('button:contains("Apply")');
                        var form = settingsForm.find('form');
                        var loading = $('<div class="loading" style="display: none; float:left; margin:15px;"><div class="ui-loading-icon"></div> Loading ...</div>').show();
                        settingsForm.parent().find('button:contains("Close")').before(loading);
                        btn.attr('disabled', 'disabled').addClass('ui-state-disabled');

                        Site_Webservice_Widget.saveWidgetSettings(widgetId, form.serializeArray(), function(res) {
                            $('#cms-widget-' + widgetId + ' .cms-widget').html(res.html);
                            btn.removeAttr('disabled').removeClass('ui-state-disabled');
                            loading.remove();
                        });
                        return false;
                    }
                }
            ]
        });
    }
    
    $('#widgetsSelector').widgetsSelectorDialog();
    $('.cms-widget-container').cmsBazaltWidgetSettings();
    

    /*$(".cms-widget-container").live("mouseover", function(){    
        $(this).makeoverlay("#cms_plugin_overlay");
    });
    $("#cms_plugin_overlay").mouseenter(function(){
        $("#cms_plugin_overlay").show();
    }).mouseleave(function(){
        $("#cms_plugin_overlay").hide();
    });
    
    $.fn.makeoverlay = function(options){
        var pluginH = $(this).height();
        var pluginHmin = 10;
        if (pluginH < pluginHmin) {
            pluginH = pluginHmin
        }
        $(options).show();
        $(options).css({
            'width'        : $(this).width() + 'px',
            'height'    : pluginH +'px',
            'left'        : $(this).offset().left + 'px',
            'top'        : $(this).offset().top - 28 + 'px'
        });
    }*/
    
    
    $('.btn-settings').live('click', function() {
        var el = $(this).parents('.cms-widget-container');
        $('#' + el.attr('id') + '-settings').dialog("open");//.toggle();
        return false;
    });
    
    $('.cms-widgets-settings .add').click(function(){
        $('.tplTitle', $(this).parent().parent()).parent().show();
        $('.editTemplate', $(this).parent().parent()).show();
    });
    $('.widget-templates').change(function(){
        $('.cms-widgets-settings .edit').hide();
        if($(this).find('option:selected').parent().attr('rel') == 'custom') {
            $(this).parent().find('.edit').show();
        }
    });
    $('.cms-widgets-settings .edit').click(function(){
        var form = $(this).parent();
        var cont = $('.editTemplate', form.parent());
        cont.show();
        $('.tplName', cont).val(form.find('.widget-templates').val());
        $('.tplTitle', cont).parent().hide();
        Site_Webservice_Widget.getCustomTemplateContent(form.find('.widget-templates').val(), function(res){
            $('.tplContent', cont).val(res);
        });
        // console.log($(this));
        // $('.editTemplate', $(this).parent().parent()).show();
    });
    $('.cms-widgets-settings .save').click(function(){
        var cont = $(this).parent();
        if($('.tplName', cont).val() != '' && $('.tplTitle', cont).val() != '') {
            Site_Webservice_Widget.createCustomTemplate(
                $('.widgetId', cont).val(), 
                $('.tplName', cont).val(),
                $('.tplTitle', cont).val(),
                $('.tplContent', cont).val(),
                function(r){
                    $('.tplName', cont).val('');
                    $('.tplTitle', cont).val('');
                    $('.tplContent', cont).val('');
                    $('.editTemplate', cont).hide();
                }
            );
        } else {
            Site_Webservice_Widget.saveCustomTemplate(
                $('.tplName', cont).val(), 
                $('.tplContent', cont).val(),
                function(r){
                    $('.tplName', cont).val('');
                    $('.tplContent', cont).val('');
                    $('.editTemplate', cont).hide();
                }
            );
        }
    });
});