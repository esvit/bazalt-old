<?php

namespace Framework\System\Multilingual\GetText;

define('PO_MAX_LINE_LEN', 79);

ini_set('auto_detect_line_endings', 1);

class PoReader extends AbstractReader
{
    /**
     * Exports headers to a PO entry
     *
     * @return string msgid/msgstr PO entry for this PO file headers, doesn't contain newline at the end
     */
    public function exportHeaders()
    {
        $headerString = '';
        foreach($this->meta as $header => $value) {
            $headerString.= "$header: $value\n";
        }
        $poified = self::poify($headerString);
        $poified = 'msgid ""' . "\n" . 
                   'msgstr ' . rtrim($poified);
        return $poified;
    }

    /**
     * Exports all entries to PO format
     *
     * @return string sequence of mgsgid/msgstr PO strings, doesn't containt newline at the end
     */
    public function exportEntries()
    {
        //TODO sorting
        return implode("\n\n", array_map(array('self', 'exportEntry'), $this->getEntries()));
    }

    /**
     * Exports the whole PO file as a string
     *
     * @param bool $include_headers whether to include the headers in the export
     * @return string ready for inclusion in PO file string for headers and all the enrtries
     */
    public function export($includeHeaders = true)
    {
        $res = '';
        if ($includeHeaders) {
            $res .= $this->exportHeaders();
            $res .= "\n\n";
        }
        $res .= $this->exportEntries();
        return $res;
    }

    /**
     * Same as {@link export}, but writes the result to a file
     *
     * @param string $filename where to write the PO string
     * @param bool $include_headers whether to include tje headers in the export
     * @return bool true on success, false on error
     */
    public function exportToFile($filename)
    {
        $fh = fopen($filename, 'w');
        if (false === $fh) {
            throw new Exception('Cant write file "' . $filename . '"');
        }
        $export = $this->export();
        $res = fwrite($fh, $export);
        if (!$res) {
            throw new Exception('Cant write file "' . $filename . '"');
        }
        return fclose($fh);
    }

    /**
     * Formats a string in PO-style
     *
     * @param string $string the string to format
     * @return string the poified string
     */
    public static function poify($string)
    {
        $replaces = array('\\' => '\\\\', '"' => '\"', "\t" => '\t', "\r" => '\r');

        $string = str_replace(array_keys($replaces), array_values($replaces), trim($string));

        $po = '"' . implode('\n"' . "\n" . '"', explode("\n", $string)) . '"';
        
        // add empty string on first line for readbility
        //if (strpos($string, "\n") !== false && (substr_count($string, "\n") > 1 || !(substr($string, -strlen("\n")) === "\n"))) {
        //    $po = '""' . "\n" . $po;
        //}

        // remove empty strings
        $po = str_replace("\n" . '""', '', $po);
        return $po;
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
    
    /**
     * Inserts $with in the beginning of every new line of $string and
     * returns the modified string
     *
     * @param string $string prepend lines in this string
     * @param string $with prepend lines with this string
     */
    private static function prependEachLine($string, $with)
    {
        $php_with = var_export($with, true);
        $lines = explode("\n", $string);
        // do not prepend the string on the last empty line, artefact by explode
        if ("\n" == substr($string, -1)) {
            unset($lines[count($lines) - 1]);
        }
        $res = implode("\n", array_map(create_function('$x', "return $php_with.\$x;"), $lines));
        // give back the empty line, we ignored above
        if ("\n" == substr($string, -1)) {
            $res .= "\n";
        }
        return $res;
    }
    
    /**
     * Prepare a text as a comment -- wraps the lines and prepends #
     * and a special character to each line
     *
     * @param string $text the comment text
     * @param string $char character to denote a special PO comment,
     *     like :, default is a space
     */
    private function commentBlock($text, $char = ' ')
    {
        $text = wordwrap($text, PO_MAX_LINE_LEN - 3);
        return self::prependEachLine($text, '#' . $char . ' ');
    }

    /**
     * Builds a string from the entry for inclusion in PO file
     *
     * @static
     * @param object &$entry the entry to convert to po string
     * @return string|bool PO-style formatted string for the entry or
     *     false if the entry is empty
     */
    public static function exportEntry(&$entry)
    {
        if (is_null($entry->getString())) {
            return false;
        }
        $po = array();
        $comments = $entry->getTranslatorComment();
        if (!empty($comments)) {
            $po[] = self::commentBlock($comments);
        }
        $comments = $entry->getDeveloperComment();
        if (!empty($comments)) {
            $po[] = self::commentBlock($comments, '.');
        }
        $references = $entry->getReference();
        if (!empty($references)) {
            $po[] = self::commentBlock(implode(' ', $references), ':');
        }
        $flags = $entry->getFlags();
        if (!empty($flags)) {
            $po[] = self::commentBlock(implode(', ', $flags), ',');
        }
        $context = $entry->getContext();
        if (!empty($context)) {
            $po[] = 'msgctxt ' . self::poify($context);
        }
        $po[] = 'msgid ' . self::poify($entry->getString());
        $translations = $entry->getTranslations();
        if (!$entry->isPlural()) {
            $translation = empty($translations) ? '' : $translations[0];
            $po[] = 'msgstr ' . self::poify($translation);
        } else {
            $po[] = 'msgid_plural ' . self::poify($entry->getPlural());
            $translations = empty($translations) ? array('', '') : $translations;
            foreach ($translations as $i => $translation) {
                $po[] = 'msgstr[' . $i . '] ' . self::poify($translation);
            }
        }
        return implode("\n", $po);
    }

    public static function importFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new Exception('File "' . $filename . '" not found');
        }

        $class = __CLASS__;
        $file = new $class;
        $file->file = $filename;

        $f = fopen($filename, 'r');
        if (!$f) {
            return false;
        }
        $lineno = 0;
        $header = null;
        while (true) {
            list($entry, $line) = self::readEntry($f, $lineno);
            if (!$entry) {
                break;
            }
            $str = $entry->getString();
            if (empty($str)) {
                $header = $entry->getTranslations();
                $file->setHeaders(self::makeHeaders($header));
            } else {
                $file->addEntry($entry);
            }
        }
        self::readLine($f, 'clear');

        return $file;
    }

    private static function _isFinal($context)
    {
        return $context == 'msgstr' || $context == 'msgstr_plural';
    }

    public static function readEntry($f, $lineno = 0)
    {
        $entry = new \Framework\System\Multilingual\Translation\Entry();
        // where were we in the last step
        // can be: comment, msgctxt, msgid, msgid_plural, msgstr, msgstr_plural
        $context = '';
        $msgstr_index = 0;
        while (true) {
            $lineno++;
            $line = self::readLine($f);
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
                if (self::_isFinal($context)) {
                    self::readLine($f, 'put-back');
                    $lineno--;
                    break;
                }
                # comments have to be at the beginning
                if ($context && $context != 'comment') {
                    return false;
                }
                # add comment
                self::addCommentToEntry($entry, $line);
            } elseif (preg_match('/^msgctxt\s+(".*")/', $line, $m)) {
                if (self::_isFinal($context)) {
                    self::readLine($f, 'put-back');
                    $lineno--;
                    break;
                }
                if ($context && $context != 'comment') {
                    return false;
                }
                $context = 'msgctxt';
                $entry->setContext(self::unpoify($m[1]));
            } elseif (preg_match('/^msgid\s+(".*")/', $line, $m)) {
                if (self::_isFinal($context)) {
                    self::readLine($f, 'put-back');
                    $lineno--;
                    break;
                }
                if ($context && $context != 'msgctxt' && $context != 'comment') {
                    return false;
                }
                $context = 'msgid';
                $entry->setString(self::unpoify($m[1]));
            } elseif (preg_match('/^msgid_plural\s+(".*")/', $line, $m)) {
                if ($context != 'msgid') {
                    return false;
                }
                $context = 'msgid_plural';
                $entry->setPlural(self::unpoify($m[1]));
            } elseif (preg_match('/^msgstr\s+(".*")/', $line, $m)) {
                if ($context != 'msgid') {
                    return false;
                }
                $context = 'msgstr';
                $entry->addTranslation(self::unpoify($m[1]));
            } elseif (preg_match('/^msgstr\[(\d+)\]\s+(".*")/', $line, $m)) {
                if ($context != 'msgid_plural' && $context != 'msgstr_plural') {
                    return false;
                }
                $context = 'msgstr_plural';
                $msgstr_index = $m[1];
                $entry->addTranslation(self::unpoify($m[2]), $m[1]);
            } elseif (preg_match('/^".*"$/', $line)) {
                $unpoified = self::unpoify($line);
                switch ($context) {
                    case 'msgid': $entry->setString($entry->getString() . $unpoified); break;
                    case 'msgctxt': $entry->setContext($entry->getContext() . $unpoified); break;
                    case 'msgid_plural': $entry->setPlural($entry->getPlural() . $unpoified); break;
                    case 'msgstr': $entry->setTranslation($entry->getTranslation() . $unpoified); break;
                    case 'msgstr_plural': $entry->setTranslation($entry->getTranslation($msgstr_index) . $unpoified, $msgstr_index); break;
                    default: return false;
                }
            } else {
                return false;
            }
        }
        /*if (array() == array_filter($entry->translations, create_function('$t', 'return $t || "0" === $t;'))) {
            $entry->translations = array();
        }*/
        return array($entry, $lineno);
    }
   
    public static function readLine($f, $action = 'read')
    {
        static $last_line = '';
        static $use_last_line = false;
        if ($action == 'clear') {
            $last_line = '';
            return true;
        }
        if ($action == 'put-back') {
            $use_last_line = true;
            return true;
        }
        $line = $use_last_line ? $last_line : fgets($f);
        $last_line = $line;
        $use_last_line = false;
        return $line;
    }

    protected static function addCommentToEntry(&$entry, $poCommentLine)
    {
        $firstTwo = substr($poCommentLine, 0, 2);
        $comment = trim(substr($poCommentLine, 2));
        switch ($firstTwo) {
            case '#:': $entry->addReference($comment); break;
            case '#.': $entry->addDeveloperComment($comment); break;
            case '#,': $entry->addFlags(preg_split('/,\s*/', $comment)); break;
            default: $entry->addTranslatorComment($comment);
        }
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