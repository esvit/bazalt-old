<?php
$trace = $_GLOBAL['exception']->getTrace();
$str = 'Uncaught exception:'."\n";
$str .= $_GLOBAL['exception']->getMessage().':'.$_GLOBAL['exception']->getCode()."\n";
$str .= 'In file '.$_GLOBAL['exception']->getFile() .' '. $_GLOBAL['exception']->getLine()."\n";
$str .= 'Trace: '."\n";

print $str;

foreach ($trace as $k => $item) {
    print '   '.($k+1) . ') ' . $item['class'].$item['type'].$item['function'].' - '.$item['line']."\n";
}