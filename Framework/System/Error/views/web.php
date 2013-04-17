<?php
    use Framework\System\Error\Catcher,
        Framework\Core as Core;
?>
<div id="trace_ex" style="background-color: white; padding: 15px; z-index: 1000; border: 1px solid black;">
    <h3><span style="color: red;">Uncaught exception (<?php echo get_class($_GLOBAL['exception']); ?>)</span></h3>

    <div style="white-space:pre-wrap; font-size: 150%"><?php echo htmlentities($_GLOBAL['exception']->getMessage()); ?></div>
        
    <?php
        if (($_GLOBAL['exception'] instanceof Core\Exception\Base) && $_GLOBAL['exception']->getDetails() != null) { ?>
            <div style="white-space:pre-wrap;"><?php echo $_GLOBAL['exception']->getDetails(); ?></div><?php
        }
    ?>

    <strong>Code <?php echo $_GLOBAL['exception']->getCode(); ?></strong><br />

    <i><?php echo relativePath($_GLOBAL['exception']->getFile(), SITE_DIR); ?> : <?php echo $_GLOBAL['exception']->getLine(); ?></i>

    <pre style="background-color: #EEEEEE; overflow: hidden;"><?php echo Catcher::getFileLineForDebug($_GLOBAL['exception']->getFile(), $_GLOBAL['exception']->getLine()); ?></pre>

    <p>
        <strong><a href="#" onclick="javascript: hideDiv('innerException'); toggleTrace('trace'); return false;">Trace</a></strong>
        <?php if (method_exists($_GLOBAL['exception'], 'getPrevious') &&$_GLOBAL['exception']->getPrevious() != null) { ?>
            <a href="#" onclick="javascript: hideDiv('trace'); toggleTrace('innerException'); return false;">Inner Exception</a>
        <?php } ?>
    </p>

    <div id="trace" style="display: none">
        <?php echo Catcher::renderTrace($_GLOBAL['exception']); ?>
    </div>

    <?php if (method_exists($_GLOBAL['exception'], 'getPrevious') && $_GLOBAL['exception']->getPrevious() != null) { ?>
    <div id="innerException" style="display: none">
        <h4><span style="color: red;">Inner exception (<?php echo get_class($_GLOBAL['exception']->getPrevious()); ?>)</span></h4>

        <div style="white-space:pre-wrap;"><?php echo htmlentities($_GLOBAL['exception']->getPrevious()->getMessage()); ?></div>
        
        <?php
            if ($_GLOBAL['exception']->getPrevious() instanceof Core\Exception\Base && $_GLOBAL['exception']->getPrevious()->getDetails() != null) { ?>
                <div style="white-space:pre-wrap;"><?php echo $_GLOBAL['exception']->getPrevious()->getDetails(); ?></div><?php
            }
        ?>

        <strong>Code <?php echo $_GLOBAL['exception']->getPrevious()->getCode(); ?></strong><br />

        <i><?php echo relativePath($_GLOBAL['exception']->getPrevious()->getFile(), SITE_DIR); ?> : <?php echo $_GLOBAL['exception']->getPrevious()->getLine(); ?></i>

        <pre style="background-color: #EEEEEE; overflow: hidden;"><?php echo Catcher::getFileLineForDebug($_GLOBAL['exception']->getPrevious()->getFile(), $_GLOBAL['exception']->getPrevious()->getLine()); ?></pre>

        <?php echo Catcher::renderTrace($_GLOBAL['exception']->getPrevious()); ?>
    </div>
    <?php } ?>
</div>
<script>
function hideDiv(id)
{
    var el = document.getElementById(id);
    if (el != null) el.style.display = 'none';
}
function toggleTrace(id)
{
    var el = document.getElementById(id);
    el.style.display = (el.style.display != 'none' ? 'none' : '' );
}
function closeException()
{
    var el = document.getElementById('trace_ex');
	el.style.display = 'none';
}
</script>