$(function () {
    var show = false

    var to_top_button = $('.to_top');

    $('.to_top_panel', to_top_button).click(function () {
        $.scrollTo($('body'), 500, { axis:'y' });
    })

    $(window).scroll(function () {
        show_or_hide()
    })

    function show_or_hide() {
        if (window.pageYOffset > 400) {
            if (!show) {
                to_top_button.show()
                show = true
            }
        } else {
            if (show) {
                to_top_button.hide()
                show = false
            }
        }
    }

    show_or_hide()
});