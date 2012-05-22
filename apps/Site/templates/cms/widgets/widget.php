<?php if ($hasPermition) { ?>
<div class="cms-widget-container ui-widget-container" id="cms-widget-<?php echo $widgetConfig->id; ?>">
    <div class="cms-widget-title ui-state-default">
        <div class="cms-widget-title-text"><?php echo $widgetConfig->name; ?></div>
        <a href="javascript:;" class="ui-icon ui-icon-wrench btn-settings"></a>
        <a href="javascript:;" class="ui-icon ui-icon-trash btn-delete"></a>
    </div>
    <div class="cms-widget">
<?php } ?>

<?php echo $content; ?>

<?php if ($hasPermition) { ?>
    </div>
    <?php require 'widget_settings.php';?>
</div>
<?php } ?>