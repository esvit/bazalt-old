<?php

namespace Framework\System\Multilingual;

use Framework\System\Multilingual\GetText as GetText;

class Scope
{
    const DEFAULT_LANGUAGE = 'en';

    protected static $root = null;

    protected $adapter = null;

    /**
     * @var Scope[]
     */
    protected $childScopes = [];

    /**
     * @var Scope
     */
    protected $parentScope = null;

    /**
     * @var array
     */
    protected $folders = [];

    /**
     * @var string
     */
    protected $localeFolder = null;

    /**
     * @var string
     */
    protected $language = self::DEFAULT_LANGUAGE;

    /**
     * @todo Тимчасово ми використовуємо тільки gettext, якщо треба буде в подальшому зробимо інші
     */
    protected function __construct($domain, $folders = [], $localeFolder = null)
    {
        $this->adapter = new GetText\Reader($this);
        $this->localeFolder = $localeFolder;
        $this->folders = $folders;
        $this->domain = $domain;
    }

    public static function root()
    {
        if (self::$root == null) {
            self::$root = new Scope('root');
        }
        return self::$root;
    }

    public function clearScopes()
    {
        foreach ($this->childScopes as $scope) {
            $scope->clearScopes();
        }
        $this->childScopes = [];
    }

    /**
     * @param null $localeFolder
     * @return Scope|string
     */
    public function localeFolder($localeFolder = null)
    {
        if ($localeFolder != null) {
            $this->localeFolder = $localeFolder;
            return $this;
        }
        return $this->localeFolder;
    }

    public function language($language = null)
    {
        if ($language != null) {
            $this->language = $language;
            foreach ($this->childScopes as $scope) {
                $scope->language($language);
            }
            return $this;
        }
        return $this->language;
    }

    public function domain()
    {
        return $this->domain;
    }

    /**
     * Create new scope
     *
     * @param $domain
     * @param array $folders
     * @param null $localeFolder
     * @return Scope
     */
    public function newScope($domain, $folders = [], $localeFolder = null)
    {
        $scope = new Scope($domain, $folders, $localeFolder);
        $scope->parentScope = $this;
        $this->childScopes []= $scope;
        return $scope;
    }

    public function translate($string, $pluralString = null, $count = null)
    {
        $tr = $this->adapter->translate($string, $pluralString, $count);
        if (STAGE == TESTING_STAGE) {
            echo 'translate(' . $string . ' => ' . $tr . ')' . "\n";
        }
        return ($tr === false) ? $string : $tr;
    }
}