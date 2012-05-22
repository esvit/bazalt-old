<?php if (count($errors = $element->getErrors()) > 0) { ?>
<?php foreach ($errors as $error) { ?>
<em class="bz-form-label-error"><?php echo $error; ?></em>
<?php } ?>
<?php } ?>