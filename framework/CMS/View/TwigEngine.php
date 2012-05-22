<?php

class CMS_View_TwigEngine extends View_BaseEngine
{
    protected $localeDomain = null;

    protected static $extensions = array();

    public static function addExtension($extension)
    {
        self::$extensions []= $extension;
    }

    protected function getTwig($folder)
    {
        using('Framework.Vendors.Twig');

        $loader = new Twig_Loader_Filesystem($folder);
        $twig = new Twig_Environment($loader, array(
            'auto_reload' => true,
            'cache' => TEMP_DIR . '/templates/Twig',
        ));

        //$twig->enableAutoReload();
        $twig->addExtension(new CMS_View_Twig_Extension());

        foreach (self::$extensions as $ext) {
            $twig->addExtension($ext);
        }

        return $twig;
    }

    public function fetch($folder, $file, View_Base $view)
    {
        $vars = $view->getAssignedVars();

        $vars['bazalt_cms_locale_domain'] = $this->localeDomain;

        $twig = $this->getTwig($folder);

        $template = $twig->loadTemplate($file);
        return $template->render($vars);
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