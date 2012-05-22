<?php if (count($errors = $element->getErrors()) > 0) { ?>
<?php foreach ($errors as $error) { ?>
<span class="help-inline"><?php echo $error; ?></span>
<?php } ?>
<?php } ?>