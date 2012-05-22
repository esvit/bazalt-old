<?php

define('GOOGLE_CLOSURE_COMPILER', dirname(__FILE__) . '/compiler.jar');

class GoogleClosure
{
    var $_srcs = array();
    var $_mode = "WHITESPACE_ONLY";
    var $_warning_level = "DEFAULT";
    var $_use_closure_library = false;
    var $_pretty_print = false;
    var $_debug = true;
    var $_cache_dir = "";
    var $_code_url_prefix = "";

    /**
     * Adds a source file to the list of files to compile.  Files will be
     * concatenated in the order they are added.
     */
    function add($file)
    {
        $this->_srcs[] = $file;
        return $this;
    }

  /**
   * Sets the directory where the compilation results should be cached, if
   * not set then caching will be disabled and the compiler will be invoked
   * for every request (NOTE: this will hit ratelimits pretty fast!)
   */
    function cacheDir($dir) {
        $this->_cache_dir = $dir;
        return $this;
    }

  /**
   * Sets whether to use the Closure Library.  i.e. goog.requires will be
   * resolved and the library code made available.
   */
    function useClosureLibrary() {
        $this->_use_closure_library = true;
        return $this;
    }

  /**
   * Sets the URL prefix to use with the Closure Compiler service's code_url
   * parameter.
   *
   * By default PHP-Closure posts the scripts to the compiler service, however,
   * this is subject to a 200000-byte size limit for the whole post request.
   *
   * Using code_url tells the compiler service the URLs of the scripts to
   * fetch.  The file paths added in add() must therefore be relative to this
   * URL.
   *
   * Example usage:
   *
   * $c->add("js/my-app.js")
   *   ->add("js/popup.js")
   *   ->useCodeUrl('http://www.example.com/app/')
   *   ->cacheDir("/tmp/js-cache/")
   *   ->write();
   *
   * This assumes your PHP script is in a directory /app/ and that the JS is in
   * /app/js/ and accessible via HTTP.
   */
    function useCodeUrl($code_url_prefix) {
        $this->_code_url_prefix = $code_url_prefix;
        return $this;
    }

    /**
     * Tells the compiler to pretty print the output.
     */
    function prettyPrint()
    {
        $this->_pretty_print = true;
        return $this;
    }

    /**
     * Turns of the debug info.
     * By default statistics, errors and warnings are logged to the console.
     */
    function hideDebugInfo()
    {
        $this->_debug = false;
        return $this;
    }

    /**
     * Sets the compilation mode to optimize whitespace only.
     */
    function whitespaceOnly()
    {
        $this->_mode = "WHITESPACE_ONLY";
        return $this;
    }

    /**
     * Sets the compilation mode to simple optimizations.
     */
    function simpleMode()
    {
        $this->_mode = "SIMPLE_OPTIMIZATIONS";
        return $this;
    }

    /**
     * Sets the compilation mode to advanced optimizations (recommended).
     */
    function advancedMode()
    {
        $this->_mode = "ADVANCED_OPTIMIZATIONS";
        return $this;
    }

    /**
    * Gets the compilation mode from the URL, set the mode param to
    * 'w', 's' or 'a'.
    */
    function getModeFromUrl()
    {
        if ($_GET['mode'] == 's') $this->simpleMode();
        else if ($_GET['mode'] == 'a') $this->advancedMode();
        else $this->whitespaceOnly();
        return $this;
    }

    /**
     * Sets the warning level to QUIET.
     */
    function quiet()
    {
        $this->_warning_level = "QUIET";
        return $this;
    }

    /**
     * Sets the default warning level.
     */
    function defaultWarnings()
    {
        $this->_warning_level = "DEFAULT";
        return $this;
    }

  /**
   * Sets the warning level to VERBOSE.
   */
  function verbose() {
    $this->_warning_level = "VERBOSE";
    return $this;
  }

    /**
     * Writes the compiled response.  Reading from either the cache, or
     * invoking a recompile, if necessary.
     */
    function write($fileName)
    {
        file_put_contents($fileName, $this->_compile());
    }

    function _compile()
    {
        // Quieten strict notices.
        $code = $originalSize = $originalGzipSize = $compressedSize = $compressedGzipSize = $compileTime = '';

        $tree = $this->_parseXml($this->_makeRequest());

        $result = $tree[0]["value"];
        foreach ($result as $node) {
          switch ($node["tag"]) {
            case "compiledCode": $code = $node["value"]; break;
            case "warnings": $warnings = $node["value"]; break;
            case "errors": $errors = $node["value"]; break;
            case "statistics":
              foreach ($node["value"] as $stat) {
                switch ($stat["tag"]) {
                  case "originalSize": $originalSize = $stat["value"]; break;
                  case "originalGzipSize": $originalGzipSize = $stat["value"]; break;
                  case "compressedSize": $compressedSize = $stat["value"]; break;
                  case "compressedGzipSize": $compressedGzipSize = $stat["value"]; break;
                  case "compileTime": $compileTime = $stat["value"]; break;
                }
              }
              break;
          }
        }

        $result = "";
        if ($this->_debug) {
          $result = "if(window.console && window.console.info){\r\n" .
                    "window.console.info('Closure Compiler Stats:\\n" .
                    "-----------------------\\n" .
                    "Original Size: $originalSize\\n" .
                    "Original Gzip Size: $originalGzipSize\\n" .
                    "Compressed Size: $compressedSize\\n" .
                    "Compressed Gzip Size: $compressedGzipSize\\n" .
                    "Compile Time: $compileTime\\n" .
                    "Generated: " . Date("Y/m/d H:i:s T") . "');\r\n";
          if (isset($errors)) $result .= $this->_printWarnings($errors, "error");
          if (isset($warnings)) $result .= $this->_printWarnings($warnings, "warn");
          $result .= "}\r\n\r\n";
        }
        $result .= "$code \r\n";

        return $result;
    }
    
  function _printWarnings($warnings, $level="log") {
    $result = "";
    foreach ($warnings as $warning) {
      $desc = addslashes($warning["value"]);
      $type = $warning["attributes"]["type"];
      $lineno = $warning["attributes"]["lineno"];
      $charno = $warning["attributes"]["charno"];
      $line = addslashes($warning["attributes"]["line"]);
      $result .= "window.console.$level('$type: $desc\\nLine: $lineno\\nChar: $charno\\nLine: $line');\r\n";
    }
    return $result;
  }

  function _getCacheFileName() {
    return $this->_cache_dir . $this->_getHash() . ".js";
  }

  function _getHash() {
    return md5(implode(",", $this->_srcs) . "-" .
        $this->_mode . "-" .
        $this->_warning_level . "-" .
        $this->_use_closure_library . "-" .
        $this->_pretty_print . "-" .
        $this->_debug);
  }

  function _getParams() {
    $params = array();
    foreach ($this->_getParamList() as $key => $value) {
      $params[] = preg_replace("/_[0-9]$/", "", $key) . "=" . urlencode($value);
    }
    return implode("&", $params);
  }

  function _getParamList() {
    $params = array();
    if ($this->_code_url_prefix) {
      // Send the URL to each source file instead of the raw source.
      $i = 0;
      foreach($this->_srcs as $file){
        $params["code_url_$i"] = $this->_code_url_prefix . $file;
        $i++;
      }
    } else {
      $params["js_code"] = $this->_readSources();
    }
    $params["compilation_level"] = $this->_mode;
    $params["output_format"] = "xml";
    $params["warning_level"] = $this->_warning_level;
    if ($this->_pretty_print) $params["formatting"] = "pretty_print";
    if ($this->_use_closure_library) $params["use_closure_library"] = "true";
    $params["output_info_1"] = "compiled_code";
    $params["output_info_2"] = "statistics";
    $params["output_info_3"] = "warnings";
    $params["output_info_4"] = "errors";
    return $params;
  }

  function _readSources() {
    $code = "";
    foreach ($this->_srcs as $src) {
      $code .= file_get_contents($src);
    }
    return $code;
  }

  function _makeRequest() {
    $data = $this->_getParams();
    $referer = @$_SERVER["HTTP_REFERER"] or "";

    $fp = fsockopen("closure-compiler.appspot.com", 80) or die("Unable to open socket");;

    if ($fp) {
      fputs($fp, "POST /compile HTTP/1.1\r\n");
      fputs($fp, "Host: closure-compiler.appspot.com\r\n");
      fputs($fp, "Referer: $referer\r\n");
      fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
      fputs($fp, "Content-length: ". strlen($data) ."\r\n");
      fputs($fp, "Connection: close\r\n\r\n");
      fputs($fp, $data); 

      $result = ""; 
      while (!feof($fp)) {
        $result .= fgets($fp, 128);
      }

      fclose($fp);
    }

    $data = substr($result, (strpos($result, "\r\n\r\n")+4));
    if (strpos(strtolower($result), "transfer-encoding: chunked") !== FALSE) {
      $data = $this->_unchunk($data);
    }

    return $data;
  }

    function _unchunk($data)
    {
        $fp = 0;
        $outData = "";
        while ($fp < strlen($data)) {
          $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
          $num = hexdec(trim($rawnum));
          $fp += strlen($rawnum);
          $chunk = substr($data, $fp, $num);
          $outData .= $chunk;
          $fp += strlen($chunk);
        }
        return $outData;
    }

    function _parseXml($data)
    {
        // &lt; trips up the XML parser.  Hack to fix it here.
        $data = str_replace("&lt;", "---LTLTLTLT---", $data);
        $xml = new XMLReader();
        $xml->xml($data);
        return $this->_parseXmlHelper($xml);
    }

    function _parseXmlHelper($xml)
    {
        $tree = null; 
        while($xml->read()) {
            switch ($xml->nodeType) { 
                case XMLReader::END_ELEMENT: return $tree; 
                case XMLReader::ELEMENT: 
                    $node = array('tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : $this->_parseXmlHelper($xml)); 
                    if($xml->hasAttributes) {
                        while($xml->moveToNextAttribute()) {
                            $node['attributes'][$xml->name] = str_replace("---LTLTLTLT---", "<", $xml->value);
                        }
                    }
                    $tree[] = $node; 
                    break; 
            case XMLReader::TEXT:
            case XMLReader::CDATA: 
                $tree .=  str_replace("---LTLTLTLT---", "<", $xml->value);
                break;
            default:
                exit("\r\nUnknown node type\r\n" .
                        $xml->nodeType . "\r\n" .
                        $xml->name . "\r\n" .
                        $xml->value);
            }
        }
        return $tree; 
    }

    public static function compile($file, $output)
    {
        $errFile = $output . '.error';
        $java = (OS == OS_WIN) ? 'java' : '/usr/local/bin/java';
        $command = $java . ' -jar ' . GOOGLE_CLOSURE_COMPILER . 
                   ' --js ' . $file . 
                   ' --js_output_file ' . $output .
                   ' 2> ' . $errFile;
        
        $ret = exec($command);

        $errors = null;
        if (filesize($output) == 0) {
            $errors = file_get_contents($errFile);
            $errors = iconv('866', 'UTF-8//TRANSLIT', $errors);
            $errors = str_replace(realpath($file), basename($file), $errors);
            $errors = str_replace("\r\n", "\n", $errors);
            $errors = str_replace("\n\r", "\n", $errors);
            $errors = explode("\n\n", $errors);
        }
        unlink($errFile);
        return $errors;
    }
}