<div class="bz-form-row">
<?php echo $element->renderLabel(); ?>
<textarea <?php echo $element->getAttributesString() ?>><?php echo $element->value(); ?></textarea>
<?php echo $element->renderError(); ?>
<?php echo $element->renderComment(); ?>
</div>