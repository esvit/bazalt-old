<?php
    $id = $element->id();
    $className = get_class($element);
?>

<?php echo $className; ?> = function(id, className) {
    this.value = function(value) {
        if (typeof value == 'undefined') {
            return this.element.val();
        }
        return this.element.val(value);
    };

    <?php echo $element->getAjaxMethodsJs(); ?>
};

<?php echo $className; ?>.prototype = new Html_FormElement();
<?php echo $className; ?>.prototype.constructor = <?php echo $className; ?>;
<?php echo $className; ?>.superclass = Html_FormElement.prototype;