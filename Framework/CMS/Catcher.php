<?php

namespace Framework\CMS;

use \Framework\Core\Helper as Helper;

class Catcher extends \Framework\System\Error\Catcher
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

        /*if (Request::isAjax() || Webservice::isWebserviceAjax()) {
            $this->setFormat(self::AJAX_FORMAT);
        }*/
    }

    /**
     * Get output format for exception
     *
     * @param Exception $exception Exception
     *
     * @param null $content
     * @return string Output text
     */
    public function getFormatError($exception, $content = null)
    {
        if (STAGE == PRODUCTION_STAGE && !is_string($exception)) {
            ob_end_clean();
        }
        //Metatags::Singleton()->noIndex()->noFollow();

        if (self::$format == self::AJAX_FORMAT) {
            return CMS_Webservice::sendError($exception);
        }
        if (STAGE == PRODUCTION_STAGE) {
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
        self::sendToErrorService($error);
        /*$view = CMS_Application::current()->View;
        // Comment reason: Cannot use output buffering in output buffering display handlers
        if ($view) {
            $content = $view->fetch('error500');
        } else {*/
        //if (file_exists($rootError)) {
        //    $content = file_get_contents($rootError);
        //} else {
            $content = file_get_contents(dirname(__FILE__) . '/views/error.500.html');
        //}
        //}

        return $content;
    }
    
    public static function sendToErrorService($exception)
    {
        $data = array();
        if ($exception instanceof Exception) {
            $data['code'] = $exception->getCode();
            $data['message'] = get_class($exception).': '. $exception->getMessage();
            if ($exception instanceof Exception_Base && $exception->getDetails() != null) {
                $data['message_details'] = $exception->getDetails();
            }
            $data['trace'] = $exception->getTraceAsString();
            $data['file'] = relativePath($exception->getFile(), SITE_DIR).' : '. $exception->getLine();
            $data['request'] = print_r(array_merge($_COOKIE, $_REQUEST, $_SERVER), true);
            $data['session'] = print_r($_SESSION, true);
        } else {
            $data['message'] = $exception;
            $data['request'] = '-';
            $data['session'] = '-';
        }
        $data['url'] = Helper\Url::getRequestUrl();

        if (STAGE == PRODUCTION_STAGE) {
            $url = new Helper\Url('http://errors.bazalt-cms.com/add/');
            $res = $url->post(array(
                'error' => json_encode($data),
                'host' => Helper\Url::getDomain()
            ));
        }
    }
}