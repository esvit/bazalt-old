<?php
    $comment = $element->comment();
    if (!empty($comment)) {
?>
<span class="bz-form-comment"><?php echo $comment; ?></span>
<?php } ?>