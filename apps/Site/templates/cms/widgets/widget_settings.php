<?php
$templates = $widget->getTemplates();
$settigns = $widgetConfig->getWidgetSettings();

?>
<div rel="<?php print $widgetConfig->id;?>" id="cms-widget-<?php print $widgetConfig->id;?>-settings" class="cms-widgets-settings ui-corner-all">

    <form action="#" method="POST" autocomplete="off">
        <input type="hidden" value="<?php print $widgetConfig->id;?>" class="widget-id" name="widget_id" />

        <?php print $settigns;?>

        <?php
        
            $templates = $widget->getTemplates();
            $customTemplates = $widget->getCustomTemplates();
        ?>
        <br /> <br /> <?php echo __('Template', 'CMS'); ?>:
        <select class="widget-templates" name="cms_widget_template">
            <optgroup label="<?php echo __('System', 'CMS'); ?>">
                <option value=""<?php if (empty($widgetConfig->widget_template)) { ?> selected="selected"<?php } ?>><?php echo __('[Default template]'); ?></option>
                <?php foreach ($templates as $template => $title) {  ?>
                <option value="<?php echo $template; ?>"<?php if ($template == $widgetConfig->widget_template) { ?> selected="selected"<?php } ?>><?php echo $title; ?></option>
                <?php } ?>
            </optgroup>
            <?php $showEdit = false; if (count($customTemplates) > 0) { ?>
            <optgroup rel="custom" label="<?php echo __('Custom', 'CMS'); ?>">
            <?php foreach ($customTemplates as $customTemplate) {  ?>
                <option value="<?php echo $customTemplate->name; ?>"<?php if ($customTemplate->name == $widgetConfig->widget_template) { $showEdit = true; ?> selected="selected"<?php } ?>><?php echo $customTemplate->title; ?></option>
            <?php } ?>
            </optgroup>
            <?php } ?>
        </select>
        <button type="button" class="add">+</button>
        <button type="button"<?php if (!$showEdit) { ?> style="display: none"<?php } ?> class="edit">edit</button>
        

        <div class="spacer"></div>
    </form>
    <div class="editTemplate" style="display: none;">
        <form action="" method="POST" autocomplete="off">
            <input type="hidden" value="<?php print $widgetConfig->widget_id;?>" class="widgetId"/>
            <div class="bz-form-row clearfix">
                <label class="bz-form-label" for="tplName"><?php echo __('Name', 'CMS'); ?>:</label>
                <input type="text" class="tplName bz-form-input ui-input">
            </div>
            <div class="bz-form-row clearfix">
                <label class="bz-form-label" for="tplTitle"><?php echo __('Title', 'CMS'); ?>:</label>
                <input type="text" class="tplTitle bz-form-input ui-input">
            </div>
            <div class="bz-form-row">
                <label class="bz-form-label" for="content"><?php echo __('Content', 'CMS'); ?>:</label>
                <textarea rows="6" cols="40" class="tplContent bz-form-textarea ui-input"></textarea>
            </div>
            <button type="button" class="save"><?php echo __('Save', 'CMS'); ?></button>
            <div class="spacer"></div>
        </form>
    </div>
    

</div>