$(function() {
    var loginForm = $('.bz-form'),
        loginAndPassword = $('#login, #password', loginForm),
        submitButton = $('#signin_submit', loginForm),
        emptyFields = loginAndPassword;

    // focus in first input
    $('input:first', loginForm).focus();

    loginAndPassword.bind('input blur cut copy paste keypress', function () {
        emptyFields = $();
        loginAndPassword.each(function() {
            var el = $(this);
            if ($.trim(el.val()) == '') emptyFields = emptyFields.add(this);
        });
        if (emptyFields.size() > 0) {
            submitButton.attr('disabled', 'disabled');
        } else {
            submitButton.removeAttr('disabled');
        }
    }).keyup(function(e){
        e.preventDefault();
        if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
            if (emptyFields.size() > 0) {
                emptyFields.focus();
            } else {
                submitButton.click();
            }
        }
    });

    submitButton.click(function() {
        if (submitButton.attr('disabled') == 'disabled' || submitButton.hasClass('disabled')) {
            return false;
        }
        loginAndPassword.attr('readonly', 'readonly');

        submitButton.focus().button('loading');

        loginForm.submit();
        return false;
    });

    loginAndPassword.blur();
});