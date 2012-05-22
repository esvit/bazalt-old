<div <?php echo $element->getAttributesString() ?>>
<?php echo $element->renderLabel(); ?>

<ul>
<?php foreach ($errors as $err) { ?>
<li><?php echo $err; ?></li>
<?php } ?>
</ul>

<?php echo $element->renderComment(); ?>
</div>