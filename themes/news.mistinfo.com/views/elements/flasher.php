<?php
    $element->addClass('alert');
    $element->addClass('alert-' . $element->type());
?>
<div <?php echo $element->getAttributesString(); ?>>
    <a class="close" data-dismiss="alert" href="#">&times;</a>
    <?php echo $element->text(); ?>
</div>