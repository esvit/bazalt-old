var jsTreePreload = new function() {
    var self = this;

    this.ajaxing = false;
    this.loadingLayer = null;

    /**
     * Show preloader
     */
    this.startLoading = function() {
        self.ajaxing = true;
        if (self.loadingLayer == null) {
            $('#tree-container').after('<div class="jsTreePreload"></div>');
            self.loadingLayer = $('.jsTreePreload');
        }
        self.loadingLayer.show();
        /*
        .css({
            height: $(document).height()+'px'
        })*/
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
        if (self.loadingLayer != null) {
            self.loadingLayer.hide();
        }
        self.ajaxing = false;
    };
}

$(function() {
    // Add preloader for ajax
    $(document).ajaxStart(function() { jsTreePreload.startLoading(); })
               .ajaxStop(function() { jsTreePreload.stopLoading(); });
});