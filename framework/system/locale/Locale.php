<?php
/**
 * Locale
 *
 * @category   Locale
 * @package    BAZALT
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    SVN: $Revision: 154 $
 * @link       http://bazalt-cms.com/
 */

/**
 * Locale
 *
 * @category   Locale
 * @package    BAZALT
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    Release: $Revision: 154 $
 */
class Locale extends Object implements Config_IConfigurable
{
    protected static $instance = null;

    /**
     * Class wide Locale Constants
     *
     * @var array
     */
    private static $_locales = array(
        'af' => 'af_ZA', 'de' => 'de_DE',
        'en' => 'en_US', 'fr' => 'fr_FR',
        'it' => 'it_IT', 'es' => 'es_ES',
        'pt' => 'pt_PT', 'sv' => 'sv_SE',
        'nb' => 'nb_NO', 'nn' => 'nn_NO',
        'no' => 'no_NO', 'fi' => 'fi_FI',
        'is' => 'is_IS', 'da' => 'da_DK',
        'nl' => 'nl_NL', 'pl' => 'pl_PL',
        'sl' => 'sl_SI', 'hu' => 'hu_HU',
        'ru' => 'ru_RU', 'cs' => 'cs_CZ',
        'uk' => 'uk_UA'
    );

    /**
     * Class wide Locale Constants
     *
     * @var array $_territoryData
     */
    private static $_territoryData = array(
        'AD' => 'ca_AD', 'AE' => 'ar_AE', 'AF' => 'fa_AF', 'AG' => 'en_AG', 'AI' => 'en_AI',
        'AL' => 'sq_AL', 'AM' => 'hy_AM', 'AN' => 'pap_AN', 'AO' => 'pt_AO', 'AQ' => 'und_AQ',
        'AR' => 'es_AR', 'AS' => 'sm_AS', 'AT' => 'de_AT', 'AU' => 'en_AU', 'AW' => 'nl_AW',
        'AX' => 'sv_AX', 'AZ' => 'az_Latn_AZ', 'BA' => 'bs_BA', 'BB' => 'en_BB', 'BD' => 'bn_BD',
        'BE' => 'nl_BE', 'BF' => 'mos_BF', 'BG' => 'bg_BG', 'BH' => 'ar_BH', 'BI' => 'rn_BI',
        'BJ' => 'fr_BJ', 'BL' => 'fr_BL', 'BM' => 'en_BM', 'BN' => 'ms_BN', 'BO' => 'es_BO',
        'BR' => 'pt_BR', 'BS' => 'en_BS', 'BT' => 'dz_BT', 'BV' => 'und_BV', 'BW' => 'en_BW',
        'BY' => 'be_BY', 'BZ' => 'en_BZ', 'CA' => 'en_CA', 'CC' => 'ms_CC', 'CD' => 'sw_CD',
        'CF' => 'fr_CF', 'CG' => 'fr_CG', 'CH' => 'de_CH', 'CI' => 'fr_CI', 'CK' => 'en_CK',
        'CL' => 'es_CL', 'CM' => 'fr_CM', 'CN' => 'zh_Hans_CN', 'CO' => 'es_CO', 'CR' => 'es_CR',
        'CU' => 'es_CU', 'CV' => 'kea_CV', 'CX' => 'en_CX', 'CY' => 'el_CY', 'CZ' => 'cs_CZ',
        'DE' => 'de_DE', 'DJ' => 'aa_DJ', 'DK' => 'da_DK', 'DM' => 'en_DM', 'DO' => 'es_DO',
        'DZ' => 'ar_DZ', 'EC' => 'es_EC', 'EE' => 'et_EE', 'EG' => 'ar_EG', 'EH' => 'ar_EH',
        'ER' => 'ti_ER', 'ES' => 'es_ES', 'ET' => 'en_ET', 'FI' => 'fi_FI', 'FJ' => 'hi_FJ',
        'FK' => 'en_FK', 'FM' => 'chk_FM', 'FO' => 'fo_FO', 'FR' => 'fr_FR', 'GA' => 'fr_GA',
        'GB' => 'en_GB', 'GD' => 'en_GD', 'GE' => 'ka_GE', 'GF' => 'fr_GF', 'GG' => 'en_GG',
        'GH' => 'ak_GH', 'GI' => 'en_GI', 'GL' => 'iu_GL', 'GM' => 'en_GM', 'GN' => 'fr_GN',
        'GP' => 'fr_GP', 'GQ' => 'fan_GQ', 'GR' => 'el_GR', 'GS' => 'und_GS', 'GT' => 'es_GT',
        'GU' => 'en_GU', 'GW' => 'pt_GW', 'GY' => 'en_GY', 'HK' => 'zh_Hant_HK', 'HM' => 'und_HM',
        'HN' => 'es_HN', 'HR' => 'hr_HR', 'HT' => 'ht_HT', 'HU' => 'hu_HU', 'ID' => 'id_ID',
        'IE' => 'en_IE', 'IL' => 'he_IL', 'IM' => 'en_IM', 'IN' => 'hi_IN', 'IO' => 'und_IO',
        'IQ' => 'ar_IQ', 'IR' => 'fa_IR', 'IS' => 'is_IS', 'IT' => 'it_IT', 'JE' => 'en_JE',
        'JM' => 'en_JM', 'JO' => 'ar_JO', 'JP' => 'ja_JP', 'KE' => 'en_KE', 'KG' => 'ky_Cyrl_KG',
        'KH' => 'km_KH', 'KI' => 'en_KI', 'KM' => 'ar_KM', 'KN' => 'en_KN', 'KP' => 'ko_KP',
        'KR' => 'ko_KR', 'KW' => 'ar_KW', 'KY' => 'en_KY', 'KZ' => 'ru_KZ', 'LA' => 'lo_LA',
        'LB' => 'ar_LB', 'LC' => 'en_LC', 'LI' => 'de_LI', 'LK' => 'si_LK', 'LR' => 'en_LR',
        'LS' => 'st_LS', 'LT' => 'lt_LT', 'LU' => 'fr_LU', 'LV' => 'lv_LV', 'LY' => 'ar_LY',
        'MA' => 'ar_MA', 'MC' => 'fr_MC', 'MD' => 'ro_MD', 'ME' => 'sr_Latn_ME', 'MF' => 'fr_MF',
        'MG' => 'mg_MG', 'MH' => 'mh_MH', 'MK' => 'mk_MK', 'ML' => 'bm_ML', 'MM' => 'my_MM',
        'MN' => 'mn_Cyrl_MN', 'MO' => 'zh_Hant_MO', 'MP' => 'en_MP', 'MQ' => 'fr_MQ', 'MR' => 'ar_MR',
        'MS' => 'en_MS', 'MT' => 'mt_MT', 'MU' => 'mfe_MU', 'MV' => 'dv_MV', 'MW' => 'ny_MW',
        'MX' => 'es_MX', 'MY' => 'ms_MY', 'MZ' => 'pt_MZ', 'NA' => 'kj_NA', 'NC' => 'fr_NC',
        'NE' => 'ha_Latn_NE', 'NF' => 'en_NF', 'NG' => 'en_NG', 'NI' => 'es_NI', 'NL' => 'nl_NL',
        'NO' => 'nb_NO', 'NP' => 'ne_NP', 'NR' => 'en_NR', 'NU' => 'niu_NU', 'NZ' => 'en_NZ',
        'OM' => 'ar_OM', 'PA' => 'es_PA', 'PE' => 'es_PE', 'PF' => 'fr_PF', 'PG' => 'tpi_PG',
        'PH' => 'fil_PH', 'PK' => 'ur_PK', 'PL' => 'pl_PL', 'PM' => 'fr_PM', 'PN' => 'en_PN',
        'PR' => 'es_PR', 'PS' => 'ar_PS', 'PT' => 'pt_PT', 'PW' => 'pau_PW', 'PY' => 'gn_PY',
        'QA' => 'ar_QA', 'RE' => 'fr_RE', 'RO' => 'ro_RO', 'RS' => 'sr_Cyrl_RS', 'RU' => 'ru_RU',
        'RW' => 'rw_RW', 'SA' => 'ar_SA', 'SB' => 'en_SB', 'SC' => 'crs_SC', 'SD' => 'ar_SD',
        'SE' => 'sv_SE', 'SG' => 'en_SG', 'SH' => 'en_SH', 'SI' => 'sl_SI', 'SJ' => 'nb_SJ',
        'SK' => 'sk_SK', 'SL' => 'kri_SL', 'SM' => 'it_SM', 'SN' => 'fr_SN', 'SO' => 'sw_SO',
        'SR' => 'srn_SR', 'ST' => 'pt_ST', 'SV' => 'es_SV', 'SY' => 'ar_SY', 'SZ' => 'en_SZ',
        'TC' => 'en_TC', 'TD' => 'fr_TD', 'TF' => 'und_TF', 'TG' => 'fr_TG', 'TH' => 'th_TH',
        'TJ' => 'tg_Cyrl_TJ', 'TK' => 'tkl_TK', 'TL' => 'pt_TL', 'TM' => 'tk_TM', 'TN' => 'ar_TN',
        'TO' => 'to_TO', 'TR' => 'tr_TR', 'TT' => 'en_TT', 'TV' => 'tvl_TV', 'TW' => 'zh_Hant_TW',
        'TZ' => 'sw_TZ', 'UA' => 'uk_UA', 'UG' => 'sw_UG', 'UM' => 'en_UM', 'US' => 'en_US',
        'UY' => 'es_UY', 'UZ' => 'uz_Cyrl_UZ', 'VA' => 'it_VA', 'VC' => 'en_VC', 'VE' => 'es_VE',
        'VG' => 'en_VG', 'VI' => 'en_VI', 'VU' => 'bi_VU', 'WF' => 'wls_WF', 'WS' => 'sm_WS',
        'YE' => 'ar_YE', 'YT' => 'swb_YT', 'ZA' => 'en_ZA', 'ZM' => 'en_ZM', 'ZW' => 'sn_ZW'
    );

    protected static $fallbacks = array(
        'no_NO' => 'nb_NO',
        'nb_NO' => 'no_NO',
    );

    protected static $info = array();

    protected static $last = array();

    protected static $defaultLocale = 'en';

    protected static $localeObject = null;

    protected static $localeLanguage = null;

    protected static $localeEncoding = null;

    protected static $currentLocale = null;

    protected static $detectors = array();

    protected static $allowLocales = array();

    protected static $denyLocales = array();

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            Configuration::init('locale', self::$instance);
        }
        return self::$instance;
    }

    public function configure($config)
    {
        $defaultLocale = isset($config['default'])    ? $config['default'] : null;
        $autodetect    = isset($config['autodetect']) ? $config['autodetect'] : false;

        if (!empty($defaultLocale)) {
            self::$defaultLocale = $defaultLocale;
            self::setLocale($defaultLocale);
        }
        if (isset($config['detection'])) {
            $detections = $config['detection'];

            $allow = isset($config['allow']) ? $config['allow'] : null;
            $deny  = isset($config['deny'])  ? $config['deny']  : null;

            if (!empty($allow)) {
                self::$allowLocales = explode(',', $allow);
            }
            if (!empty($deny)) {
                self::$denyLocales = explode(',', $deny);
            }
            foreach ($detections as $detector) {
                $class = $detector->value;
                $attributes = $detector->attributes;
                if (isset($attributes['namespace'])) {
                    using($attributes['namespace']);
                    unset($attributes['namespace']);
                }
                self::$detectors []= Type::getObjectInstance($class, array('options' => $attributes), 'Locale_Detector_Abstract');
            }
        }
        if ($autodetect) {
            self::setUserLocale();
        }
        return $this;
    }

    public static function getDefaultLocale()
    {
        return self::$defaultLocale;
    }

    /**
     * Get several locale specific information
     * 
     * @see     http://www.php.net/localeconv
     * 
     * <code>
     * $locale = I18Nv2::setLocale('en_US');
     * $dollar = I18Nv2::getInfo('currency_symbol');
     * $point  = I18Nv2::getInfo('decimal_point');
     * </code>
     * 
     * @return  mixed
     * @param   string  $part
     */
    public static function getInfo($part = null)
    {
        return ($part != null) ? self::$info[$part] : self::$info;
    }

    public static function getLast()
    {
        return self::$last;
    }

    /**
     * Set Locale
     * 
     * Example:
     * <code>
     * I18Nv2::setLocale('en_GB');
     * </code>
     * 
     * @return  mixed   &type.string; used locale or false on failure
     * @param   string  $locale     a valid locale like en_US or de_DE
     */
    public static function setLocale($locale)
    {
        Logger::getInstance()->info('set locale ' . $locale);
        if (array_key_exists($locale, self::$_territoryData)) {
            $locale = self::$_territoryData[$locale];
        }
        if (!self::isAllowLocale($locale)) {
            return null;
        }
        // get complete standard locale code (en => en_US)
        if (array_key_exists($locale, self::$_locales)) {
            $locale = self::$_locales[$locale];
        }
        // get Win32 locale code (en_US => enu)
        if (OS == OS_WIN) { // @codeCoverageIgnoreStart
            $windows = LocaleInfo::getWindowsLocales();
            $setlocale = array_key_exists($locale, $windows) ? $windows[$locale] : $locale;
        } else {            // @codeCoverageIgnoreEnd
            $setlocale = $locale . '.UTF-8'; // и неипёт
        }
        $syslocale = setLocale(LC_ALL, $setlocale);

        // if the locale is not recognized by the system, check if there 
        // is a fallback locale and try that, otherwise return false
        if (!$syslocale) {
            if (array_key_exists($locale, self::$fallbacks)) {
                // avoid endless recursion with circular fallbacks
                $trylocale = self::$fallbacks[$locale];
                unset(self::$fallbacks[$locale]);
                if ($retlocale = self::setLocale($trylocale)) {
                    $fallbacks[$locale] = $trylocale;
                    return Locale::Singleton();
                }
            }
            return null;
        }

        $language = substr($locale, 0, 2);

        self::$localeLanguage = $language;
        if (OS == OS_WIN) { // @codeCoverageIgnoreStart
            @putEnv('LANG='     . $language);
            @putEnv('LANGUAGE=' . $language);
        } else {            // @codeCoverageIgnoreEnd
            @putEnv('LANG='     . $locale);
            @putEnv('LANGUAGE=' . $locale);
        }
        
        // unshift locale stack
        array_unshift(self::$last, 
            array(
                'locale'    => $locale,
                'language'  => $language,
                'syslocale' => $syslocale
            )
        );

        self::$localeEncoding = null;
        if(!strpos($syslocale, 'UTF-8')) {
            $encoding = explode('.', $syslocale);
            self::$localeEncoding = ((OS == OS_WIN) ? 'Windows-' : 'CP') . end($encoding);
        }
        self::$info = localeConv();
        self::_setCurrentLocale($locale);

        return Locale::Singleton();
    }

    public static function &getLocale()
    {
        return self::$localeObject;
    }
    
    public static function translit($string)
    {
        if (self::$localeObject == null) {
        
        }
        return self::$localeObject->translit($string);
    }

    private static function _setCurrentLocale($locale)
    {
        self::$currentLocale = $locale;

        self::$localeObject = self::findLocaleByAlias(self::languageOf($locale));

        LocaleFormat::loadLocaleData(self::$localeEncoding);
    }

    /**
     * Split locale code
     * 
     * Splits locale codes into its language and country part
     *
     * @return  array
     * @param   string  $locale
     */
    public static function splitLocale($locale)
    {
        @list($l, $c) = preg_split('/[_-]/', $locale, 2, PREG_SPLIT_NO_EMPTY);
        return array($l, $c);
    }

    /**
     * Get language code of locale
     *
     * @return  string
     * @patram  string  $locale
     */
    public static function languageOf($locale)
    {
        return current(self::splitLocale($locale));
    }

    /**
     * Get country code of locale
     *
     * @return  string
     * @param   string  $locale
     */
    public static function countryOf($locale)
    {
        return end(self::splitLocale($locale));
    }

    public static function findLocaleByAlias($alias)
    {
        $className = 'Locale_Language_' .  ucfirst(strToLower($alias));
        if (!class_exists($className)) {
            throw new Exception('Invalid locale "' . $className . '"');
        }
        return Object::Singleton($className);
    }

    public static function getLanguage()
    {
        return self::$localeLanguage;
    }

    public static function getDetectors()
    {
        return self::$detectors;
    }

    public static function isAllowLocale($locale)
    {
        $locale = trim($locale);
        if (@count(self::$denyLocales) > 0) {
            if (in_array($locale, self::$denyLocales)) {
                return false;
            }
        }
        if (@count(self::$allowLocales) > 0) {
            if (in_array($locale, self::$allowLocales)) {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }

    public static function setUserLocale()
    {
        $locales = array();
        foreach (self::$detectors as $detector) {
            $locale = $detector->detectLocale();
            if ($locale != null) {
                if (!is_array($locale)){
                    $locale = array($locale);
                }
                foreach ($locale as $local) {
                    $local = trim($local);
                    if (!self::isAllowLocale($local)) {
                        continue;
                    }
                    if ($res = self::setLocale($local)) {
                        return $res->getlocale();
                    }
                }
            }
        }
        return null;
    }
}