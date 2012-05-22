<div id="tree-container">
</div>

<!--
<ul class="sortable">
    <li class="ui-state-default">
    <a href="#" class="item-title">Item 1</a><input style="display:none;width:250px;height:1.5em;line-height:1.2em;margin-top:-4px;" type="text" size="3" value="355"/>
    <a style="margin-top:-2px;float:right;" class="delete fg-button ui-state-default fg-button-icon-right ui-corner-all" href="#"><span class="ui-icon ui-icon-trash"></span>Delete</a>
    </li>
    <li class="ui-state-default">Item 2
        <ul class="sortable">
            <li class="ui-state-default">SubItem 1</li>
            <li class="ui-state-default">SubItem 2</li>
            <li class="ui-state-default">SubItem 3</li>
        </ul>
    </li>
    <li class="ui-state-default">Item 3</li>
    <li class="ui-state-default">Item 4</li>
    <li class="ui-state-default">Item 5</li>
    <li class="ui-state-default">Item 6</li>
    <li class="ui-state-default">Item 7</li>
</ul>
-->

{literal}

<style type="text/css">
    .sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    .sortable li {
        background-image:url("/admin/media/scripts/jquery_tree/themes/bazalt/grippy.png");
        background-position:left center;
        background-repeat:repeat-y;
        font-size:1.2em;
        height:1.5em;
        margin:0 5px 5px;
        padding:5px 5px 5px 10px;
    }
    html>body .sortable li { height: 1.5em; line-height: 1.2em; }
    .ui-state-highlight { height: 1.5em; line-height: 1.2em; }
</style>

<script type="text/javascript">
$(function() {
/*
    $(".sortable").sortable({
        placeholder: 'ui-state-highlight'
    });
    $(".sortable").disableSelection();
*/


    $('#tree-container').tree({
        data: {
            type: "json",
            opts: {
                static: [
                    { 
                        // the short format demo
                        //data : 'Bugagashechka<input style="width:250px;border:1px solid red;height:16px !important;line-height:16px;margin-top:-4px;" type="text" size="3" value="355"/><a style="float:right;width:10px;" class="delete fg-button ui-state-default fg-button-icon-right ui-corner-all" href="#"><span style="border:medium none;margin-top:-9px;right:1px;width:8px;" class="ui-icon ui-icon-trash"></span>&nbsp;</a>',
                        data : 'Bugagashechka',
                        // here are the children
                        children : [
                            { data : "Child node 1" },
                            { data : "Child node 2" },
                            { data : "Child node 3" }
                        ]
                    },
                    { 
                        // this is the long data format
                        data : 'The long data format'
                    }
                ]
            }
        },
        ui: {
            dots: true,
            theme_name: 'bazalt'
        },
        callback: {
            onparse: function(str,tree_obj) {
                //console.log(str);
                var html = $('<ul>' + str + '</ul>');
                var els = $('li', html);
                els.each(function(){
                    if($(this).find('.m_input').size() == 0) {                        
                        var a = $('a:first', this);
                        var txt = a.text();
                        a.addClass('m_a').after('<input class="m_input" style="display:none;width:250px;border:1px solid red;height:16px !important;line-height:16px;margin-top:-4px;" type="text" value="'+txt+'" size="3"/><a style="float:right;width:10px;" class="delete fg-button ui-state-default fg-button-icon-right ui-corner-all" href="#"><span style="border:medium none;margin-top:-9px;right:1px;width:8px;" class="ui-icon ui-icon-trash"></span>&nbsp;</a>');
                    }
                });
                //els.addClass('ui-state-default');
                //console.log(html.html());
                return html.html();
                //return str;
            },
            onload: function(tree_obj) {
                $('.m_a').click(function(event){
                    $('.m_input').blur();
                    var el = $(this);
                    el.hide();
                    var parentEl = el.parent();
                    parentEl.find('.m_input:first').val(el.text()).show().focus();
                });
                $('.m_input').blur(function(){
                    var el = $(this);
                    el.hide();
                    var parentEl = el.parent();                    
                    var linkEl = parentEl.find('a:first');
                    if(linkEl.text() != el.val()) {
                        linkEl.text(el.val());
                        //self.changeTitle(el);
                    }
                    linkEl.show();
                });
            }
        }
    });

});
</script>
{/literal}