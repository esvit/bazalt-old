<?php
    $element->addClass('btn');
?>
<div class="bz-form-row">
<?php echo $element->renderLabel(); ?>
<input <?php echo $element->getAttributesString() ?> <?php if ($element->isRequireField()) { ?> required="required"<?php } ?> />
<?php echo $element->renderError(); ?>
<?php echo $element->renderComment(); ?>
</div>