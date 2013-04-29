define(['angular/angular-cookies.min', 'jquery-ui/jquery-ui.min'], function() {



    // SEO
    $('#show-seo-link').click(function() {
        $('#comseo-content-for-page').hide();
        $('#show-seo-content').slideToggle('fast');
        return false;
    });
    $('#show-seo-link-page').click(function() {
        $('#show-seo-content').hide();
        $('#comseo-content-for-page').slideToggle('fast');
        return false;
    });
    
    
    
    $('#cms_overlay .btn-cancel').click(function() {
        $('#cms_overlay').removeClass('show');
    });
    $('.btn-settings').on('click', function() {
        //var el = $(this).parents('.cms-widget-container');
        /*$('#' + el.attr('id') + '-settings').dialog("open");//.toggle();
        
        return false;*/
         var el = $(this),
             target = el.data('id'),
             overlay = $('#cms_overlay').addClass('show');
             settingsForm = $('.cms_dialog .cms_dialog-content', overlay).empty();

             settingsForm.data('id', target);
             ComPanel_Webservice_Widget.getWidgetSettings(target, {
                success: function(res) {
                    settingsForm.html(res);
                }
             });
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
    $('#cms_overlay .cms_dialog .btn-apply').click(function(){
        var btn = $(this),
            form = btn.closest('.cms_dialog').find('.cms_dialog-content'),
            id = form.data('id'),
            settings = form.serializeArray();

            btn.attr('disabled', 'disabled')
               .addClass('disabled')
               .data('text', btn.text())
               .text('Loading...');
            ComPanel_Webservice_Widget.saveWidgetSettings(id, settings, function(res) {
                $('#bz-widget-' + id).replaceWith(res.html);
                btn.removeAttr('disabled')
                   .removeClass('disabled')
                   .text(btn.data('text'));
                $('#cms_overlay').removeClass('show');
            });
    });
});