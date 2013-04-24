<?php echo __('Template', 'CMS'); ?>:
<select class="widget-templates" ng-model="widget.widget_template" ng-options="item.template as item.template group by item.type for item in templates"></select>

<p><label class="checkbox"><input ng-model="widget.publish" name="published" type="checkbox"> <?php echo __('Published', 'CMS'); ?></label></p>