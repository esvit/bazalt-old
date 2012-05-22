<div class="bz-form-row">
<label for="<?php echo $element->id(); ?>" class="bz-form-label checkbox">
    <input value="<?php echo $element->postValue(); ?>" <?php echo $element->getAttributesString() ?> <?php if ($element->isRequireField()) { ?> required="required"<?php } ?> />
    <?php if ($element->label()) { ?>
        <?php echo $element->label(); ?>
        <?php if ($element->isRequireField()) { ?><span class="bz-required-field">*</span><?php } ?>
    <?php } ?>
</label>
<?php echo $element->renderError(); ?>
<?php echo $element->renderComment(); ?>
</div>