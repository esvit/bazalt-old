<?php



    class ManagingSitemaps extends GoogleWebmasterTool {

        function __construct($auth) {
            $this->auth = $auth;
            $this->service = "sitemaps";
        }

        public function submitRegularSitemap($site, $sitemap_url, $type="WEB") {
            $url = $this->baseUrl.$this->urlencoding($site)."/sitemaps/";
            $xml = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'><atom:id>".$sitemap_url."</atom:id><atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#sitemap-regular'/><wt:sitemap-type xmlns:wt='http://schemas.google.com/webmasters/tools/2007'>".$type."</wt:sitemap-type></atom:entry>";
            return $this->requestHttpPutXml($url, $xml);
        }
        
        public function submitSitemapMobile($site, $sitemap_url, $markup_language="HTML") {
            $url = $this->baseUrl.$this->urlencoding($site)."/sitemaps/";
            $xml = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'><atom:id>".$sitemap_url."</atom:id><atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#sitemap-mobile'/> <wt:sitemap-mobile-markup-language xmlns:wt='http://schemas.google.com/webmasters/tools/2007'>".$markup_language."</wt:sitemap-mobile-markup-language></atom:entry>";
            return $this->requestHttpPutXml($url, $xml);
        }

        public function submitSitemapNews($site, $sitemap_url, $publication_label="") {
            $url = $this->baseUrl.$this->urlencoding($site)."/sitemaps/";
            $xml = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'><atom:id>".$sitemap_url."</atom:id><atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#sitemap-news'/>";
            if ($publication_label!="") $xml .= "<wt:sitemap-news-publication-label xmlns:wt='http://schemas.google.com/webmasters/tools/2007'>".$publication_label."</wt:sitemap-news-publication-label>";
            $xml.="</atom:entry>";
            return $this->requestHttpPutXml($url, $xml);
        }

        public function deleteSitemap($site, $sitemap_url) {
            $url = $this->baseUrl.$this->urlencoding($site)."/sitemaps/".$this->urlencoding($sitemap_url);
             return $this->requestHttpDelete($url);
        }


    }

    class CrawlIssues  extends GoogleWebmasterTool {

        function __construct($auth) {
            $this->auth = $auth;
            $this->service = "crawlissues";
        }
    }
