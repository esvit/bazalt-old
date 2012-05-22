<?php

class Installer
{
    protected static $logs = array();

    protected static function log($message)
    {
        self::$logs []= $message;
    }

    public static function install($filename)
    {
        $tmp = self::createTempFolder(UPLOAD_DIR);
        if (empty($tmp)) {
            throw new Exception('Can\'t create temp folder');
        }
        self::unzip($filename, $tmp);
        $install = self::parseInstallFile($tmp);
        if (!$install) {
            self::deleteTempFolder($tmp);
            throw new Exception('Invalid installer file');
        }
        switch ($install->attribute('type')) {
            case 'component':
                self::installComponent($filename, $tmp, $install);
                break;
        }
        self::deleteTempFolder($tmp);
    }

    public static function deinstall($dir)
    {
        $install = self::parseInstallFile($dir);
        if (!$install) {
            throw new Exception('Invalid installer file');
        }
        switch ($install->attribute('type')) {
        case 'component': self::deinstallComponent($dir, $install);
        }
        self::deleteTempFolder($dir);
    }

    protected static function parseInstallFile($dir)
    {
        $file = glob($dir . PATH_SEP . 'install.xml');
        if (count($file) > 0) {
            $file = $file[0];
            $install = XmlParser::parse($file);
            if (!$install || $install->name() != 'install') {
                return false;
            }
            return $install;
        }
        return false;
    }

    protected static function installComponent($fileName, $dir, $node)
    {
        $component = $node->node('component');
        if (!$component) {
            throw new Exception('Install file must contains component section');
        }
        $componentName = $component->attribute('name');
        $path = COMPONENTS_DIR . PATH_SEP . strToLower($componentName);
        // create component dir
        mkdir($path, 0666);

        self::unzip($fileName, $path);

        if ($database = $node->node('database')) {
            self::createDatabaseStruct($database);
        }
        $com = CmsComponents::create($componentName, $component->attribute('title'));
        if ($options = $node->node('options')) {
            $options = $options->nodes('option');
            foreach ($options as $option) {
                CmsOptions::add($option->attribute('name'), $option->value(), $com->id);
            }
        }
        $com->enable();
    }

    protected static function deinstallComponent($dir, $node)
    {
        $component = $node->node('component');
        if (!$component) {
            throw new Exception('Install file must contains component section');
        }
        $componentName = $component->attribute('name');
        if ($database = $node->node('database')) {
            self::deleteDatabaseStruct($database);
        }
        $com = CmsComponents::getByName($componentName);

        $widgets = $com->Widgets->get();
        foreach ($widgets as $widget) {
            $widget->component_id = null;
            $widget->is_active = false;
            $widget->save();
        }
        $com->Services->removeAll();
        $com->Routes->removeAll();
        $com->Options->removeAll();
        $com->delete();
    }

    protected static function createDatabaseStruct($node)
    {
        foreach ($node->nodes('table') as $table) {
            $tableName = $table->attribute('name');
            if (ORM::isTableExists($tableName)) {
                throw new Exception('Table "' . $tableName . '" already exists');
            }
            self::log(sprintf('Create table "%s"', $tableName));
            $q = new ORMQuery($table->value());
            $q->exec();
        }
    }

    protected static function deleteDatabaseStruct($node)
    {
        foreach ($node->nodes('table') as $table) {
            $tableName = $table->attribute('name');
            ORM::dropTable($tableName);
            self::log(sprintf('Drop table "%s"', $tableName));
        }
    }

    protected static function unzip($filename, $folder)
    {
        $zip = new ZipArchive();
        if ($zip->open($filename) === true) {
            $zip->extractTo($folder);
            $zip->close();
            return true;
        }
        return false;
    }

    protected static function createTempFolder($path, $prefix = 'BAZALT')
    {
        $tempname = tempnam($path, $prefix);
        if (!$tempname) {
            return false;
        }
        if (!unlink($tempname)) {
            return false;
        }
        // Create the temporary directory and returns its name.
        if (mkdir($tempname)) {
            return $tempname;
        }
        return false;
    }

    protected static function deleteTempFolder($dir)
    {
        $files = glob($dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteTempFolder($file);
            } else {
                unlink($file);
            }
        }
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}