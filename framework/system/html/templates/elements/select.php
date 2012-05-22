<div class="bz-form-row bz-form-select-container clearfix">
<?php echo $element->renderLabel(); ?>
    <div class="bz-form-element">
        <select <?php echo $element->getAttributesString() ?> <?php if ($element->isRequireField()) { ?> required="required"<?php } ?>>
            <?php foreach ($element->getGroups() as $i => $group) {
                echo $group->toString($element->value(), $i == 0);
            } ?>
        </select>
    </div>
<?php echo $element->renderError(); ?>
<?php echo $element->renderComment(); ?>
</div>