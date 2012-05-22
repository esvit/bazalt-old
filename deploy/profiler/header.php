<?php
 
if (extension_loaded('xhprof')) {
    define('XHPROF_ENABLE', true);
    include_once getenv('XHPROF_DIR') . '/utils/xhprof_lib.php';
    include_once getenv('XHPROF_DIR') . '/utils/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}