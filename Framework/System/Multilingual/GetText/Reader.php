<?php

namespace Framework\System\Multilingual\GetText;

use Framework\System\Multilingual\ArrayTranslate;
use Framework\System\Multilingual\TranslateAdapter;

ini_set('auto_detect_line_endings', 1);

class Reader extends ArrayTranslate
{
    protected $files = [];

    protected $folder = null;

    private $_lastLine = null;

    private $_useLastLine = null;

    public function translate($string, $pluralString = null, $count = null)
    {
        $lang = $this->scope->language();
        if (!isset($this->files[$lang]) || $this->folder != $this->scope->localeFolder()) {
            $this->files[$lang] = sprintf('%s/%s/%s.po', $this->scope->localeFolder(), $this->scope->language(), $this->scope->domain());

            $this->folder = $this->scope->localeFolder();

            if ($this->load($this->files[$lang]) && STAGE == TESTING_STAGE) {
                echo 'Load language file ' . $this->scope->language() . ', ' . $this->scope->domain() . "\n";
            }
        }
        return parent::translate($string, $pluralString, $count);
    }

    /**
     * Gives back the original string from a PO-formatted string
     *
     * @param string $string PO-formatted string
     * @return string enascaped string
     */
    public static function unpoify($string)
    {
        $lines = array_map(array('self', 'trimQuotes'), explode("\n", $string));

        return implode("\n", $lines);
    }

    public function load($file)
    {
        if (!file_exists($file)) {
            // @todo add notice
            //throw new \Exception('File "' . $this->file . '" not found');
            return false;
        }
        $lang = $this->scope->language();

        $f = fopen($file, 'r');
        if (!$f) {
            return false;
        }
        $lineno = 0;
        $header = null;
        while (true) {
            $entry = $this->readEntry($f, $lineno);
            if (!$entry) {
                break;
            }
            $str = $entry['string'];
            if (!empty($str)) {
                $this->messages[$lang][$str] = $entry;
            } else {
                $headers = explode("\n", $entry['translation']);
                foreach ($headers as $value) {
                    if (strpos($value, 'Plural-Forms') !== false) {
                        $this->pluralExpression(trim(substr($value, 13)));
                        break;
                    }
                }
            }
        }
        $this->_lastLine = null;
        fclose($f);
        return true;
    }

    private static function _isFinal($context)
    {
        return $context == 'msgstr' || $context == 'msgstr_plural';
    }

    public function readEntry($f, $lineno = 0)
    {
        $entry = [];
        // where were we in the last step
        // can be: comment, msgctxt, msgid, msgid_plural, msgstr, msgstr_plural
        $context = '';
        $msgstr_index = 0;

        while (true) {
            $lineno++;
            $line = $this->_useLastLine ? $this->_lastLine : fgets($f);
            $this->_lastLine = $line;
            $this->_useLastLine = false;

            if (!$line)  {
                if (feof($f)) {
                    if (self::_isFinal($context)) {
                        break;
                    } elseif (!$context) { // we haven't read a line and eof came
                        return null;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            if ($line == "\n") {
                continue;
            }
            $line = trim($line);
            if (preg_match('/^#/', $line, $m)) {
                # the comment is the start of a new entry
            } elseif (preg_match('/^msgctxt\s+(".*")/', $line, $m)) {
                if (self::_isFinal($context)) {
                    $this->_useLastLine = true;
                    $lineno--;
                    break;
                }
                if ($context && $context != 'comment') {
                    return false;
                }
                $context = 'msgctxt';
                $entry['context'] = self::unpoify($m[1]);
            } elseif (preg_match('/^msgid\s+(".*")/', $line, $m)) {
                if (self::_isFinal($context)) {
                    $this->_useLastLine = true;
                    $lineno--;
                    break;
                }
                if ($context && $context != 'msgctxt' && $context != 'comment') {
                    return false;
                }
                $context = 'msgid';
                $entry['string'] = self::unpoify($m[1]);
            } elseif (preg_match('/^msgid_plural\s+(".*")/', $line, $m)) {
                if ($context != 'msgid') {
                    return false;
                }
                $context = 'msgid_plural';
                $entry['plural'] = self::unpoify($m[1]);
            } elseif (preg_match('/^msgstr\s+(".*")/', $line, $m)) {
                if ($context != 'msgid') {
                    return false;
                }
                $context = 'msgstr';
                $entry['translation'] = self::unpoify($m[1]);
            } elseif (preg_match('/^msgstr\[(\d+)\]\s+(".*")/', $line, $m)) {
                if ($context != 'msgid_plural' && $context != 'msgstr_plural') {
                    return false;
                }
                $context = 'msgstr_plural';
                $msgstr_index = $m[1];
                $entry['translations'][$m[1]] = self::unpoify($m[2]);
            } elseif (preg_match('/^".*"$/', $line)) {
                $unpoified = self::unpoify($line);
                switch ($context) {
                    case 'msgid': $entry['string'] .= $unpoified; break;
                    case 'msgctxt': $entry['context'] .= $unpoified; break;
                    case 'msgid_plural': $entry['plural'] .= $unpoified; break;
                    case 'msgstr': $entry['translation'] .= $unpoified; break;
                    case 'msgstr_plural': $entry['translations'][$msgstr_index] .= $unpoified; break;
                    default: return false;
                }
            } else {
                return false;
            }
        }
        if (isset($entry['translations']) && array_filter($entry['translations'], create_function('$t', 'return $t || "0" === $t;')) == []) {
            $entry['translations'] = [];
        }
        return $entry;
    }

    public static function trimQuotes($s)
    {
        $s = trim($s);
        if ($s[0] == '"' && $s[strlen($s) - 1] == '"') {
            $s = substr($s, 1, -1);
        }
        $s = str_replace('\\\\', '_BAZALT_LOCALE_SLASHES_', $s);
        $escapes = array('\"', '\r', '\t', '\n');
        $chars   = array('"',  "\r", "\t", "\n");

        $s = str_replace($escapes, $chars, $s);
        $s = str_replace('_BAZALT_LOCALE_SLASHES_', '\\', $s);
        return $s;
    }
}