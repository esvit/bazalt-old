<?php $element->addClass('ui-widget-content ui-corner-all'); ?>
<fieldset <?php echo $element->getAttributesString() ?>>
    <?php if ($element->title()) { ?><legend><?php echo $element->title(); ?></legend><?php } ?>
    <?php echo $content; ?>
</fieldset>