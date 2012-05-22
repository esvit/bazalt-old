<?php
    $id = $element->id();
    $className = get_class($element);
?>
<?php echo $className; ?> = function(id, className) {
    this.headerCell = null;
    this.rowsCells = null;
    this.checkboxes = null;

    this.initElement = function() {
        var self = this;

        this.headerCell = $('th.column-' + this.id);
        this.rowsCells = $('td.column-' + this.id);
    };

    <?php echo $element->getAjaxMethodsJs(); ?>
};
<?php echo $className; ?>.prototype = new Html_FormElement();
<?php echo $className; ?>.prototype.constructor = <?php echo $className; ?>;
<?php echo $className; ?>.superclass = Html_FormElement.prototype;