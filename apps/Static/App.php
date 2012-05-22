<?php

class Static_App extends CMS_Application
{
    public static function notFound($file)
    {
        header('HTTP/1.0 404 Not Found');
        include dirname(__FILE__) . '/templates/notfound.php';
        exit;
    }

    public function start()
    {
        $requestUrl = DataType_Url::getRequestUrl();
        $fileName = PUBLIC_DIR . $requestUrl;

        # antihacker
        if (substr_count($fileName, '../') != false) {
            exit;
        }

        // file extension
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        // Precompile filename
        $preCompiled = $fileName . '.pre';
        if (!file_exists($preCompiled)) {
            self::notFound($requestUrl);
        }

        if (in_array(strToLower($ext), CMS_Image::$extensions)) {
            try {
                $img = new CMS_Image($preCompiled);
                $img->doActions();
                $img->save($fileName);
            } catch (CMS_Exception_PageNotFound $ex) {
                self::notFound(relativePath($ex->getPage()));
            }

            ob_end_clean();
            $info = getImageSize($fileName);
            header('Content-Type: '.$info['mime']);
            readfile($fileName);
            exit;
        }

        $files = explode("\n", file_get_contents($preCompiled));

        $type = $files[0];
        unset($files[0]);

        set_time_limit(0);

        switch ($type) {
            // if precompiled file - scripts
            case Assets_JS::PRECOMILE_TYPE:
                header('Content-type: application/javascript');
                Assets_JS::compileFiles($files, $fileName);
                break;
            // if precompiled file - styles
            case Assets_CSS::PRECOMILE_TYPE:
                header('Content-type: text/css');
                Assets_CSS::compileFiles($files, $fileName);
                break;
            // all other
            default:
                self::notFound($requestUrl);
        }

        readfile($fileName);
        exit;
    }
}