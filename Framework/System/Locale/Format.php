<?php

namespace Framework\System\Locale;

class Format
{
    protected static $loadData = null;

    protected static $days;

    protected static $abbrDays;

    protected static $months;

    protected static $abbrMonths;

    protected static $currencyFormats = array();

    protected static $numberFormats = array();

    protected static $dateFormats = array();

    protected static $timeFormats = array();

    protected static $dateTimeFormats = array();

    protected static $currentDateFormat = null;

    protected static $currentTimeFormat = null;

    protected static $currentDateTimeFormat = null;

    protected static $currentNumberFormat = null;

    protected static $currentCurrencyFormat = null;

    protected static $localeEncoding = null;

    protected static $info = null;

    private static function _e($data)
    {
        if (is_array($data)) {
            return array_map(array('self', '_e'), $data);
        }
        if (empty($data) || self::$localeEncoding == null) {
            return $data;
        }
        return iconv(self::$localeEncoding, 'UTF-8', $data);
    }

    public static function loadLocaleData($encoding)
    {
        self::$localeEncoding = $encoding;
        self::$info = Config::getInfo();

        $jan = $mon = mktime(1, 1, 1, 1, 1, 1990);
        $feb = $tue = mktime(1, 1, 1, 2, 6, 1990);
        $mar = $wed = mktime(1, 1, 1, 3, 7, 1990);
        $apr = $thu = mktime(1, 1, 1, 4, 5, 1990);
        $may = $fri = mktime(1, 1, 1, 5, 4, 1990);
        $jun = $sat = mktime(1, 1, 1, 6, 2, 1990);
        $jul = $sun = mktime(1, 1, 1, 7, 1, 1990);
        $aug = mktime(1, 1, 1, 8, 1, 1990);
        $sep = mktime(1, 1, 1, 9, 1, 1990);
        $oct = mktime(1, 1, 1, 10, 1, 1990);
        $nov = mktime(1, 1, 1, 11, 1, 1990);
        $dec = mktime(1, 1, 1, 12, 1, 1990);

        self::$days = array(
            self::_e(strftime('%A', $sun)),
            self::_e(strftime('%A', $mon)),
            self::_e(strftime('%A', $tue)),
            self::_e(strftime('%A', $wed)),
            self::_e(strftime('%A', $thu)),
            self::_e(strftime('%A', $fri)), 
            self::_e(strftime('%A', $sat))
        );

            
        self::$abbrDays = array(
            self::_e(strftime('%a', $sun)),
            self::_e(strftime('%a', $mon)),
            self::_e(strftime('%a', $tue)),
            self::_e(strftime('%a', $wed)),
            self::_e(strftime('%a', $thu)),
            self::_e(strftime('%a', $fri)),
            self::_e(strftime('%a', $sat))
        );

        self::$months = array(
            self::_e(strftime('%B', $jan)),
            self::_e(strftime('%B', $feb)),
            self::_e(strftime('%B', $mar)),
            self::_e(strftime('%B', $apr)),
            self::_e(strftime('%B', $may)),
            self::_e(strftime('%B', $jun)),
            self::_e(strftime('%B', $jul)),
            self::_e(strftime('%B', $aug)),
            self::_e(strftime('%B', $sep)),
            self::_e(strftime('%B', $oct)),
            self::_e(strftime('%B', $nov)),
            self::_e(strftime('%B', $dec))
        );
        
        self::$abbrMonths = array(
            self::_e(strftime('%b', $jan)),
            self::_e(strftime('%b', $feb)),
            self::_e(strftime('%b', $mar)),
            self::_e(strftime('%b', $apr)),
            self::_e(strftime('%b', $may)),
            self::_e(strftime('%b', $jun)),
            self::_e(strftime('%b', $jul)),
            self::_e(strftime('%b', $aug)),
            self::_e(strftime('%b', $sep)),
            self::_e(strftime('%b', $oct)),
            self::_e(strftime('%b', $nov)),
            self::_e(strftime('%b', $dec))
        );

        self::$currencyFormats = array(
            LOCALE_CURRENCY_LOCAL => array(
                self::$info['currency_symbol'],
                self::$info['int_frac_digits'],
                self::$info['mon_decimal_point'],
                self::$info['mon_thousands_sep'],
                self::$info['negative_sign'],
                self::$info['positive_sign'],
                self::$info['n_cs_precedes'],
                self::$info['p_cs_precedes'],
                self::$info['n_sep_by_space'],
                self::$info['p_sep_by_space'],
                self::$info['n_sign_posn'],
                self::$info['p_sign_posn'],
            ),
            LOCALE_CURRENCY_INTERNATIONAL => array(
                self::$info['int_curr_symbol'],
                self::$info['int_frac_digits'],
                self::$info['mon_decimal_point'],
                self::$info['mon_thousands_sep'],
                self::$info['negative_sign'],
                self::$info['positive_sign'],
                self::$info['n_cs_precedes'],
                self::$info['p_cs_precedes'],
                true,
                true,
                self::$info['n_sign_posn'],
                self::$info['p_sign_posn']
            )
        );
        
        self::$numberFormats = array(
            LOCALE_NUMBER_FLOAT => array(self::$info['frac_digits'], self::$info['decimal_point'], self::$info['thousands_sep']),
            LOCALE_NUMBER_INTEGER => array('0', self::$info['decimal_point'], self::$info['thousands_sep'])
        );

        if (Config::getLocale() == null) {
        
        }
        self::$dateFormats = Config::getLocale()->getDateFormats();
        self::$timeFormats = Config::getLocale()->getTimeFormats();

        if (!count(self::$dateTimeFormats)) {
            self::$dateTimeFormats = array(
                LOCALE_DATETIME_SHORT   => self::$dateFormats[LOCALE_DATETIME_SHORT]   . ', ' . self::$timeFormats[LOCALE_DATETIME_SHORT],
                LOCALE_DATETIME_MEDIUM  => self::$dateFormats[LOCALE_DATETIME_MEDIUM]  . ', ' . self::$timeFormats[LOCALE_DATETIME_MEDIUM],
                LOCALE_DATETIME_DEFAULT => self::$dateFormats[LOCALE_DATETIME_DEFAULT] . ', ' . self::$timeFormats[LOCALE_DATETIME_DEFAULT],
                LOCALE_DATETIME_LONG    => self::$dateFormats[LOCALE_DATETIME_LONG]    . ', ' . self::$timeFormats[LOCALE_DATETIME_LONG],
                LOCALE_DATETIME_FULL    => self::$dateFormats[LOCALE_DATETIME_FULL]    . ', ' . self::$timeFormats[LOCALE_DATETIME_FULL],
            );
        }
        self::setDefaults();
        self::$loadData = true;
    }

    /**
     * Set defaults
     *
     * @return  void
     */
    public static function setDefaults()
    {
        self::$currentTimeFormat     = self::$timeFormats[LOCALE_DATETIME_DEFAULT];
        self::$currentDateFormat     = self::$dateFormats[LOCALE_DATETIME_DEFAULT];
        self::$currentDateTimeFormat = self::$dateTimeFormats[LOCALE_DATETIME_DEFAULT];
        self::$currentNumberFormat   = self::$numberFormats[LOCALE_NUMBER_FLOAT];
        self::$currentCurrencyFormat = self::$currencyFormats[LOCALE_CURRENCY_INTERNATIONAL];
    }

    /**
     * Day name
     *
     * @return  mixed   Returns &type.string; name of weekday on success or
     *                  <classname>PEAR_Error</classname> on failure.
     * @param   int     $weekday    numerical representation of weekday
     *                              (0 = Sunday, 1 = Monday, ...)
     * @param   bool    $short  whether to return the abbreviation
     */
    public static function dayName($weekday, $short = false)
    {
        $weekday--;
        if (self::$loadData == null) {
            self::loadData();
        }
        if ($short) {
            if (!array_key_exists($weekday, self::$abbrDays)) {
                throw new Exception('Weekday "'.$weekday.'" is out of range.');
            }
            return self::$abbrDays[$weekday];
        } else {
            if (!array_key_exists($weekday, self::$days)) {
                throw new Exception('Weekday "'.$weekday.'" is out of range.');
            }
            return self::$days[$weekday];
        }
    }

    /**
     * Month name
     *
     * @return  mixed   Returns &type.string; name of month on success or
     *                  <classname>PEAR_Error</classname> on failure.
     * @param   int     $month  numerical representation of month
     *                          (0 = January, 1 = February, ...)
     * @param   bool    $short  whether to return the abbreviation
     * @throws Exception
     */
    public static function monthName($month, $short = false)
    {
        $month--;
        if (self::$loadData == null) {
            self::loadData();
        }
        if ($short) {
            if (!array_key_exists($month, self::$abbrMonths)) {
                throw new Exception('Month "' . $month . '" is out of range.');
            }
            return self::$abbrMonths[$month];
        } else {
            if (!array_key_exists($month, self::$months)) {
                throw new Exception('Month "'.$month.'" is out of range.');
            }
            return self::$months[$month];
        }
    }

    /**
     * Format a date
     *
     * @param int $ownFormat
     * @param int $timestamp
     *
     * @return string
     */
    public static function formatDate($ownFormat = null, $timestamp = null)
    {
        if ($ownFormat == 'atom') {
            $date = strftime('%Y-%m-%dT%H:%M:%S', ($timestamp != null) ? $timestamp : time());
            $date .= date('P', ($timestamp != null) ? $timestamp : time());
            return self::_e($date);
        }
        $format = self::$currentDateFormat;
        if ($ownFormat != null) {
            $format = array_key_exists($ownFormat, self::$dateFormats) ? self::$dateFormats[$ownFormat] : $ownFormat;
        }
        return self::_e(strftime($format, ($timestamp != null) ? $timestamp : time()));
    }

    /**
     * Format currency
     *
     * @param   numeric $value
     * @param   int     $ownFormat
     * @param   string  $ownSymbol
     *
     * @return  string
     */
    public static function formatCurrency($value, $ownFormat = null, $ownSymbol = null)
    {
        @list($symbol, $digits, $decpoint, $thseparator, 
              $sign['-'], $sign['+'], 
              $precedes['-'], $precedes['+'], 
              $separate['-'], $separate['+'], 
              $position['-'], $position['+']) = ($ownFormat != null) ? self::$currencyFormats[$ownFormat] : self::$currentCurrencyFormat;

        if ($ownSymbol != null) {
            $symbol = $ownSymbol;
        }

        # number_format the absolute value
        $amount = number_format(abs($value), $digits, $decpoint, $thseparator);

        $S = $value < 0 ? '-' : '+';

        # check posittion of the positive/negative sign(s)
        switch ($position[$S])
        {
            case 0: $amount  = '(' . $amount . ')';   break;
            case 1: $amount  = $sign[$S] . $amount; break;
            case 2: $amount .= $sign[$S];           break;
            case 3: $symbol  = $sign[$S] . $symbol; break;
            case 4: $symbol .= $sign[$S];           break;
        }

        if ($precedes[$S]) {
            # currency symbol precedes amount
            $amount = $symbol . ($separate[$S] ? ' ' : '') . $amount;
        }
        else {
            # currency symbol succedes amount
            $amount .= ($separate[$S] ? ' ' : '') . $symbol;
        }
        return $amount;
    }
    /**
     * Format a number
     *
     * @access  public
     * @return  string
     * @param   numeric $value
     * @param   int     $overrideFormat
     */
    function formatNumber($value, $ownFormat = null)
    {
        list($dig, $dec, $sep) = ($ownFormat != null) ? self::$numberFormats[$ownFormat] : self::$currentNumberFormat;
        return self::_e(number_format($value, $dig, $dec, $sep));
    }
    
    /**
     * Format a time
     *
     * @param   int     $timestamp
     * @param   int     $overrideFormat
     *
     * @return  string
     */
    public static function formatTime($ownFormat = null, $timestamp = null)
    {
        $format = self::$currentTimeFormat;
        if ($ownFormat != null) {
            $format = array_key_exists($ownFormat, self::$timeFormats) ? self::$timeFormats[$ownFormat] : $ownFormat;
        }
        return self::_e(strftime($format, ($timestamp != null) ? $timestamp : time()));
    }

    /**
     * Format a datetime
     *
     * @param   int     $timestamp
     * @param   int     $overrideFormat
     *
     * @return  string
     */
    public static function formatDateTime($ownFormat = null, $timestamp = null)
    {
        $format = self::$currentDateTimeFormat;
        if ($ownFormat != null) {
            $format = array_key_exists($ownFormat, self::$dateTimeFormats) ? self::$dateTimeFormats[$ownFormat] : $ownFormat;
        }
        return self::_e(strftime($format, ($timestamp = null) ? $timestamp : time()));
    }
    
    /**
     * Locale time
     *
     * @param   int     $timestamp
     *
     * @return  string
     */
    public static function time($timestamp = null)
    {
        return self::_e(strftime('%X', ($timestamp != null) ? $timestamp : time()));
    }

    /**
     * Locale date
     *
     * @param   int     $timestamp
     *
     * @return  string
     */
    public static function date($timestamp = null)
    {
        return self::_e(strftime('%x', ($timestamp != null) ? $timestamp : time()));
    }

    /**
     * Set currency format
     *
     * @param  int $format string
     * @return void
     */
    function setCurrencyFormat($format, $custom = false)
    {
        self::$currentCurrencyFormat = $format;
    }

    /**
     * Set number format
     *
     * @param  int $format string
     * @return void
     */
    public static function setNumberFormat($format, $custom = false)
    {
        self::$currentNumberFormat = $format;
    }

    /**
     * Set date format
     *
     * @param  int $format string
     * @return void
     */
    public static function setDateFormat($format, $custom = false)
    {
        self::$currentDateFormat = $format;
    }

    /**
     * Set time format
     *
     * @param  int $format string
     * @return void
     */
    public static function setTimeFormat($format)
    {
        self::$currentTimeFormat = $format;
    }

    /**
     * Set datetime format
     *
     * @param  int $format string
     *
     * @return void
     */
    public static function setDateTimeFormat($format)
    {
        self::$currentDateTimeFormat = $format;
    }
}