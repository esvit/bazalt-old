<?php if ($hasPermition) { ?>
<div class="cms-widgets-border-around" data-template="<?php print $template ?>" data-position="<?php print $position ?>">
    <div class="cms-widgets-position-title ui-widget-header">
        <?php print $position ?>
        <a href="javascript:;" template="<?php print $template ?>" position="<?php print $position ?>" class="cms-widgets-add-widget ui-icon ui-icon ui-icon-plusthick"></a>
    </div>
<?php } ?>

<?php echo $content; ?>

<?php if ($hasPermition) { ?>
</div>
<?php } ?>