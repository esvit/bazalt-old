<?php

namespace Framework\CMS\View;

use Framework\System\View as View;

class TwigEngine extends View\Engine
{
    protected $localeDomain = null;

    protected static $extensions = array();

    public static function addExtension($extension)
    {
        self::$extensions []= $extension;
    }

    public static function fetchString($string, $vars = array())
    {
        using('Framework.Vendors.Twig');

        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader, array(
            'debug' => true,
            'auto_reload' => true,
            'cache' => TEMP_DIR . '/templates/Twig'
        ));

        //$twig->enableAutoReload();
        $twig->addExtension(new Twig\Extension());
        $twig->addExtension(new \Bazalt\Thumbs\Extension());

        foreach (self::$extensions as $ext) {
            $twig->addExtension($ext);
        }
        //$vars['bazalt_cms_locale_domain'] = $this->localeDomain;

        return $twig->render($string, $vars);
    }

    protected function getTwig($folder)
    {
        using('Framework.Vendors.Twig');

        $loader = new \Twig_Loader_Filesystem($folder);
        $twig = new \Twig_Environment($loader, array(
            'debug' => DEBUG,
            'auto_reload' => true,
            'cache' => TEMP_DIR . '/templates/Twig'
        ));

        //$twig->enableAutoReload();
        $twig->addExtension(new Twig\Extension());
        if (DEBUG) {
            $twig->addExtension(new \Twig_Extension_Debug());
        }
        $twig->addExtension(new \Bazalt\Thumbs\Extension());

        foreach (self::$extensions as $ext) {
            $twig->addExtension($ext);
        }

        return $twig;
    }

    public function fetch($folder, $file, View\Scope $view)
    {
        $vars = $view->variables();

        $vars['bazalt_cms_locale_domain'] = $this->localeDomain;

        $twig = $this->getTwig($folder);

        $template = $twig->loadTemplate($file);
        $content = $template->render($vars);
        return $content;
    }

    public function setLocaleDomain($domain)
    {
        $this->localeDomain = $domain;
    }

    public function getLocalizationStrings($file)
    {
        # smarty open tag
        $ldq = preg_quote('{%');
        # smarty close tag
        $rdq = preg_quote('%}');
        # smarty command
        $cmd = preg_quote('tr');

        $content = @file_get_contents($file);

        if (empty($content)) {
            return array();
        }

        $pattern = "/{$ldq}\s*({$cmd})\s*([^{$rdq}]*){$rdq}([^{$ldq}]*){$ldq}\s*end\\1\s*{$rdq}/";
        preg_match_all($pattern, $content, $matches);

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

            if (preg_match("/(.*){$ldq}\s*plural\s*(.*)\s*{$rdq}(.*)/i", $string, $match)) {
                $string = $match[1];
                $plural = $match[3];
            }
            /*if (preg_match('/comment\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $args, $match)) {
                $comment = $match[1];
                $args = str_replace($match[0], '', $args);
            }*/

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


        $pattern = "/{$ldq}\s*({$cmd})\s*([^{$rdq}]*){$rdq}(.*){$ldq}\s*plural\s*(.*)\s*{$rdq}(.*){$ldq}\s*end\\1\s*{$rdq}/";
        preg_match_all($pattern, $content, $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $text = $matches[0][$i];
            $start = strpos($content, $text);
            $line = substr_count($content, "\n", 0, $start) + 1;
            $content = substr_replace($content, '', $start, strlen($text));

            $string = $matches[3][$i];
            $plural = $matches[5][$i];

            $trString = array(
                'original' => $string,
                'plural' => $plural,
                'lines' => array($line)
            );
            if (isset($strings[$string])) {
                $trString = $strings[$string];
                $trString['lines'][] = $line;
            }
            $strings[$string] = $trString;
        }
        return $strings;
    }
}