<?php

namespace Framework\CMS;

/**
 * Файл з ключом, для шифрування даних
 */
if (!defined('KEYPASS_FILE')) {
    define('KEYPASS_FILE', SITE_DIR . '/.keypass');
}

class Option
{
    const DEFAULT_KEYPASS = 'bazalt';

    const CRYPT_VALUE_PREFIX = 'crypt:';

    protected static $options = array();

    public static function set($name, $value, $componentId = null, $siteId = null, $crypt = false)
    {
        if ($crypt) {
            $value = self::CRYPT_VALUE_PREFIX . self::cryptOption($value);
        }
        if (is_string($componentId)) {
            $component = Bazalt::getComponent($componentId);
            if ($component) {
                $componentId = $component->getCmsComponent()->id;
            }
        }
        if (!$siteId) {
            $siteId = Bazalt::getSiteId();
        }
        self::$options[$siteId][$name] = Model\Option::set($name, $value, $componentId, $siteId);
    }

    public static function get($name, $default = null, $crypt = false)
    {
        return self::getForSite($name, null, $default, $crypt = false);
    }

    public static function getForSite($name, $siteId = null, $default = null, $crypt = false)
    {
        if (!$siteId) {
            $siteId = Bazalt::getSiteId();
        }
        if (!isset(self::$options[$siteId])) {
            $options = Model\Option::getSiteOptions($siteId);
            foreach ($options as $option) {
                self::$options[$siteId][$option->name] = $option;
            }
        }
        if (!isset(self::$options[$siteId][$name])) {
            return $default;
        }
        $res = self::$options[$siteId][$name];
        $value = $res->value;
        if ($crypt || substr($value, 0, strlen(self::CRYPT_VALUE_PREFIX)) == self::CRYPT_VALUE_PREFIX) {
            $value = substr($value, strlen(self::CRYPT_VALUE_PREFIX));
            $value = self::decryptOption($value);
        }
        return $value;
    }

    public static function delete($name, $componentId = null, $siteId = null)
    {
        if (!$siteId) {
            $siteId = Bazalt::getSiteId();
        }
        if (isset(self::$options[$siteId][$name])) {
            unset(self::$options[$siteId][$name]);
        }
        $res = Model\Option::get($name, $componentId, $siteId);
        if ($res) {
            $res->delete();
            return true;
        }
        return false;
    }

    public static function cryptOption($value, $key = null)
    {
        if ($key == null) {
            $key = file_exists(KEYPASS_FILE) ? file_get_contents(KEYPASS_FILE) : self::DEFAULT_KEYPASS;
        }
        if (extension_loaded('mcrypt')) {
            $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);

            $value = mcrypt_encrypt(MCRYPT_CAST_256, md5($key), $value, MCRYPT_MODE_ECB, $iv);
        }
        return base64_encode($value);
    }

    public static function decryptOption($value, $key = null)
    {
        if ($key == null) {
            $key = file_exists(KEYPASS_FILE) ? file_get_contents(KEYPASS_FILE) : self::DEFAULT_KEYPASS;
        }
        $value = base64_decode($value);
        if (extension_loaded('mcrypt')) {
            $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);

            $value = mcrypt_decrypt(MCRYPT_CAST_256, md5($key), $value, MCRYPT_MODE_ECB, $iv);
        }
        return trim($value);
    }
}