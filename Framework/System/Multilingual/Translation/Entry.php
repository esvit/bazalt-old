<?php

namespace Framework\System\Multilingual\Translation;

/**
 * TranslationEntry class encapsulates a translatable string
 */
class Entry
{
    /**
     * Whether the entry contains a string and its plural form, default is false
     *
     * @var boolean
     */
    protected $isPlural = false;

    /**
     * the string to translate, if omitted and empty entry will be created
     */
    protected $trString = null;

    /**
     * the plural form of the string, setting this will set {@link $isPlural} to true
     */
    protected $plural = null;

    /**
     * Comments left by translators
     */
    protected $translatorComment = '';

    /**
     * Comments left by developers
     */
    protected $devComment = '';

    protected $context = '';

    protected $translations = array();

    /**
     * places in the code this strings is used, in relative_to_root_path/file.php:linenum form
     */
    protected $references = array();

    /**
     * flags for string format
     */
    protected $flags = array();

    public function __construct($string = '', $plural = null, $flags = array())
    {
        $this->trString = $string;
        $this->isPlural = ($plural != null);
        $this->plural = $plural;
        $this->flags = $flags;
    }

    public function addTranslation($string, $id = null)
    {
        if ($id == null) {
            $this->translations []= $string;
        } else {
            $this->translations[$id] = $string;
        }
    }

    public function isPlural()
    {
        return $this->isPlural;
    }

    public function getPlural()
    {
        return $this->plural;
    }

    public function hasTranslated()
    {
        if ($this->isPlural) {
            foreach ($this->translations as $str) {
                if (!empty($str)) {
                    return true;
                }
            }
            return false;
        }
        return !empty($this->translations[0]);
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function getTranslation($index = null)
    {
        if ($this->isPlural && $index != null) {
            return $this->translations[$index];
        }
        return $this->translations[0];
    }

    public function setTranslation($string, $index = null)
    {
        if ($this->isPlural) {
            if (is_array($string)) {
                $this->translations = $string;
                return;
            } else if (is_string($string) && $index != null) {
                $this->translations[$index] = $string;
            }
        }
        $this->translations = array($string);
    }

    public function addReference($reference)
    {
        $this->references []= $reference;
    }

    public function getReference()
    {
        return $this->references;
    }

    public function getString()
    {
        return $this->trString;
    }

    public function setString($string)
    {
        $this->trString = $string;
    }

    public function setPlural($string)
    {
        $this->isPlural = true;
        $this->plural = $string;
    }

    public function setContext($string)
    {
        $this->context = $string;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setTranslatorComment($comment)
    {
        $this->translatorComment = $comment;
    }

    public function getTranslatorComment()
    {
        return $this->translatorComment;
    }

    public function addTranslatorComment($comment)
    {
        if (!empty($this->translatorComment)) {
            $this->translatorComment .= "\n";
        }
        $this->translatorComment .= $comment;
    }

    public function setDeveloperComment($comment)
    {
        $this->devComment = $comment;
    }

    public function getDeveloperComment()
    {
        return $this->devComment;
    }

    public function addDeveloperComment($comment)
    {
        if (!empty($this->devComment)) {
            $this->devComment .= "\n";
        }
        $this->devComment .= $comment;
    }

    public function addFlags($flag)
    {
        if (is_array($flag)) {
            $this->flags = array_merge($this->flags, $flag);
        } else {
            $this->flags []= $flag;
        }
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function toArray()
    {
        return array(
            'string' => $this->trString,
            'plural' => $this->plural,
            'devcomment' => $this->devComment,
            'trcomment' => $this->translatorComment,
            'translations' => $this->isPlural ? $this->translations : $this->translations[0],
            'isPlural' => $this->isPlural
        );
    }

    /**
    * Generates a unique key for this entry
    *
    * @return string|bool the key or false if the entry is empty
    */
    public function key()
    {
        if (is_null($this->trString)) {
            return false;
        }
        // prepend context and EOT, like in MO files
        return is_null($this->context)? $this->trString : $this->context . chr(4) . $this->trString;
    }

}