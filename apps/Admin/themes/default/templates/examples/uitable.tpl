<h1>Examples: Checkboxes & radiobuttons</h1>

{literal}
<script type="text/javascript" src="/admin/services/tagsservice?js"></script>
{/literal}
{jsmodule name="jsmallcheck"}
{jsmodule name="paginator"}
{jsmodule name="ui-spinner"}
{jsmodule name="table-row-checkboxes"}
{jsmodule name="ui-table"}

<div class="dev-panel">

<div id="errdlg" class="ui-widget ui-helper-hidden">
    <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
        <p id="errtext"></p>
    </div>
</div>

<div class="fg-toolbar ui-widget-header ui-corner-all ui-helper-clearfix panel_main">
    <div class="right">
        <a title="Publish all checked" class="group-ctrl tooltip many-publish fg-button ui-state-default ui-state-disabled fg-button-icon-left ui-corner-all" id="1" href="javascript:void(null)">
            <span class="ui-icon ui-icon-check"></span> {tr}ON{/tr}
        </a>
        <a title="No publish all checked" class="group-ctrl tooltip many-publish fg-button ui-state-default ui-state-disabled fg-button-icon-left ui-corner-all" id="0" href="javascript:void(null)">
            <span class="ui-icon ui-icon-cancel"></span> {tr}OFF{/tr}
        </a>
        <a title="Delete all checked" class="group-ctrl tooltip many-delete fg-button ui-state-default ui-state-disabled fg-button-icon-left ui-corner-all" href="javascript:void(null)">
            <span class="ui-icon ui-icon-trash"></span> {tr}Delete{/tr}
        </a>
    </div>
</div>

<div class="panel_filter groupTags">           
    <a id="getAll" class="active" rel="null" href="javascript:void(null);">{tr}All{/tr} <span>({$countAllTags})</span></a> |
    <a id="getPublished" rel="1" href="javascript:void(null);">{tr}Published{/tr} <span>({$countPublishedTags})</span></a> |
    <a id="getNotPublished" rel="0" href="javascript:void(null);">{tr}Not published{/tr} <span>({$countNotPublishedTags})</span></a>
</div>

<div class="ui-grid ui-widget ui-widget-content ui-corner-all">
    <table id="tagsTable2" class="ui-grid-content ui-widget-content">
    </table>
</div>

</div>

<script type="text/javascript">
{literal}
$(function() {
    var countPerPage = 5;
    var tableOptions = {
        fields: [
            {
                name: 'title',
                title: 'Title',
                sorted: true,//default is false
                editable: true,//default is false
                onEdit: function(primaryKey, field, value) {
                    TagsService.ChangeTitle(primaryKey, value);
                },
                format: function(data, field, primaryKey) {
                    return '<td class="ui-widget-content" style="text-align:left;">'+data[field]+'</td>';
                },
                type: 'string' 
            },
            {
                name: 'rating',
                link: function(data) {
                    return '/admin/some/'+data.id;
                }
            },
            {
                name: 'order',
                editable: true,//default is false
                onEdit: function(primaryKey, field, value) {
                    TagsService.ChangeOrder(primaryKey, value, function(){
                        $uiTableObj.uiTable('update');
                    });
                },
                sorted: true,
                type: 'number' 
            },
            {
                name: 'publish',
                sorted: true,//default is false
                editable: true,//default is false
                onEdit: function(primaryKey, field, value) {
                    value = (value == true) ? 1 : 0;
                    TagsService.ChangePublish(primaryKey, value, function(){
                        $uiTableObj.uiTable('update');
                    });
                },
                type: 'bool'
            },
            {
                name: 'delete',
                title: 'Видалити',
                format: function(data, field, primaryKey) {
                    return '<td width="28" class="ui-widget-content">'+
                    ' <a href="#" class="ui-state-error delete fg-button ui-state-default fg-button-icon-solo ui-corner-all" rel="'+data[primaryKey]+'" title="Видалити">'+
                    ' <span class="ui-icon ui-icon-trash"></span>Видалити</a>'+
                    '</td>';
                },
                onInit: function(self) {
                    $('.delete').unbind('click').click(function(event) {
                        TagsService.Delete( $(this).attr('rel'), function(res) {
                            $uiTableObj.uiTable('update');
                            $(this).parent().parent().remove();
                        });
                    });
                }
            }
        ],

        primaryKey: 'id',
        
        groupOperations: true,
     
        callback: {
            onGroupChecked: function(checked) {
                if(checked.size() > 0) {
                    $('.group-ctrl').removeClass('ui-state-disabled');
                } else {
                    $('.group-ctrl').addClass('ui-state-disabled');
                }
            },
            onUpdate: function(filter, sorting, curPage) {
                TagsService.GetTags(filter, sorting, {'countPerPage':countPerPage,'current':curPage}, function(res) {
                    $uiTableObj.uiTable('onUpdated', res);
                });
            },
            onPreUpdated: function(data) {
                if(data.counts != undefined) {
                    $('#getAll span').text('(' + data.counts.all + ')');
                    $('#getPublished span').text('(' + data.counts.published + ')');
                    $('#getNotPublished span').text('(' + data.counts.notpublished + ')');
                    
                    delete data.counts;
                }
                if(data.pages) {
                    $uiTableObj.uiTable('setPaging', data.pages);
                    
                    delete data.pages;
                }
            }
        }
    };
    
    $('.groupTags a').unbind('click').click(function(event){
        $('.panel_filter a').removeClass('active');
        $(this).addClass('active');
        if($(this).attr('rel') == 'null') {
            $uiTableObj.uiTable('setFilter', null);
        } else {
            $uiTableObj.uiTable('setFilter', {'publish': $(this).attr('rel')});
        }
    });
    
    $('.many-publish').unbind('click').click(function(event) {
        var ids = [];
        var publish = this.id;
        $uiTableObj.uiTable('getChecked').each(function(){
            ids.push(this.id.slice(8));
            if(publish == 0){
                $(this).parent().parent().find('.editable-bool').attr('checked',false);
            }else{
                $(this).parent().parent().find('.editable-bool').attr('checked',true);
            }
        });
        if(ids.length == 0) {
            return false;
        }
        TagsService.ChangePublish(ids, this.id, function(res) {
            $uiTableObj.uiTable('update');
        });
    });
    $('.many-delete').click(function() {
        if($('.many-delete').hasClass('ui-state-disabled')){
            return;
        }
        if(confirm('Are you shure ?')) {
            var ids = [];
            $uiTableObj.uiTable('getChecked').each(function(){
                ids.push(this.id.slice(8));
            });
            if(ids.length == 0) {
                return false;
            }
            TagsService.Delete(ids, function(res) {
                $uiTableObj.uiTable('update');
            });
        }
    });
    
    TagsService.GetTags(null, null, {'countPerPage':countPerPage,'current':1}, function(res) {
        $uiTableObj.uiTable('onUpdated', res);
    });
    $uiTableObj = $('#tagsTable2').uiTable(tableOptions);
});
{/literal}
</script>