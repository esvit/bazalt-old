(function(widget) {

    var buttom = $('.bz-input-submit', widget.container),
        emailInput = $('.bz-input-mail', widget.container),
        subscribeForm = $('.b-subscribe-form', widget.container),
        errors = $('.bz-errors', widget.container);

    buttom.removeAttr('disabled');

    var onSuccess = function(result) {
        errors.empty();
        buttom.button('reset');
        if (result.error) {
            errors.addClass('alert-danger').show().html(result.error);
        } else {
            subscribeForm.hide();
            errors.addClass('alert-success').show().html(result.msg);
        }
    };

    buttom.click(function() {
        var email = $.trim(emailInput.val());
        buttom.button('loading');
        widget.rpc.subscribe(email, { success: onSuccess });
    });

}(widget));