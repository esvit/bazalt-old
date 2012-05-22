<?php

class CMS_Option
{
    const DEFAULT_KEYPASS = 'bazalt';

    const CRYPT_VALUE_PREFIX = 'crypt:';

    protected static $options = null;

    public static function set($name, $value, $componentId = null, $siteId = null, $crypt = false)
    {
        if ($crypt) {
            $value = self::CRYPT_VALUE_PREFIX . self::cryptOption($value);
        }
        if (is_string($componentId)) {
            $component = CMS_Bazalt::getComponent($componentId);
            if ($component) {
                $componentId = $component->getCmsComponent()->id;
            }
        }
        self::$options[$name] = CMS_Model_Option::set($name, $value, $componentId, $siteId);
    }

    public static function get($name, $default = null, $crypt = false)
    {
        if (self::$options == null) {
            $options = CMS_Model_Option::getSiteOptions();
            foreach ($options as $option) {
                self::$options[$option->name] = $option;
            }
        }
        if (!isset(self::$options[$name])) {
            return $default;
        }
        $res = self::$options[$name];
        $value = $res->value;
        if ($crypt || substr($value, 0, strlen(self::CRYPT_VALUE_PREFIX)) == self::CRYPT_VALUE_PREFIX) {
            $value = substr($value, strlen(self::CRYPT_VALUE_PREFIX));
            $value = self::decryptOption($value);
        }
        return $value;
    }

    public static function delete($name, $componentId = null, $siteId = null)
    {
        if (isset(self::$options[$name])) {
            unset(self::$options[$name]);
        }
        $res = CMS_Model_Option::get($name, $componentId, $siteId);
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