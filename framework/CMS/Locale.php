<?php

using('Framework.System.Locale');

class CMS_Locale
{
    public static function hasLocale($path, $domain)
    {
        Locale_Translation::bindTextDomain($path, $domain);
    }

    /**
     * Повертає список файлів у яких потрібно знайти переклади
     */
    protected static function getLocalizableFiles($folders = array())
    {
        if (!is_array($folders)) {
            $folders = array($folders);
        }
        $extensions = CMS_View::getExtensions();

        $ignore = array('.svn');

        $files = array();
        foreach ($folders as $dir) {
            if (!is_dir($dir)) {
                return null;
            }
            $d = dir($dir);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..' || in_array($entry, $ignore)) {
                    continue;
                }
                $entry = $dir . '/' . $entry;

                if (is_dir($entry)) {
                    # if a directory, go through it
                    $files = array_merge($files, self::getLocalizableFiles($entry));
                } else {
                    # if file, parse only if extension is matched
                    $pi = pathinfo($entry);

                    if (isset($pi['extension']) && in_array($pi['extension'], $extensions)) {
                        $files[$entry] = $pi['extension'];
                    }
                }
            }
        }
        $d->close();
        return $files;
    }

    public static function scanFolders($name, $folders = array())
    {
        $files = self::getLocalizableFiles($folders);

        $engines = CMS_View::getEngines();
        $strings = array();
        $dict = new Locale_Translation_Dictionary($name, $folder, Locale_Translation::Singleton()->getAdapter());

        foreach ($files as $file => $extension) {
            $engine = CMS_View::getEngine($engines[$extension]);

            $strings = $engine->getLocalizationStrings($file);
            foreach ($strings as $string) {
                $str = $string['original'];
                if ($dict->isExists($str)) {
                    $entry = $dict->getEntry($str);
                } else {
                    $entry = new Locale_Translation_Entry($str, $string['plural']);
                    foreach ($string['lines'] as $line) {
                        $entry->addReference( relativePath($file, SITE_DIR) . ':' . $line );
                    }
                }

                $dict->addEntry($entry);
            }
        }
        return $dict;
    }
}