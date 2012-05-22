<?php

class CMS_Controller_Webservice extends CMS_Controller_Abstract
{
    public function preAction($action, $args)
    {
    }

    public function applicationWebserviceAction($service)
    {
        require_once APPS_DIR . PATH_SEP . str_replace('_', PATH_SEP, $service) . '.php';

        $service = new $service();

        if (getenv('QUERY_STRING') == 'js') {
            $service->showServiceJs();
        } else {
            $service->executeService();
            $service->showServiceInfo();
        }
        exit;
    }

    public function componentWebserviceAction($cms_component, $service)
    {
        $component = CMS_Bazalt::getComponent($cms_component);
        if (!$component) {
            throw new CMS_Exception_PageNotFound();
        }

        $comService = CMS_Bazalt::getComponentWebservice($component, $service);

        if (getenv('QUERY_STRING') == 'js') {
            $comService->showServiceJs();
        } else {
            $comService->executeService();
            $comService->showServiceInfo();
        }
        exit;
    }
}