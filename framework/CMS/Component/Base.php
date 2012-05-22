<?php

/**
 * Базовий компонент, від якого повинні бути унаслідовані інші компоненти
 */
abstract class CMS_Component_Base extends Object implements ISingleton
{
    /**
     * Список усіх сервісів, які були ініціалізовані цим компонентом
     */
    protected $services = array();

    /**
     * Ім'я компонента
     */
    protected $name = null;

    /**
     * Запис компонента в БД
     */
    protected $cmsComponent = null;

    /**
     * Базова папка компонента
     */
    protected $baseDir = null;

    protected $view = null;

    protected $user = null;

    protected static $instances = array();

    public function __construct(CMS_Model_Component $component, $baseDir)
    {
        $this->name = $component->name;
        $this->baseDir = $baseDir;
        $this->cmsComponent = $component;

        self::$instances[strToLower($this->name)] = $this;

        $this->view = new CMS_View(array($this->name => $baseDir . PATH_SEP . CMS_Theme::TEMPLATES_DIRNAME), $this);

        $this->user = CMS_User::getUser();
    }

    public static function &getComponentInstance($name)
    {
        return self::$instances[strToLower($name)];
    }

    public function getCmsComponent()
    {
        return $this->cmsComponent;
    }

    public function initComponent(CMS_Application $application)
    {
    }

    public function initRoutes()
    {
    }

    /**
     * Викликається для ініціалізації бекенду компоненту
     */
    public function initBackend(CMS_Application $backend)
    {
    }

    /**
     * Повертає список ролей, які має компонент
     */
    public function getRoles()
    {
        return null;
    }

    public function getView()
    {
        return $this->view;
    }
}
