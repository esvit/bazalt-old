<script id="widgetTpl" type="text/x-jquery-tmpl">
    <div class="reset-css">
        <ul>
        {{each widgets}}
            <li class="ui-widget-content ui-state-default ui-corner-all" rel="${id}">
                <div class="bz-widgetselector-content">
                    <div class="bz-widgetselector-title"><span class="ui-icon ui-icon-check"></span>${title}</div>
                    <div class="bz-widgetselector-description">${description}</div>
                </div>
            </li>
        {{/each}}
        </ul>
    </div>
</script>
<div id="widgetsSelector" title="Add widget">
    <div class="ui-loading-icon"></div>
</div>
<?php
    Assets_JS::addPackage('Bazalt');
    CMS_Application::current()->addScript('cms/widgets.js');
    CMS_Application::current()->addStyle('cms/widgets.css');
?>

<div id="cms_plugin_overlay">
    <div class="cms_plugin_overlay_bg"></div>

</div>