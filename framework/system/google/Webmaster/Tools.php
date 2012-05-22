<?php

class Google_Webmaster_Tools
{
    protected $auth = null;

    protected $service;

    protected $baseUrl = "https://www.google.com/webmasters/tools/feeds/";

    public function __construct(Google_Auth $auth)
    {
        $this->auth = $auth;
    }

    protected function urlencoding($site)
    {
        return str_replace(".", "%2E", urlencode($site));
    }

    protected function executeService($site, $operation)
    {
        if (strlen($site)>0) {
            $request = $this->urlencoding($site)."/".$operation."/";
        } else {
            $request = $operation."/";
        }
        $url = $this->baseUrl.$request;
        $xml = $this->getFeed($url);
        return $xml;
    }
    
    protected function getHeaders()
    {
        $headers = $this->auth->generateAuthHeader();
        $headers['GData-Version'] = '2';
        $headers['Content-type'] = 'application/atom+xml';
        return $headers;
    }
    
    protected function requestHttpPutXml($url,$xml, $put=false)
    {
        $header = $this->getHeaders();
        if ($put) {
            array_push($header, "X-HTTP-Method-Override: PUT");
        }
echo $url;
        $url = new DataType_Url($url);
        $url->setHeaders($header);

        if ($put) {
            $result = $url->put($xml);
        } else {
            $result = $url->post($xml);
        }
        $responseCode = $url->getResponseCode();
echo $result;
        if ($responseCode !=200) {
            return $responseCode;
        }
        return $result;
    }

    protected function requestHttpDelete($url)
    {
        //$url = $this->baseUrl."sites/".urlencode($site);
        $ch = curl_init();
        
        $head = $this->getHeaders();
        $head['X-HTTP-Method-Override'] = 'DELETE';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info['http_code'];
    }


    public function getFeed($url)
    {
        $head = $this->getHeaders();
        $url = new DataType_Url($url);
        $url->setHeaders($head);

        $result = $url->get();
        $responseCode = $url->getResponseCode();
        if ($responseCode !=200) {
            return $responseCode;
        }
        return $result;
     }

     public function getDataResult($site="") {
        $data = array();
        $xml = $this->executeService($site, $this->service);
        if(is_integer($xml)) {
            $this->errorCode = $xml;
            return false;
        }
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $entries = $dom->getElementsByTagName("entry");
        foreach($entries as $entry) {
           $objectEntry = new Entry();
           $nodes = $entry->childNodes;
           foreach($nodes as $node) {
               $objectEntry->addProperty($node->nodeName, $node->nodeValue);
           }
           array_push($data, $objectEntry);
        }
        return $data;
    }
    
    
    protected function createXmlRequest($site, $params = array())
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElementNS('http://www.w3.org/2005/Atom', 'atom:entry');

        if (count($params) > 0) {
            $root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:wt','http://schemas.google.com/webmasters/tools/2007');
        }
        $doc->appendChild($root);

        $element = $doc->createElement('atom:id', $site);
        $root->appendChild($element);

        if (count($params) > 0) {
            $element = $doc->createElement('atom:category');
            $element->setAttribute('scheme','http://schemas.google.com/g/2005#kind');
            $element->setAttribute('term','http://schemas.google.com/webmasters/tools/2007#site-info');
            $root->appendChild($element);
        } else {
            $element = $doc->createElement('atom:content');
            $element->setAttribute('src',$site);
            $root->appendChild($element);
        }
        foreach ($params as $tag => $value) {
            if (is_array($value)) {
                $element = $doc->createElement("wt:$tag", $value['_value']);
                foreach ($value as $att => $valueAttr) {
                    if ($att == '_value') {
                        continue;
                    }
                    $element->setAttribute($att, $valueAttr);
                }
            } else {
                $element = $doc->createElement("wt:$tag", $value);
            }
            $root->appendChild($element);
        }
        return $doc;
    }
    
        function _callWMT($method, $url, $site='', $params = array(), $array_elements_in = array()) {

      $method = strtolower($method);
      $site = "http://$site/";
      $url = str_replace('{site}', urlencode($site), $url);
      $xml = '';

      if ($method=='post' || $method=='put') {

          $doc = new DOMDocument('1.0', 'utf-8');
          $root = $doc->createElementNS("http://www.w3.org/2005/Atom", 'atom:entry' );

          if (count($params) > 0) {
              $root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:wt','http://schemas.google.com/webmasters/tools/2007');
          }


          if (count($params) > 0) {
              $element = $doc->createElement('atom:category');
              $element->setAttribute('scheme','http://schemas.google.com/g/2005#kind');
              $element->setAttribute('term','http://schemas.google.com/webmasters/tools/2007#site-info');
              $root->appendChild($element);
          } else {
              $element = $doc->createElement('atom:content');
              $element->setAttribute('src',$site);
              $root->appendChild($element);
          }

          foreach ($params as $tag => $value) {

             if (is_array($value)) {
                 $element = $doc->createElement("wt:$tag", $value['_value']);
                 foreach($value as $att => $value) {
                    if($att=='_value') continue;
                    $element->setAttribute('att','value');
                 }
             } else {
                 $element = $doc->createElement("wt:$tag", $value);
                 $root->appendChild($element);
             }
          }

          $xml = $doc->saveXML();
      }

      $body = $this->_Http($method, $url, "application/atom+xml", $xml);


      if ($body!='') {
          $doc = new DOMDocument();
          $success = $doc->loadXML($body);
          return $this->_ElementToArray($doc, $array_elements_in);
      } else {
          return false;
      }

    } 

    protected function _http($method, $url, $content = '', $contentType = 'application/atom+xml')
    {
        $method = strToUpper($method);
        $auth = $this->auth->generateAuthHeader();
        $opts = array('http' =>
            array(
                'method'  => $method,
                'protocol_version' => 1.0,
                'header'  => 'Content-type: ' . $contentType .
                             (isset($this->auth) ? "\nAuthorization: " . $auth['Authorization']  : '' ) .
                             "\nContent-Length: " . strlen($content),
                'content' => $content
            )
        );
        $context  = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);
        return $result;
    } 

    

    function _GetText($node) {
        $text = '';
        for ($i=0; $i < $node->childNodes->length; $i++) {
            $child = $node->childNodes->item($i);
            if ($child->nodeType==XML_TEXT_NODE)
                $text .= $child->wholeText;
        }
        return $text;
    }

    // array_elements_in has the set of tags we should use as array b
    // because they may repeat.
    function _ElementToArray($node, $array_elements_in = array()) {
        $row = array();

        $array_elements = array();
        foreach ($array_elements_in as $array_element)
           $array_elements[$array_element] = true;

        for ($i=0; $i < $node->childNodes->length; $i++) {
            $item = $node->childNodes->item($i);
            if (!isset($item->tagName)) continue;
            $children = $this->_ElementToArray($item, $array_elements_in);
            if (count($children) > 0) {
                $value = $children;
            } else {
                $value = $this->_GetText($item);
            }
            if (isset($array_elements[$item->tagName])) {
                if (!isset($row[$item->tagName])) $row[$item->tagName] = array();
                $row[$item->tagName][] = $value;
            } else
                $row[$item->tagName] = $value;
        }
        return $row;
    } 
}

    class Entry {

        private $map = array();

        public function addProperty($key,$value) {
            $this->map[$key] = $value;
        }
        public function getPropertyValue($key) {
            if (!isset($this->map[$key])) return false;
            return $this->map[$key];
        }

        public function getArrayResult() {
            return $this->map;
        }

    }