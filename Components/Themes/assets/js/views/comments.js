app.views.comments = Backbone.View.extend({

    commentTemplate:    _.template($('#comment-template').html()),

    commentsTemplate:   _.template($('#comments-template').html()),

    events:             {
    },

    highlightNew:       false,

    form:               null,

    initialize: function(options) {
        _.bindAll(this, 'render', 'addComment');

        var self = this;
        this.collection.bind('add', this.addComment);
        this.collection.comparator = function(comment) {
            return parseInt(comment.get('lft'));
        };

        this.collection.sort();
        this.collection.each(function(comment) {
            self.addComment(comment);
        });
        this.highlightNew = true;
        this.render();
    },

    addComment: function(comment) {
        this.collection.sort();

        var el = $('#comment' + comment.get('id'))
          , parent = this.collection.getParent(comment)
          , container = $(this.commentsTemplate());

        if (el.size() == 0) {
            el = $(this.commentTemplate(comment.toJSON()));

            if (parent) {
                parent.view.$el.children('.comments').append(el);
            } else {
                this.form.$el.before(el);
            }
        }
        comment.view = new app.views.comment({
            model:      comment,
            el:         el,
            collection: this.collection
        });
        if (this.highlightNew) {
            comment.view.highlight();
        }
        comment.view.form.render();
    },

    render: function() {
        if (this.form == null) {
            var newEl = $('<li></li>').addClass('comment-form');
            this.$el.append(newEl);

            this.form = new app.views.commentForm({
                el:             newEl.get(0),
                collection:     this.collection
            });
            this.form.render();
            this.form.showForm();
        }
        return this;
    }
});