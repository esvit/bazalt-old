<?php

$userIP = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];

if (!in_array($userIP, array('127.0.0.1', '::1', '10.0.0.40', '192.168.56.1'))) {
    header('HTTP/1.0 404 Not Found');
    die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information. Your IP: ' . $userIP);
}

define('DEBUG', true);
define('WIDGET_BORDER_AROUND', true);

if (extension_loaded('xhprof') && isset($_SERVER['XHPROF_HOST'])) {
    if (!defined('XHPROF_ENABLE')) {
        define('XHPROF_ENABLE', true);
    }
    include_once dirname(__FILE__) . '/../deploy/profiler/xhprof_lib.php';
    include_once dirname(__FILE__) . '/../deploy/profiler/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

require_once '../bootstrap.php';

// Save profiler data
if (extension_loaded('xhprof') && defined('XHPROF_ENABLE') && XHPROF_ENABLE && (!defined('XHPROF_SAVERUN') || (defined('XHPROF_SAVERUN') && !XHPROF_SAVERUN))) {
    $xhprofData = xhprof_disable();
    $profilerNamespace = DataType_Url::getDomain();
    $xhprofRuns = new XHProfRuns_Default();
    $runId = $xhprofRuns->save_run($xhprofData, $profilerNamespace);

    $link = sprintf('http://' . $_SERVER['XHPROF_HOST'] . '/index.php?run=%s&source=%s', $runId, $profilerNamespace);

    echo '<a href="' . $link . '" target="_blank">Profiler</a>';
}

echo '<pre>';
foreach (ORM_Connection_Abstract::getLogQueries() as $query) {
    echo $query . "\n";
}

echo Logger::showLogs();
echo '</pre>';