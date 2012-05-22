<div class="bz-form-row">
    <?php echo $element->renderLabel(); ?>
    <div <?php echo $element->getAttributesString() ?>></div>
    <input name="<?php echo $element->name() ?>" value="<?php echo $element->value() ?>" type="hidden" />
    <?php echo $element->renderError(); ?>
    <?php echo $element->renderComment(); ?>
</div>