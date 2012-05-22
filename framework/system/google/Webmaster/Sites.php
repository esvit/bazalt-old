<?php

class Google_Webmaster_Sites extends Google_Webmaster_Tools
{
    function __construct(Google_Auth $auth)
    {
        parent::__construct($auth);

        $this->service = 'sites';
    }

    public function addSite($site)
    {
        $url = $this->baseUrl."sites/";
        $xml ="<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'><atom:content src=\"".$site."\" /></atom:entry>";
        return $this->requestHttpPutXml($url, $xml);
    }

    public function deleteSite($site)
    {
        return $this->requestHttpDelete($site);
    }


    public function getSites()
    {
        $rawSites = $this->_callWMT('get','https://www.google.com/webmasters/tools/feeds/sites','',array(),array('entry'));
        $sites = array();
        foreach ($rawSites['feed']['entry'] as $entry) {
            $site = explode('/', $entry['title']);
            $site = $site[2];
            $sites[$site] = $entry;
        }
        return $sites; 
    } 



    public function setGeolocation($site,$tld)
    {
        $url = $this->baseUrl."sites/".$this->urlencoding($site)."/";
        $xml = "<atom:entry xmlns:atom=\"http://www.w3.org/2005/Atom\"xmlns:wt=\"http://schemas.google.com/webmasters/tools/2007\">
                <atom:id>".$site."</atom:id>
                <atom:category scheme='http://schemas.google.com/g/2005#kind'term='http://schemas.google.com/webmasters/tools/2007#site-info'/>
                <wt:geolocation>".$tld."</wt:geolocation>
                </atom:entry>";
        return $this->requestHttpPutXml($url, $xml);
    }

    public function setCrawlRate($site,$crawl_rate)
    {
        $url = $this->baseUrl."sites/".$this->urlencoding($site)."/";
        echo $url."<br>";
        $xml = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'><atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#site-info'/><wt:crawl-rate xmlns:wt='http://schemas.google.com/webmasters/tools/2007'>faster</wt:crawl-rate></atom:entry>";
        return $this->requestHttpPutXml($url, $xml, true);
    }


    public function setPreferredDomain($site)
    {
        $xml = "<atom:entry xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:wt=\"http://schemas.google.com/webmasters/tools/2007\">
                <atom:id>http://www.example.com/news/sitemap-index.xml</atom:id>
                <atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#site-info'/>
                <wt:preferred-domain>preferwww</wt:preferred-domain>
                </atom:entry>";
    }


    public function verifySite($site, $metaTag = false)
    {
        $url = 'https://www.google.com/webmasters/tools/feeds/sites/' .$this->urlencoding($site)."";
        $type = ($metaTag) ? 'metatag' : 'htmlpage';
        $doc = $this->createXmlRequest($site, array('verification-method' => array('type' => $type, 'in-use' => 'true')));
        $xml = $doc->saveXML();

        $result = $this->_http('put', $url, $xml);

        using('Framework.System.XML');
        $result = XmlParser::parseString($result);
        $result = $result->nodes('verification-method', 'http://schemas.google.com/webmasters/tools/2007');

        $return = array();
        foreach ($result as $method) {
            $return[$method->attribute('type')] = array(
                'value' => $method->value(),
                'in-use' => $method->attribute('in-use'),
                'file-content' => $method->attribute('file-content')
            );
        }
        return $return;
    }
}