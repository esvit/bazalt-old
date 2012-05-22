<div id="trace_ex" style="background-color: white; padding: 15px; z-index: 1000; border: 1px solid black;">
    <a href="#" style="float: right;" onclick="javascript: closeException(); return false;">X</a>

    <h3><span style="color: red;">Uncaught exception (<?php echo get_class($_GLOBAL['exception']); ?>)</span></h3>

    <div style="white-space:pre-wrap; font-size: 150%"><?php echo htmlentities($_GLOBAL['exception']->getMessage()); ?></div>
        
    <?php
        if ($_GLOBAL['exception'] instanceof Exception_Base && $_GLOBAL['exception']->getDetails() != null) { ?>
            <div style="white-space:pre-wrap;"><?php echo $_GLOBAL['exception']->getDetails(); ?></div><?php
        }
    ?>

    <strong>Code <?php echo $_GLOBAL['exception']->getCode(); ?></strong><br />

    <i><?php echo relativePath($_GLOBAL['exception']->getFile(), SITE_DIR); ?> : <?php echo $_GLOBAL['exception']->getLine(); ?></i>

    <pre style="background-color: #EEEEEE; overflow: hidden;"><?php echo Error_Catcher::getFileLineForDebug($_GLOBAL['exception']->getFile(), $_GLOBAL['exception']->getLine()); ?></pre>

    <p>
        <strong><a href="#" onclick="javascript: hideDiv('innerException'); toggleTrace('trace'); return false;">Trace</a></strong>
        <?php if ($_GLOBAL['exception'] instanceof Exception_Base && $_GLOBAL['exception']->getInnerException() != null) { ?>
            <a href="#" onclick="javascript: hideDiv('trace'); toggleTrace('innerException'); return false;">Inner Exception</a>
        <?php } ?>
    </p>

    <div id="trace" style="display: none">
        <?php echo Error_Catcher::renderTrace($_GLOBAL['exception']); ?>
    </div>

    <?php if ($_GLOBAL['exception'] instanceof Exception_Base && $_GLOBAL['exception']->getInnerException() != null) { ?>
    <div id="innerException" style="display: none">
        <h4><span style="color: red;">Inner exception (<?php echo get_class($_GLOBAL['exception']->getInnerException()); ?>)</span></h4>

        <div style="white-space:pre-wrap;"><?php echo htmlentities($_GLOBAL['exception']->getInnerException()->getMessage()); ?></div>
        
        <?php
            if ($_GLOBAL['exception']->getInnerException() instanceof Exception_Base && $_GLOBAL['exception']->getInnerException()->getDetails() != null) {
                echo $_GLOBAL['exception']->getDetails();
            }
        ?>

        <strong>Code <?php echo $_GLOBAL['exception']->getInnerException()->getCode(); ?></strong><br />

        <i><?php echo relativePath($_GLOBAL['exception']->getInnerException()->getFile(), SITE_DIR); ?> : <?php echo $_GLOBAL['exception']->getInnerException()->getLine(); ?></i>

        <pre style="background-color: #EEEEEE; overflow: hidden;"><?php echo Error_Catcher::getFileLineForDebug($_GLOBAL['exception']->getInnerException()->getFile(), $_GLOBAL['exception']->getInnerException()->getLine()); ?></pre>

        <?php echo Error_Catcher::renderTrace($_GLOBAL['exception']->getInnerException()); ?>
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