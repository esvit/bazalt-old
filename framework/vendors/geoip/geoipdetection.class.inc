<?php

using('Framework.System.Locale');

class GeoIPDetection extends Locale_Detector_Abstract
{
    protected $geoIpDatabase = null;

    public function __construct($options)
    {
        if (empty($options['database'])) {
            throw new Exception('Unknown GeoIP database location');
        }
        $this->geoIpDatabase = DataType_String::replaceConstants($options['database']);
    }

    //getenv(HTTP_X_FORWARDED_FOR)
    public function getInfoAboutUser()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $local = $this->getInfoByIP($ip);
        if (empty($local['country_name'])) {
            $local = $this->getInfoByIP($_SERVER['SERVER_ADDR']);
        }
        //$local['timezone'] = Get_Time_Zone($IPinfo['country_name'], $IPinfo['region']);

        return $local;
    }

    public function getInfoByIP($ip)
    {
        $info = array();
        if (!empty($this->geoIpDatabase) && file_exists($this->geoIpDatabase)) {
            require_once 'geoipcity.inc';
            require_once 'geoipregionvars.php';

            $geoIP = geoIP_Open($this->geoIpDatabase, GEOIP_STANDARD);
            $record = geoIP_Record_By_Addr($geoIP, $ip);

            if ($record) {
                $info['region_id'] = $record->region;
                $info['region'] = $GEOIP_REGION_NAME[$record->country_code][$info['region_id']];
                $info['country_name'] = geoIP_Country_Code_By_Addr($geoIP, $ip);
                $info['city'] = $record->city;
                $info['latitude'] = $record->latitude;
                $info['longitude'] = $record->longitude;
                $info['country_code'] = $record->country_code;
            }
            geoip_close($geoIP);
        }
        return $info;
    }

    public function detectLocale()
    {
        $user = $this->getInfoAboutUser();
        if (isset($user['country_code']))
        {
            return $user['country_code'];
        }
        return null;
    }
}