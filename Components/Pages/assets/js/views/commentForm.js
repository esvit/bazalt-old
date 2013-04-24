app.views.commentForm = Backbone.View.extend({

    formTemplate:    _.template($('#comment-form').html()),

    events:             {
        'click .add':   'postComment',
        'keypress .body':  'keyPress',
        'keypress .name':  'keyPress'
    },

    initialize: function(options) {
        _.bindAll(this, 'render', 'postComment', 'keyPress')
    },

    keyPress: function(e) {
        var keyCode = (e.which ? e.which : e.keyCode);          
        
        if (keyCode === 10 || keyCode == 13 && e.ctrlKey) {
            this.postComment(e);
            return false;
        }

        return true;
    },

    postComment: function(e) {
        var self = this
          , addButton = this.$el.find('.add')
          , name = $('.name', this.el).val()
          , body = $('.body', this.el)
          , bodyText = body.val()
          , itemId = addButton.data('itemId')
          , parentId = addButton.data('parentId');

        body.val('');
        name = $.trim(name);
        bodyText = $.trim(bodyText);
        if (name == '' || bodyText == '') {
            alert('Заповніть усі поля форми');
            return false;
        }

        $.cookie('commentUserName', name);
        addButton.button('loading')
                 .attr('disabled', true);
        ComNewsChannel_Webservice_Comments.postComment(parentId, itemId, name, bodyText, function(comment) {
            addButton.button('reset')
                     .removeAttr('disabled');
            self.collection.add(comment);
            self.hideForm();
        }, function() {
            addButton.button('reset')
                     .removeAttr('disabled');
            alert('Відбулась помилка');
        });
        return false;
    },

    showForm: function() {
        var name = $.cookie('commentUserName');

        if ($('.name', this.$el).val() == '') {
            $('.name', this.$el).val(name);
        }

        this.$el.slideToggle();
        $('.reply-form').not(this.$el).slideUp();
    },

    hideForm: function() {
        if (this.$el.hasClass('reply-form')) {
            this.$el.slideUp();
        }
    },

    focus: function() {
        var name = $('.name', this.$el)
          , body = $('.body', this.$el);

        if (name.val() != '') {
            body.focus();
        } else {
            name.focus();
        }
    },

    render: function() {
        var attrs = { id: -1 };
        if (this.model) {
            attrs.id = this.model.get('id');
        }
        this.$el.html(this.formTemplate(attrs)).hide();
        
        $('textarea', this.el).elastic();

        return this;
    }
});