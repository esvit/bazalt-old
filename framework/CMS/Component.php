<?php

abstract class CMS_Component extends CMS_Component_Base
{
    protected static $componentsRoles = null;

    protected $localeFolder = null;

    public function addService(CMS_Service_Base $service)
    {
        $this->services []= $service;
    }

    public function initComponent(CMS_Application $application)
    {
    }

    public function hasLocale($folder)
    {
        if (empty($folder)) {
            throw new Exception('You must enter folder name for component locale');
        }
        if (empty($this->baseDir)) {
            throw new Exception('Component baseDir is empty. Maybe incorect initializate component. Check method initComponent()');
        }
        $this->localeFolder = $this->baseDir . PATH_SEP . $folder;

        try {
            Locale_Translation::bindTextDomain($this->localeFolder, $this->name);
        } catch (Exception $e) {
        
        }
        return $this;
    }

    public function hasRight($action)
    {
        return $this->user->hasRight($this->name, $action);
    }

    public function addWebservice($name)
    {
        $fileName = Core_Autoload::getFilename($name);

        $keyName = basename($fileName) . (file_exists($fileName) ? filemtime($fileName) : '');
        $keyName .= CMS_Language::getCurrentLanguage()->id;
        $keyName .= $this->user->getRolesKey();
        $file = STATIC_DIR . '/webservices/' . $keyName . '.js';

        if (!is_file($file)) {
            $comService = CMS_Bazalt::getComponentWebservice($this, $name);

            $content = $comService->getServiceJs();
            file_put_contents($file, $content);
        }

        CMS_Webservice::addWebservice($file);
    }

    public function addScript($file, $name = null)
    {
        if (substr(strToLower($file),0,4) != 'http') {
            $nFile = $file;
            $file = $this->baseDir . '/media/scripts/' . $file;
            if (!file_exists($file)) {
                $file = $this->baseDir . '/assets/' . $nFile;
            }
        }
        Scripts::add($file, $name);
    }

    public function addStyle($file, $name = null, $condition = null)
    {
        if (substr(strToLower($file),0,4) != 'http') {
            $nFile = $file;
            $file = $this->baseDir . '/media/styles/' . $file;
            if (!file_exists($file)) {
                $file = $this->baseDir . '/assets/' . $nFile;
            }
        }
        Assets_CSS::add($file, $condition);
    }

    public function saveLog($action, $params)
    {
        CMS_Model_ChangeLog::saveLog($this, CMS_User::getUser(), $action, $params);
    }

    public function urlFor($name, $params = array())
    {
        $params['component'] = $this->name;
        return CMS_Mapper::urlFor($name, $params);
    }

    public function isExistsMenuType($type)
    {
        if (!($this instanceof CMS_Menu_HasItems)) {
            throw new Exception('Component "' . $this->name . '" must implement interface CMS_Menu_HasItems');
        }
        return array_key_exists($type, $this->getMenuTypes());
    }

    /**
     * Повертає усі ролі усіх компонентів
     */
    public static function getAllRoles()
    {
        if (!is_array(self::$componentsRoles)) {
            self::$componentsRoles = array();
            $components = CMS_Bazalt::getComponents();
            foreach ($components as $component) {
                $roles = $component->getRoles();
                if (!$roles) {
                    continue;
                }
                if (is_array($roles)) {
                    $roles = new CMS_Roles($roles);
                }
                if (!($roles instanceof CMS_Roles)) {
                    throw new Exception('Roles must extends CMS_Roles');
                }
                if ($roles != null) {
                    $roles->component($component);
                    self::$componentsRoles[$component->Name] = $roles;
                }
            }
        }
        return self::$componentsRoles;
    }
}