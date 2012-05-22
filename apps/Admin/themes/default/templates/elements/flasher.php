<?php
    $element->addClass('alert alert-message');
    $element->addClass('alert-' . $element->type());
?>
<div <?php echo $element->getAttributesString(); ?>>
    <button data-dismiss="alert" class="close">&times;</button>
    <?php echo $element->text(); ?>
</div>