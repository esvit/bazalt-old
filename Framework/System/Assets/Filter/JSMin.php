<?php

using('Framework.Vendors.JSMin');

class Assets_Filter_JSMin extends Assets_Filter_Abstract
{
    public function prepareFiles(array $files)
    {
        $hash = '';
        foreach ($files as $attrs) {
            $file = $attrs['file'];
            if (pathinfo($file, PATHINFO_EXTENSION) == 'js') {
                $hash .= $file . filemtime($file);
                Assets_FileManager::getInstance()->remove($file, 'js');
            }
        }
        $jsFile = Assets_FileManager::filename(md5($hash)) . 'js';
        if (!file_exists($jsFile)) {
            $jsAll = '';
            foreach ($files as $attrs) {
                $file = $attrs['file'];
                if (pathinfo($file, PATHINFO_EXTENSION) == 'js') {
                    $jsMinFile = Assets_FileManager::filename($file . filemtime($file)) . 'js';
                    if (!file_exists($jsMinFile)) {
                        $js = file_get_contents($file);

                        //$js = JSMin::minify($js);
                        file_put_contents($jsMinFile, $js);
                    } else {
                        $js = file_get_contents($jsMinFile);
                    }
                    $jsAll .= $js . ";\n";
                }
            }
            file_put_contents($jsFile, ';' . $jsAll);
        }
        Assets_FileManager::getInstance()->add($jsFile, 'js');
    }

    public function modifyFiles(array $files)
    {
    }
}