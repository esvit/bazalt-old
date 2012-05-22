<ul <?php echo $element->getAttributesString(); ?>>
<?php foreach ($element->Elements as $el) { ?>
    <li <?php if ($el->active()) { ?> class="active"<?php } ?>><a data-toggle="tab" href="#<?php echo $el->id() ?>"><span><?php echo $el->title(); ?></span></a></li>
<?php } ?>
</ul>
<div id="<?php echo $element->id(); ?>Content" class="tab-content">
<?php echo $content; ?>
</div>