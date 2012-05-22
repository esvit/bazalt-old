<?php

class Metatags extends Object implements ISingleton
{
    const PAGE_TITLE = 'PAGE_TITLE';

    protected $title = '{SITE_TITLE}';

    protected $titleAppend = '';

    protected $keywords;

    protected $description;

    protected $classification;

    protected $author = 'EqualTeam (http://equalteam.net/)';

    protected $copyright;

    protected $language;

    protected $generator = 'Bazalt CMS (http://bazalt-cms.com/)';

    protected $searchIndex = array('index' => 'index', 'follow' => 'follow');

    protected static $metaInfo = array();

    public static function &Singleton($cls = null)
    {
        return Object::Singleton(__CLASS__);
    }

    public function __call($name, $params = array())
    {
        $type = $this->getType();
    
        if ($type->hasProperty($name)) {
            if (count($params) < 1 || $params[0] == null) {
                return $this->{$name};
            }
            $this->{$name} = $params[0];
            return $this;
        }
        throw new Exception('Function "' . $name . '" not found');
    }

    public static function set($property, $value)
    {
        self::$metaInfo[$property] = $value;
    }

    public static function getMetaInfo()
    {
        self::$metaInfo['SITE_TITLE'] = CMS_Option::get(CMS_Bazalt::SITENAME_OPTION);
        return self::$metaInfo;
    }

    public function noIndex()
    {
        $this->searchIndex['index'] = 'noindex';
        header('X-Robots-Tag: ' . implode(', ', $this->searchIndex), true);
        return $this;
    }

    public function noFollow()
    {
        $this->searchIndex['follow'] = 'nofollow';
        header('X-Robots-Tag: ' . implode(', ', $this->searchIndex), true);
        return $this;
    }

    public function noArchive()
    {
        $this->searchIndex['archive'] = 'noarchive';
        header('X-Robots-Tag: ' . implode(', ', $this->searchIndex), true);
        return $this;
    }

    public function noSnippet()
    {
        $this->searchIndex['snippet'] = 'nosnippet';
        header('X-Robots-Tag: ' . implode(', ', $this->searchIndex), true);
        return $this;
    }

    public function getTitle()
    {
        //$title = str_replace('{SITE_TITLE}', $this->getSiteName(), $this->title);
        $title = DataType_String::replaceConstants($this->title, self::getMetaInfo());

        return $title . $this->titleAppend;
    }

    public static function setTitle($title)
    {
        self::Singleton()->title = $title;
    }

    public function __toString()
    {
        $tags = array();
        if (!(defined('METATAGS_NO_TITLE') && METATAGS_NO_TITLE)) {
            $tags['title'] = '<title>' . htmlentities($this->getTitle(), null, 'UTF-8') . '</title>';
        }
        $tags['content-type'] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        if (!empty($this->generator)) {
            $tags['generator'] = '<meta name="generator" content="' . $this->generator . '" />';
        }
        if (!empty($this->keywords)) {
            $tags['keywords'] = '<meta name="keywords" content="' . $this->keywords . '" />';
        }
        if (!empty($this->description)) {
            $tags['description'] = '<meta name="description" content="' . $this->description . '" />';
        }
        if (!empty($this->classification)) {
            $tags['classification'] = '<meta name="classification" content="' . $this->classification . '" />';
        }
        if (!empty($this->searchIndex)) {
            $tags['robots'] = '<meta name="robots" content="' . implode(',', $this->searchIndex) . '" />';
        }

        $tags['author'] = '<meta content="' . $this->author . '" name="author" />';

        return implode("\n", $tags);
    }
}