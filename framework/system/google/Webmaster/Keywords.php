<?php

class Google_Webmaster_Keywords extends Google_Webmaster_Tools
{

    function __construct(Google_Auth $auth)
    {
        parent::__construct($auth);

        $this->service = "keywords";
    }

    public function getDataResult($site = '')
    {
        $external = array();
        $internal = array();
        $xml = $this->executeService($site, "keywords");

        if(is_integer($xml)) {
            $this->errorCode = $xml;
            echo $xml;
            return false;
        }
        $data = array();
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $keywords = $dom->getElementsByTagName("keyword");

        foreach($keywords as $keyword) {
            if(strcasecmp($keyword->getAttribute("source"),"external")==0) {
                array_push($external, $keyword->nodeValue);
            } else {
                array_push($internal, $keyword->nodeValue);
            }

        }
        return array(
                "external" => $external,
                "internal" => $internal
        );
    }
}