<?php

namespace Framework\System\View\PHP;

use Framework\System\View as View;

class Engine extends View\Engine
{
    public function fetch($folder, $file, View\Scope $view)
    {
        $vars = $view->variables();

        extract($vars);
        ob_start();

        $errorLevel = error_reporting();
        error_reporting($errorLevel & ~E_NOTICE);

        include $folder . PATH_SEP . $file;
        $content = ob_get_contents();
        ob_end_clean();

        error_reporting($errorLevel);

        return $content;
    }
}