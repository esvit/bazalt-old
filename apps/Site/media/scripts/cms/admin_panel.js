$(function() {

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

    $('#cms-show-manage-widgets').click(function() {
        $('body').toggleClass('cms-manage-widgets');

        var el = $(this).parent();
        el.toggleClass('bz-adminpanel-active');

        var enable = el.hasClass('bz-adminpanel-active');
        if (!enable) {
            $('.cms-widgets-settings').hide();
        }
        $.cookie('cms-show-manage-widgets', enable, { path: '/' });
    });

});