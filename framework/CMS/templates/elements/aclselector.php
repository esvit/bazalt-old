<fieldset <?php echo $element->getAttributesString() ?>>
    <?php if ($element->label()) { ?>
        <legend>
            <?php echo $element->label(); ?>
        </legend>
    <?php } ?>
    <?php echo $content; ?>
</fieldset>