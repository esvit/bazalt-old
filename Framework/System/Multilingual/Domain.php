<?php

namespace Framework\System\Multilingual;

use Framework\System\Multilingual\GetText as GetText;

require_once dirname(__FILE__) . '/helpers/__.php';
require_once dirname(__FILE__) . '/helpers/_p.php';

class Domain
{
    const DEFAULT_LANGUAGE = 'en';

    protected static $root = null;

    protected $adapter = null;

    /**
     * @var Domain[]
     */
    protected static $domains = [];

    /**
     * @var Domain
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
            self::$root = new Domain('root');
        }
        return self::$root;
    }

    public static function clearDomains()
    {
        self::$domains = [];
    }

    /**
     * @param null $localeFolder
     * @return Domain|string
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
            foreach (self::$domains as $domain) {
                $domain->language = $language;
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
     * @return Domain
     */
    public static function newDomain($domain, $folders = [], $localeFolder = null)
    {
        $oDomain = new Domain($domain, $folders, $localeFolder);
        self::$domains []= $oDomain;
        return $oDomain;
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