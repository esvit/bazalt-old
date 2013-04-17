<?php
$templates = $widget->getTemplates();
$customTemplates = $widget->getCustomTemplates();
?>
<?php echo __('Template', 'CMS'); ?>:
<select class="widget-templates" name="cms_widget_template">
    <option value=""<?php if (empty($widgetConfig->widget_template)) { ?> selected="selected"<?php } ?>><?php echo __('[Default template]') . (empty($widgetConfig->widget_template) ? (' - ' . $widget->getTemplate()) : ''); ?></option>

    <?php foreach ($templates as $template => $title) {  ?>
    <option value="<?php echo $template; ?>"<?php if ($template == $widgetConfig->widget_template) { ?> selected="selected"<?php } ?>><?php echo $title; ?></option>
    <?php } ?>

    <?php foreach ($customTemplates as $file => $customTemplate) {  ?>
        <option value="<?php echo $file; ?>"<?php if ($file == $widgetConfig->widget_template) { ?> selected="selected"<?php } ?>><?php echo $file; ?></option>
    <?php } ?>
</select>
<?php
    $cheked = '';
    if ($widgetConfig->publish) {
        $cheked = 'checked="checked" rel="1"';
    }
    echo '<p><label class="checkbox"><input name="published" type="checkbox" ' . $cheked . '> ' . __('Published', 'CMS') . '</label></p>';
?>