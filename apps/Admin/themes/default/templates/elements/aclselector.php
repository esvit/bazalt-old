<?php $element->addClass('ui-widget-content'); ?>
<fieldset <?php echo $element->getAttributesString() ?>>
    <?php if ($element->label()) { ?>
        <legend>
            <?php echo $element->label(); ?> 
            <a class="ui-state-default ui-corner-all selectall" href="#"><span class="ui-icon ui-icon-bullet"></span>select all</a>
            <a class="ui-state-default ui-corner-all" href="#"><span class="ui-icon ui-icon-radio-on"></span>deselect all</a>
        </legend>
    <?php } ?>
    <?php echo $content; ?>
</fieldset>

<script>
$(function() {
    $('.bz-form-cms-aclselector a').click(function(){
        var select = $(this).hasClass('selectall'),
            container = $(this).parents('.bz-form-cms-aclselector');

        if (select) {
            $('input', container).attr('checked', 'checked');
        } else {
            $('input', container).removeAttr('checked');
        }
        return false;
    });
});
</script>