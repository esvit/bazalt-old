<div class="bz-widget" data-widget="<?php echo $widgetConfig->id; ?>"<?php if ($hasPermition) { ?> data-widget-title="<?php echo $widgetConfig->getName(); ?>"<?php } ?> id="bz-widget-<?php echo $widgetConfig->id; ?>">
<?php echo $content; ?>
</div>