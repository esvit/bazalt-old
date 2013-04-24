<?php if ($hasPermition) { ?><div data-widgets class="cms-widgets-border-around" data-template="<?php print $template ?>" data-position="<?php print $position ?>">
<div class="cms-widgets-position-title ui-widget-header"><?php print $position ?></div><?php } ?>
<?php echo $content; ?>
<?php if ($hasPermition) { ?></div><?php } ?>