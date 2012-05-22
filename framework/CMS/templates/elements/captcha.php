<div class="bz-form-row">
    <?php echo $element->renderLabel(); ?>
    <?php echo $element->renderError(); ?>
    <?php echo $element->renderComment(); ?>
    
    <img src="<?php echo CMS_Mapper::urlFor('CMS.Captcha', array('element' => md5($element->name())));?>"/>
    <br/>
    <input type="text" value="" name="<?php echo $element->name() ?>" id="<?php echo $element->id() ?>" class="<?php echo $element->class() ?>" required="required">
</div>