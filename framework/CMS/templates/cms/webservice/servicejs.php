window.<?php echo $service['name']; ?> = {
<?php foreach ($service['methods'] as $methodName => $arguments) { ?><?php $args = ''; $argCount = count($arguments); ?>
<?php foreach ($arguments as $i => $arg) { $args .= 'p_' . $arg; if ($i < $argCount - 1) { $args .= ','; } } ?>
    <?php echo ltrim($methodName, '_'); ?>: function(<?php echo $args; if ($argCount > 0) { echo ','; } ?>onSuccess, onFailure, context)
    {
        BAZALTScriptService.callMethod(<?php echo $service['name']; ?>.scriptUrl, <?php echo $service['name']; ?>.format, '<?php echo $methodName; ?>',<?php echo ($args == '') ? 'null,' : '[' . $args . '],'; ?>onSuccess,onFailure,context);
    },
<?php } ?>
    scriptUrl:    '<?php echo $service['script']; ?>',
    generator:    'BAZALT CMS'
};