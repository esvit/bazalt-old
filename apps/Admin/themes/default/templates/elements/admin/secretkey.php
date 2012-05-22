<div class="bz-form-row">
<?php echo $element->renderLabel(); ?>
<span <?php echo $element->getAttributesString() ?>>
    <span class="value uneditable-input"><?php echo $element->value() ?></span>
    <a class="ui-float-left" href="javascript:;" title="<?php echo __('Generate new secret key', 'Admin_App') ?>">
        <span class="ui-icon ui-icon-refresh"></span>
    </a>
</span>
<?php echo $element->renderError(); ?>
<?php echo $element->renderComment(); ?>
</div>