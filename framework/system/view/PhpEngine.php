<?php

class View_PhpEngine extends View_BaseEngine
{
    public function fetch($folder, $file, View_Base $view)
    {
        $vars = $view->getAssignedVars();

        extract($vars);
        ob_start();

        include $folder . PATH_SEP . $file;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function setLocaleDomain($domain)
    {
    }

    protected static function getTextQuotesPattern($paramNum = 1)
    {
        // regexp for text
        $textInQuotes = '\s*'; // spaces
        $textInQuotes .= '([\'"])'; // quotes ' or "
        $textInQuotes .= '(.*?)'; // text
        $textInQuotes .= '\\' . (int)$paramNum; // same quotes
        $textInQuotes .= '\s*'; // spaces

        return $textInQuotes;
    }

    protected static function getTextDomainPattern($paramNum = 2)
    {
        $textDomain = '(,';
        $textDomain .= '\s*'; // spaces
        $textDomain .= "(['\"]?)"; // quotes ' or "
        $textDomain .= '(.*?)'; // text
        $textDomain .= "(\\2)?"; // same quotes
        $textDomain .= '\s*'; // spaces
        $textDomain .= ')?';

        return $textDomain;
    }

    public function getLocalizationStrings($file)
    {
        $content = @file_get_contents($file);

        if (empty($content)) {
            return array();
        }

        $preg = '/'; // start
        $preg .= '__\('; // function __(

        $preg .= self::getTextQuotesPattern();
        $preg .= self::getTextDomainPattern();

        $preg .= '\)'; // end function __
        $preg .= '/x'; // end
        
        preg_match_all($preg, $content, $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $plural = null;
            $text = $matches[0][$i];
            $start = strpos($content, $text);
            $line = substr_count($content, "\n", 0, $start) + 1;
            $content = substr_replace($content, '', $start, strlen($text));

            $string = $matches[2][$i]; // 2 - number of group with text, see regexp

            $trString = array(
                'original' => $string,
                'plural' => $plural,
                'lines' => array($line)
            );
            if (isset($strings[$string])) {
                $trString = $strings[$string];
                $trString['lines'][] = $line;
            }
            # $matches[2][$i] component
            /*if (!array_key_exists($string, $strings)) {
                $strings[$string] = new Locale_Translation_Entry($string, $plural);
            }*/
            //$tr = &$strings[$string];
            //$tr->addReference($file . ':' . $line);
            $strings[$string] = $trString;
        }

        $preg = '/'; // start
        $preg .= '_p\('; // function _p(

        $preg .= self::getTextQuotesPattern();

        $preg .= ',';

        $preg .= self::getTextQuotesPattern(3);

        $preg .= ',';

        $preg .= '\s*'; // spaces
        $preg .= '(.*?)'; // text
        $preg .= '\s*'; // spaces

        $preg .= self::getTextDomainPattern();

        $preg .= '\)'; // end function _p
        $preg .= '/x'; // end
        
        $matches= array();
        preg_match_all($preg, $content, $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $text = $matches[0][$i];
            $plural = $matches[4][$i];
            $start = strpos($content, $text);
            $line = substr_count($content, "\n", 0, $start) + 1;
            $content = substr_replace($content, '', $start, strlen($text));

            $string = $matches[2][$i]; // 2 - number of group with text, see regexp

            $trString = array(
                'original' => $string,
                'plural' => $plural,
                'lines' => array($line)
            );
            if (isset($strings[$string])) {
                $trString = $strings[$string];
                $trString['lines'][] = $line;
            }
            # $matches[2][$i] component
            /*if (!array_key_exists($string, $strings)) {
                $strings[$string] = new Locale_Translation_Entry($string, $plural);
            }*/
            //$tr = &$strings[$string];
            //$tr->addReference($file . ':' . $line);
            $strings[$string] = $trString;
        }
        return $strings;
    }
}