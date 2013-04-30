<?php if ($hasPermition) { ?><div data-widgets class="cms-widgets-border-around" data-template="<?php print $template ?>" data-position="<?php print $position ?>"><?php } ?>
<?php echo $content; ?>
<?php if ($hasPermition) { ?></div><?php } ?>