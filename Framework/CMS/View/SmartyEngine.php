<?php

namespace Framework\CMS\View;

use Framework\System\View as View;

class SmartyEngine extends View\Engine
{
    public function fetch($folder, $file, View\Scope $view)
    {
        $vars = $view->getAssignedVars();

        $tpl = CMS_View_Smarty_Base::getInstance()->createTemplate($file, $vars);
        $tpl->setTemplateDir($folder);

        try {
            $ret = $tpl->fetch($file);
        } catch (SmartyException $e) {
            throw new Exception($e->getMessage() . '. Path: ' . $this->templateDir);
        }
        return $ret;
    }

    public function setLocaleDomain($domain)
    {
    }

    public function getLocalizationStrings($file)
    {
        # smarty open tag
        $ldq = preg_quote('{');
        # smarty close tag
        $rdq = preg_quote('}');
        # smarty command
        $cmd = preg_quote('tr');

        $content = @file_get_contents($file);

        if (empty($content)) {
            return array();
        }

        preg_match_all("/{$ldq}\s*({$cmd})\s*([^{$rdq}]*){$rdq}([^{$ldq}]*){$ldq}\/\\1{$rdq}/", $content, $matches);

        $strings = array();
        for ($i = 0; $i < count($matches[0]); $i++) {
            $plural = null;
            $comment = null;
            $text = $matches[0][$i];
            $start = strpos($content, $text);
            $line = substr_count($content, "\n", 0, $start) + 1;
            $content = substr_replace($content, '', $start, strlen($text));

            $string = $matches[3][$i];
            $args = $matches[2][$i];

            if (preg_match('/plural\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $args, $match)) {
                $plural = $match[1];
            }
            if (preg_match('/comment\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $args, $match)) {
                $comment = $match[1];
                $args = str_replace($match[0], '', $args);
            }

            $trString = array(
                'original' => $string,
                'plural' => $plural,
                'lines' => array($line)
            );
            if (isset($strings[$string])) {
                $trString = $strings[$string];
                $trString['lines'][] = $line;
            }

            /*if ($comment != null) {
                $tr->setDeveloperComment($comment);
            }*/
            $strings[$string] = $trString;
        }
        return $strings;
    }
}