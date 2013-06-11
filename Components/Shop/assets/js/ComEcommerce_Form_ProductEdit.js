$.bz.elements.ComEcommerce_Form_ProductEdit = $.bz.elements.Html_Form.extend({

    typeId: null,

    productId: 0,

    initialize: function(options) {
        $.bz.elements.Html_Form.prototype.initialize.apply(this, arguments);

        this.typeId = this.elements.tabs.elements.tab1.elements.type_id;
        $(this.typeId.el).change(this.loadFields);

        if(options.productId) {
            related.init(options.productId);
        }
    },

    loadFields: function() {
        var fields = this.elements.tabs.elements.tab2.elements.fields;
        fields.GetFields($(this.typeId.el).val(), fields.productId, function(res){
            var id = $(fields.el).attr('id');
            $(fields.el).replaceWith(res);
            var table = $('#' + id);
            fields.el = table.get(0);
            $('.field-active-chk').change(function() {
                $(this).parents('.bz-fields-row:first').toggleClass('field-not-active');
            });
        });
    }
});

var related = {
    id : null,
    list: null,
    init: function (id) {
        var self = this;
        $(this.search_button).bind('click', function(){
            self.search();
            return false;
        });
        self.id = id;
        self.list = $("#related-list");
        self.reloadlist();
    },
    reloadlist : function(){
        var self = this;
        ComEcommerce_Webservice_Related.getRelatedList(self.id, function(res){
            $(self.list).html(res);

        });
    },
    delete : function(id){
        var self = this;
        ComEcommerce_Webservice_Related.removeRelatedList(self.id, id);
        self.reloadlist();
    },
    search : function (){
        var self = this;
        ComEcommerce_Webservice_Related.search($("#product_tab4_search").val(), function (res){
            $("#related-search").html(res);
        });
    },
    relate : function (id){
        var self = this;
        ComEcommerce_Webservice_Related.relate(self.id, id, function(res){
            self.reloadlist();
        });

    }
}