<?php $element->addClass('alert alert-block alert-error'); ?>
<div <?php echo $element->getAttributesString() ?>>
    <?php if ($element->renderLabel()) { ?>
    <strong><?php echo $element->label(); ?></strong>
    <?php } ?>
    <ul class="unstyled">
    <?php foreach ($errors as $err) { ?>
        <li><?php echo $err; ?></li>
    <?php } ?>
    </ul>

<?php echo $element->renderComment(); ?>
</div>