<?php

using('Framework.System.Error');

class CMS_ErrorCatcher extends Error_Catcher
{
    /**
     * Ajax format output
     */
    const AJAX_FORMAT = 'ajax';

    protected static $instance = null;

    protected $email = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className();
        }
        return self::$instance;
    }

    public static function startCatch()
    {
        self::getInstance()->init();
    }

    public static function stop()
    {
        self::getInstance()->stopCatch();
    }

    public function init()
    {
        parent::init();

        if (CMS_Request::isAjax()) {
            $this->setFormat(self::AJAX_FORMAT);
        }
    }

    /**
     * Get output format for exception
     *
     * @param Exception $exception Exception
     *
     * @return string Output text
     */
    public function getFormatError($exception, $content = null)
    {
        if (STAGE == PRODUCTION_STAGE && !is_string($exception)) {
            ob_end_clean();
        }
        Metatags::Singleton()->noIndex()->noFollow();

        if (self::$format == self::AJAX_FORMAT) {
            CMS_Webservice::sendError($exception);
        }
        if (STAGE == PRODUCTION_STAGE) {
            self::sendToErrorService($exception);
            return $this->showProductionErrorPage($exception);
        }
        return parent::getFormatError($exception, $content);
    }

    protected function showFatalError($error, $content)
    {
        if (self::$format == self::AJAX_FORMAT) {
            return CMS_Webservice::sendError($error, true);
        }
        if (STAGE == PRODUCTION_STAGE) {
            return $this->showProductionErrorPage($error);
        }
        return parent::showFatalError($error, $content);
    }

    protected function showProductionErrorPage($error)
    {
        $consts = array(
            'SERVER_ADMIN' => $_SERVER['SERVER_ADMIN']
        );

        $view = CMS_Application::current()->View;
        $content = $view->fetch('error500');//file_get_contents(dirname(__FILE__) . '/templates/error500.php');

        $content = DataType_String::replaceConstants($content, $consts);

        return $content;
    }
    
    protected static function sendToErrorService($exception)
    {
        $data = array();
        if ($exception instanceof Exception) {
            $data['code'] = $exception->getCode();
            $data['message'] = get_class($exception).': '. $exception->getMessage();
            $data['trace'] = $exception->getTraceAsString();
            $data['file'] = relativePath($exception->getFile(), SITE_DIR).' : '. $exception->getLine();
        } else {
            $data['message'] = $exception;
        }
        $data['request'] = print_r(array_merge($_COOKIE, $_REQUEST), true);
        $data['session'] = print_r($_SESSION, true);
        $data['url'] = DataType_Url::getRequestUrl();
        
        $url = new DataType_Url('http://errors.bazalt-cms.com/add/');
        $res = $url->post(array(
            'error' => json_encode($data),
            'host' => DataType_Url::getDomain()
        ));
    }
}