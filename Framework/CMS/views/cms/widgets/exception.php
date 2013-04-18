<div style="border: 1px solid red; padding: 5px;">
    <strong style="color: red">Uncaught exception in widget</strong>
    <?php if (STAGE == DEVELOPMENT_STAGE) { ?>
    <pre><?php echo $exception->getMessage(); ?></pre>
    <?php } ?>
</div>