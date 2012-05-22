<div class="bz-form-row clearfix<?php if(count($element->getErrors()) > 0) { ?> error<?php } ?>">
<?php echo $element->renderLabel(); ?>
<input <?php echo $element->getAttributesString() ?> <?php if ($element->isRequireField()) { ?> required="required"<?php } ?> />
<?php /*echo $element->renderError();*/ ?>
<?php echo $element->renderComment(); ?>
</div>