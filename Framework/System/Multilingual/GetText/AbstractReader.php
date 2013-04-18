<?php

namespace Framework\System\Multilingual\GetText;

use Framework\System\Multilingual\Translation\Entry;

abstract class AbstractReader
{
    /**
     * strings
     * 
     * associative array with all [msgid => msgstr] entries
     * 
     * @access  protected
     * @var     array 
     */
    var $strings = array();

    /**
     * meta
     * 
     * associative array containing meta
     * information like project name or content type
     * 
     * @access  protected
     * @var     array 
     */
    var $meta = array();
   
    /**
     * file path
     * 
     * @access  protected
     * @var     string 
     */
    var $file = '';

    protected $nplurals;

    protected $pluralFunction;
    
    public function getEntries()
    {
        $strings = $this->strings;
        ksort($strings);
        return $strings;
    }

    public static function makeHeaders($translation)
    {
        if (is_array($translation)) {
            $translation = trim(implode("\n", $translation));
        }
        $headers = array();

        $translation = str_replace('\n', "\n", $translation);
        $lines = explode("\n", $translation);
        foreach ($lines as $line) {
            $parts = explode(':', $line, 2);
            if (!isset($parts[1])) {
                continue;
            }
            $headers[trim($parts[0])] = trim($parts[1]);
        }
        return $headers;
    }

    public function addEntry(Entry $entry)
    {
        $this->strings[$entry->getString()] = $entry;
    }

    public function setEntries(array $entries)
    {
        $this->strings = $entries;
    }

    public function setHeaders($headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
    }

    public function setHeader($header, $value)
    {
        $this->meta[$header] = $value;
        if ($header == 'Plural-Forms') {
            list($nplurals, $expression) = self::getPluralFromHeader($value);
            $this->nplurals = $nplurals;
            $this->pluralFunction = self::MakePluralFunction($nplurals, $expression);
        }
    }

    /**
     * Makes a function, which will return the right translation index, according to the
     * plural forms header
     */
    public static function MakePluralFunction($nplurals, $expression)
    {
        $expression = str_replace('n', '$n', $expression);
        $funcBody = "\$index = (int)($expression); return (\$index < $nplurals)? \$index : $nplurals - 1;";
        return create_function('$n', $funcBody);
    }

    public function getMetaInfo($name)
    {
        if (isset($this->meta[$name])) {
            return $this->meta[$name];
        }
        return null;
    }

    public function getFileTime()
    {
        $time = strToTime($this->getMetaInfo('PO-Revision-Date'));
        if ($time != null) {
            return $time;
        }
        return filemtime($this->file);
    }

    public function getPluralCount()
    {
        return $this->nplurals;
    }

    public function getPluralFormsNumber()
    {
        $forms = array();
        $func = $this->pluralFunction;
        if (!$func) {
            return array();
        }
        for ($i = 1; $i < 100; $i++) {
            $index = $func($i);
            if (!array_key_exists($index, $forms)) {
                $forms[$index] = $i;
            }
            if (count($forms) == $this->nplurals) {
                break;
            }
        }
        ksort($forms);
        return $forms;
    }

    public static function getPluralFromHeader($header)
    {
        if (preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $header, $matches)) {
            $nplurals = (int)$matches[1];
            $expression = trim(self::getPluralExression($matches[2]));
            return array($nplurals, $expression);
        } else {
            return array(2, 'n != 1');
        }
    }

    public function translate($string, $n = 0)
    {
        foreach ($this->strings as $entry) {
            if ($entry->getString() == trim($string)) {
                if ($entry->isPlural()) {
                    $func = $this->pluralFunction;
                    $index = $func($n);
                    $tr = $entry->getTranslations();
                    if (isset($tr[$index])) {
                        return sprintf($tr[$index], $n);
                    }
                    return false;
                } else {
                    return $entry->getTranslation();
                }
            }
        }
        return false;
    }

    public function testPlural()
    {
        $pl = array();
        for ($i = 0; $i < 100; $i++) {
            $func = $this->pluralFunction;
            $index = $func($i);
            $pl[$index][] = $i;
        }
        return $pl;
    }

    /**
     * Adds parantheses to the inner parts of ternary operators in
     * plural expressions, because PHP evaluates ternary oerators from left to right
     *
     * @param string $expression the expression without parentheses
     * @return string the expression with parentheses added
     */
    public static function getPluralExression($expression)
    {
        $expression .= ';';
        $res = '';
        $depth = 0;
        for ($i = 0; $i < strlen($expression); ++$i) {
           $char = $expression[$i];
           switch ($char) {
               case '?':
                   $res .= ' ? (';
                   $depth++;
                   break;
               case ':':
                   $res .= ') : (';
                  break;
               case ';':
                   $res .= str_repeat(')', $depth) . ';';
                   $depth= 0;
                   break;
               default:
                   $res .= $char;
           }
        }
        return rtrim($res, ';');
    }
}