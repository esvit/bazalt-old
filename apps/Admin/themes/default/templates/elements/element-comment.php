<?php
    $comment = $element->comment();
    if (!empty($comment)) {
?>
<span class="bz-form-comment help-block"><?php echo $comment; ?></span>
<?php } ?>