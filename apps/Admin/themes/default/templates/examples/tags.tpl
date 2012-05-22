<h1>Examples: Tags</h1>
{jsmodule name="fcbkcomplete"}
<div class="dev-panel">
<p>Selector</p>
<select id="select2" name="select2">
    <option value="testme1" selected="selected">testme1</option>
    <option value="testme2">testme2</option>
    <option value="testme3">testme3</option>
</select>
{*
<div class="ui-overlay"><div class="ui-widget-overlay"></div><div class="ui-widget-shadow ui-corner-all" style="width: 302px; height: 152px; position: absolute; left: 50px; top: 30px;"></div></div>
<div style="position: absolute; width: 280px; height: 130px;left: 50px; top: 30px; padding: 10px;" class="ui-widget ui-widget-content ui-corner-all">
    <div class="ui-dialog-content ui-widget-content" style="background: none; border: 0;">

        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
    </div>
</div>
*}
</div>
{literal}
<script type="text/javascript">
$(function() {

    $("#select2").fcbkcomplete({
        json_url: "your url",
        json_cache: true,
        filter_case: true,
        filter_hide: true,
        filter_selected: true,
        maxlength: 20,
        complete_text: '<span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span> Начните набирать текст...',
        newel: true        
    });

});
</script>
{/literal}