<div class="bz-widget cms-widget-container ui-widget-container" id="bz-widget-<?php echo $widgetConfig->id; ?>" data-widget-id="<?php echo $widgetConfig->id; ?>">

<?php if ($hasPermition) { ?>
    <div class="cms-widget-title ui-state-default">
        <div class="cms-widget-title-text"><?php echo $widgetConfig->getName(); ?></div>
        <a href="#" data-toggle="modal" data-id="<?php echo $widgetConfig->id; ?>" class="ui-icon ui-icon-wrench btn-settings"></a>
        <a href="javascript:;" class="ui-icon ui-icon-trash btn-delete"></a>
    </div>
    <div class="cms-widget">
<?php } ?>
<?php echo $content; ?>
<?php if ($hasPermition) { ?>
    </div>
    <div class="cms-widget-clearfix"></div>

<?php } ?>

</div>