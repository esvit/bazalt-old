<?php if ($element->label()) { ?>
<label for="<?php echo $element->id(); ?>" class="bz-form-label">
    <?php echo $element->label(); ?>
    <?php if ($element->isRequireField()) { ?><span class="bz-required-field">*</span><?php } ?>
</label>
<?php } ?>