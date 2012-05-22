<?php

class Admin_Webservice_Main extends CMS_Webservice_Application
{
    public function enableComponent($id, $enable)
    {
        $enable = ($enable == 'true');
        $component = CMS_Model_Component::getById($id);
        if (!$component) {
            throw new Exception('Component not found');
        }

        $site = CMS_Bazalt::getSite();
        if ($enable) {
            $site->Components->add($component);
        } else {
            $site->Components->remove($component);
        }
    }

    public function enableService($id, $enable)
    {
        $enable = ($enable == 'true');
        $service = CMS_Model_Services::getById($id);
        if (!$service) {
            throw new Exception('Service not found');
        }

        $site = CMS_Bazalt::getSite();
        if ($enable) {
            $site->Services->add($service);
        } else {
            $site->Services->remove($service);
        }
    }

    public function generateSecretKey()
    {
        $key = DataType_Guid::newGuid()->toString();
        CMS_Option::set(CMS_Bazalt::SECRETKEY_OPTION, $key);
        return $key;
    }
}