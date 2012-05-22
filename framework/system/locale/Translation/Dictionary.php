<?php

class Locale_Translation_Dictionary
{
    protected $name;

    protected $folder;

    protected $templatesTime = null;

    protected $adapter = null;

    protected $entries = array();

    protected $translatedCount = null;

    protected $pluralCount = null;

    protected $pluralForms = null;

    public function __construct($name, $folder, Locale_Translation_AbstractAdapter $adapter = null)
    {
        $this->name = $name;
        $this->folder = $folder;
        $this->adapter = $adapter;
    }

    public function setPluralCount($count)
    {
        $this->pluralCount = $count;
    }

    public function getPluralCount()
    {
        return $this->pluralCount;
    }

    public function setPluralFormsNumber($num)
    {
        $this->pluralForms = $num;
    }

    public function getPluralFormsNumber()
    {
        return $this->pluralForms;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function isExists($string)
    {
        return isset($this->entries[$string]);
    }

    public function getEntry($string)
    {
        return $this->entries[$string];
    }

    public function templatesTime($value = null)
    {
        if ($value == null) {
            return $this->templatesTime;
        }
        $this->templatesTime = $value;
        return $this;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function addEntry(Locale_Translation_Entry $entry)
    {
        $this->translatedCount = null;
        $this->entries[$entry->getString()] = $entry;
    }

    public function addEntries(array $entries)
    {
        $this->translatedCount = null;
        $this->entries = array_merge($this->entries, $entries);
    }

    public function getEntries()
    {
        return $this->entries;
    }

    public function getTranslatedCount()
    {
        if ($this->translatedCount != null) {
            return $this->translatedCount;
        }
        $count = 0;
        foreach ($this->entries as $entry) {
            if ($entry->hasTranslated()) {
                $count++;
            }
        }
        $this->translatedCount = $count;
        return $count;
    }

    public function getCount()
    {
        return count($this->entries);
    }

    public function save($folder = null, $locale = null)
    {
        if ($this->adapter == null) {
            throw new Exception('For save dictionary must set adapter');
        }

        if ($locale == null) {
            $locale = Locale::getLanguage();
        }
        $this->adapter->saveDictionary($this, $folder, $locale);
    }

    public function saveAsTemplate($folder = null, $locale = null)
    {
        if ($this->adapter == null) {
            throw new Exception('For save dictionary must set adapter');
        }
        $this->adapter->saveDictionary($this, $folder);
    }
}