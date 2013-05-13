app.views.comment = Backbone.View.extend({

    events:             {
        'click .comment-reply':  'showReply',
        'click .delete':         'deleteComment',
        'click .restore':        'restoreComment'
    },

    form:               null,

    initialize: function(options) {
        _.bindAll(this, 'render', 'showReply', 'markAsDeleted');

        this.model.bind('change', this.render);

        var formEl = this.$el.children('.reply-form');

        if (formEl.size() == 0) {
            formEl = $('<li></li>').addClass('reply-form');
            this.$el.children('.comments').prepend(formEl);
        }
        this.form = new app.views.commentForm({
            el:             formEl,
            collection:     this.collection,
            model:          this.model
        });
    },

    deleteComment: function(e) {
        var itemId = $(e.currentTarget).data('itemId')
          , commentId = $(e.currentTarget).data('commentId')
          , collection = this.collection;

        ComNewsChannel_Webservice_Comments.Delete(itemId, commentId, true, function(ids) {
            collection.each(function(comment) {
                if (_.include(ids, '' + comment.get('id'))) {
                    comment.view.markAsDeleted();
                }
            });
        });
        return false;
    },

    restoreComment: function(e) {
        var itemId = $(e.currentTarget).data('itemId')
          , commentId = $(e.currentTarget).data('commentId')
          , collection = this.collection;

        ComNewsChannel_Webservice_Comments.Delete(itemId, commentId, false, function(ids) {
            collection.each(function(comment) {
                if (_.include(ids, '' + comment.get('id'))) {
                    comment.view.removeDeletedMark();
                }
            });
        });
        return false;
    },

    markAsDeleted: function() {
        this.$el.find('.message').addClass('deleted');
    },

    removeDeletedMark: function() {
        this.$el.find('.message').removeClass('deleted');
    },

    highlight: function() {
        var el = $('#comment' + this.model.get('id') + ' .message').effect('highlight', {}, 500);

        var curPos = $(document).scrollTop();
        $('html, body').animate({ 'scrollTop': el.position().top - 200 }, curPos / 4);
    },

    showReply: function(e) {
        this.form.showForm();
        this.form.focus();
        
        return false;
    },

    render: function() {
        $('.lft:first', this.$el).html(this.model.get('lft'));
        $('.rgt:first', this.$el).html(this.model.get('rgt'));
        return this;
    }
});