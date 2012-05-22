<?php
    $element->addClass('alert alert-block');
    $element->addClass('alert-' . $element->type());
?>
<div <?php echo $element->getAttributesString() ?>>
    <a class="close" href="#">&times;</a>
    <p><?php echo $element->text(); ?></p>
    <?php
        $actions = $element->getActions();
        if (count($actions) > 0) {
    ?>
    <div class="alert-actions">
        <?php foreach ($actions as $text => $action) { ?>
        <a class="btn small" href="<?php echo $action; ?>"><?php echo $text; ?></a>
        <?php } ?>
    </div>
    <?php } ?>
</div>