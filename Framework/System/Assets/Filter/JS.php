<?php

class Assets_Modificator_Javascript extends Assets_Modificator_Abstract
{
    public static function compressFile($file)
    {
        using('Framework.Vendors.JSMin');

        return JSMin::minify($file);
    }

    public static function compress($file, $toFile = null, $publicUrl)
    {
        if ($toFile == null) {
            $toFile = $file . '.gz';
        }

        $errors = null;
        $tmpFile = $file . '.tmp';
        using('Framework.System.Google.Closure');
        /*if (true) {
            $errors = GoogleClosure::compile($file, $tmpFile);

            if ($errors != null && is_array($errors) && STAGE == DEVELOPMENT_STAGE) {
                foreach ($errors as $error) {
                    echo '<script> console.error("- JS Compressor: \n' . addcslashes($error,"\\\'\"&\n\r<>") . '")</script>';
                }
            }
        } else {
            $g = new GoogleClosure();
            $g->add($publicUrl)
              ->simpleMode()
              ->hideDebugInfo()
              ->useClosureLibrary()
              ->cacheDir(dirname($toFile) . '/')
              ->write($tmpFile);
        }*/

        $destFile = (file_exists($tmpFile) && filesize($tmpFile) == 0) ? $file : $tmpFile;

        $cmd = (OS == OS_WIN) ? (dirname(__FILE__) . '/gzip.exe') : 'gzip';
        $args = ' --best ' . $destFile . ' -c > ' . $toFile;

        exec($cmd . $args);
        //unlink($tmpFile);
        if ($errors != null) {
        //    unlink($destFile);
        }
    }
}