app.collections.comments = Backbone.Collection.extend({
    model: app.models.comment,

    defaults: {
        is_moderated:     false
    },

    initialize: function() {
        this.bind('add', this.updateLftRgt);
    },

    updateLftRgt: function(item) {
        var itemLft   = parseInt(item.get('lft'))
          , itemRgt   = parseInt(item.get('rgt'))
          , itemId    = parseInt(item.get('id'));

        this.each(function(comment) {
            var commentLft = parseInt(comment.get('lft'))
              , commentRgt = parseInt(comment.get('rgt'))
              , commentId  = parseInt(comment.get('id'));
            if (itemId == commentId) {
                return;
            }
            
            if (commentRgt > itemRgt - 2) {
                comment.set('rgt', commentRgt + 2);

                if (commentLft > itemLft - 1) {
                    comment.set('lft', commentLft + 2);
                }
            }
        });
    },

    dump: function() {
        console.info(' --- ');
        this.each(function(comment) {
            console.info(comment.id, ': ', comment.get('lft'), ' - ', comment.get('rgt'));
        });
    },

    getParent: function(comment) {
        var parent = comment.get('parent');

        if (!parent) {
            parent = this.find(function(item){
                var parentLft = parseInt(item.get('lft'))
                  , parentRgt = parseInt(item.get('rgt'))
                  , itemLft   = parseInt(comment.get('lft'))
                  , itemRgt   = parseInt(comment.get('rgt'));

                return (parentRgt > itemLft) && (parentRgt > itemRgt)
                    && (parentLft < itemRgt) && (parentLft < itemLft)
                    && (parseInt(item.get('depth')) == parseInt(comment.get('depth')) - 1); // батківський вузол має глубину на 1 меншу
            });
            comment.set('parent', parent);
        }
        return parent;
    }
});