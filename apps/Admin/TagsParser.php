<?php

class Admin_TagsParser
{
    protected static $instance = null;

    // declarations
    var $_instance;
    var $_vars;

    protected static $plugins = array();

    protected static $tags = array();

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            self::$plugins['bz'] = dirname(__FILE__) . '/tags';
            self::loadPlugins();
        }
        return self::$instance;
    }

    public static function prepare($string)
    {
        return self::getInstance()->fetchTemplate($string);
    }

    public function fetchTemplate($string)
    {
        /** vars **/
        $this->_vars						= array();
        $this->_vars["buffer"] 				= "";
        $this->_vars["log"]					= array();
        //$this->_vars["log"]["file"]			= $file;
        $this->_vars["vars"]				= array();
        $this->_vars["functionbuffer"]		= array();
        $this->_vars["namespace"]			= "bz";

        // functions

        $this->_vars["functionbuffer"]["loop"]		= array();
        $this->_vars["functionbuffer"]["loopbuffer"]		= array();
        $this->_vars["functionbuffer"]["onemptyloop"]		= array();
        $this->_vars["functionbuffer"]["onemptyloopbuffer"]		= array();
        $this->_vars["functionbuffer"]["show"]		= array();
        $this->_vars["functionbuffer"]["showbuffer"]		= array();
        $this->_vars["functionbuffer"]["section"]		= array();
        $this->_vars["functionbuffer"]["sectionbuffer"]		= array();


        /** read template **/
        $this->_vars["buffer"] = $string;

        $this->_vars["buffer"] = $this->searchtags($this->_vars["buffer"]);

        return $this;
    }

    /**
     * Output the parsed template
     *
     * @return Template buffer
     */
    function output()
    {
        // apply loops
        foreach ($this->_vars["functionbuffer"]["loop"] as $loopName=>$loopOutput) {
            if ($loopOutput == "") {
                $loopOutput = $this->_vars["functionbuffer"]["onemptyloopbuffer"][$loopName];
            }
            $this->_vars["buffer"] = str_replace("<loop_$loopName/>",$loopOutput,$this->_vars["buffer"]);
        }
        //$this->parseAndApply("after");
        return $this->_vars["buffer"];
    }

    /**
     * Search and parse all FTL tags
     *
     * @return pre-parsed template
     */
    protected function searchTags($in)
    {
        // match all tags in the form <type:name args>content</type:name> and singleton <type:name args />
        $reg = "#<([a-z]+):(.*?)(\s+(.*?)\s*)?(>(.*?)</\\1:\\2>|/>)#mis";    // global regex to match all tags. Including FBML.
        //$reg = "#<".$this->_vars["namespace"].":(.*?)\s+(.*?)\s*(>(.*?)</".$this->_vars["namespace"].":\\1>|/>)#mis";
        //$reg = '#<([a-z]+):(.*?)\s+(.*?)\s*(>(.*?)</([a-z]+):\\1>|/>)#mis';

        if (preg_match($reg, $in, $match)) {
            $in = preg_replace_callback($reg, array($this, 'tagparser'), $in, -1);
            return $this->searchTags($in);
        }
        return $in;
    }

    /**
    * Parse a single tag
    *
    * @return the parsed tag
    *
    */
    function tagparser($array)
    {
        $tagNamespace = $array[1];
        $tagName      = $array[2];
        $tagArgs      = $array[4];
        $tagContent   = isset($array[6]) ? $array[6] : '';
        $argsArray    = array();

        $tagName = str_replace('-', '_', $tagName);

        $tagArgs = str_replace("\\\"","[[QUOTE]]",$tagArgs);
        preg_match_all("#([a-z1-9]+)\s*=(([a-z0-9_-]+)|[\"?](.[^\"]+)[\"?])#mis",$tagArgs,$args);
        foreach ($args[1] as $id => $var) {
            $argsArray[$var] = $this->correctValue($args[2][$id]);
        }

        // parse nested tags
        $tagContent = $this->searchtags($tagContent);


        switch (strtolower($this->superTrim($tagName))) {
            case "loop":
                // a loop...
                // put the content in the function buffer
                $this->_vars["functionbuffer"]["loop"][$argsArray["name"]] = "";                 // output of the loop is empty
                $this->_vars["functionbuffer"]["loopbuffer"][$argsArray["name"]] = $tagContent;  // loop buffer
                // replace by a localization tag
                return "<loop_".$argsArray["name"]."/>";
            break;
            case "onemptyloop":
                // emptyloop...
                // put the content in the function buffer
                $this->_vars["functionbuffer"]["onemptyloop"][$argsArray["name"]] = "";                 // output of the onemptyloop is empty
                $this->_vars["functionbuffer"]["onemptyloopbuffer"][$argsArray["name"]] = $tagContent;  // onemptyloop buffer
                // strip the tag
                return "";
            break;
            case "section":
                // a section tag...
                // put the content in the function buffer
                $this->_vars["functionbuffer"]["section"][$argsArray["name"]] = "";                 // output of the section is empty
                $this->_vars["functionbuffer"]["sectionbuffer"][$argsArray["name"]] = $tagContent;  // section buffer
                // replace by a localization tag
                return "<section_".$argsArray["name"]."/>";
            break;
            case "show":
                // a show tag...
                // put the content in the function buffer
                $this->_vars["functionbuffer"]["show"][$argsArray["name"]] = "";                 // output of the section is empty
                $this->_vars["functionbuffer"]["showbuffer"][$argsArray["name"]] = $tagContent;  // section buffer
                // replace by a localization tag
                return "<show_".$argsArray["name"]."/>";
            break;
            default:
                // Unknown type. Probably a plugin. Else, strip the tag.
                $funcName = $tagNamespace . '_' . $tagName;
                if (function_exists($funcName)) {
                    return $funcName($tagContent, $argsArray);
                } else {
                    return $tagContent;
                }
            break;
        }
    }

    /**
    * Register a template variable
    *
    * @param String the variable name without the variable identifier (for variable "%var%", just "var")
    *        String The value of the variable
    * @return nothing
    *
    */
    function addVar($label, $value) {
        $this->_vars["vars"][$label] = $value;
        $this->_vars["buffer"] = str_replace("%".$label."%",$value,$this->_vars["buffer"]);
    }

    /**
    * Register more than one template variable at a time, in an array
    *
    * @param Array an array of variables type array("var1"=>"value1","var2"=>"value2")
    * @return nothing
    */
    function addVars($array) {
        foreach ($array as $varLabel=>$varValue) {
            $this->addVar($varLabel, $varValue);
        }
    }

    /**
    * Loop a code section
    *
    * @param String the loop name as defined in the template
    * 		 Array an array of variables type array("var1"=>"value1","var2"=>"value2")
    * @return Template buffer
    *
    */
    function loop($name, $array) {
        global $_PARSER;
        $buffer = $this->_vars["functionbuffer"]["loopbuffer"][$name];
        foreach ($array as $label=>$value) {
            $buffer = str_replace("%".$label."%",$value,$buffer);
        }
        $this->_vars["functionbuffer"]["loop"][$name] .= $buffer;
    }

    /**
    * Function used to handle the <fn:section> tag. Any call to this function will replace the current buffer with the content of the <fn:section> tag.
    *
    * @param String the name of the section to load, defined on the tag as name="[name]"
    * @return true
    */
    function useSection($sectionName) {
        global $_PARSER;
        $tmpBuffer 	= $this->_vars["functionbuffer"]["sectionbuffer"][$sectionName];
        $this->_vars["buffer"] = $tmpBuffer;
        $this->parseAndApply();
        return true;
    }


    /**
    * Function used to handle the <fn:show> tag. It will result as the display of the content of the tag.
    * If a tag is not called, it will be deleted before any output.
    *
    * @param String the name of the <fn:show> tag to load, defined on the tag as name="[name]"
    * @return true
    */
    function show($name) {
        global $_PARSER;
        $tmpBuffer 	= $this->_vars["functionbuffer"]["showbuffer"][$name];
        $this->_vars["buffer"] = str_replace("<show_$name/>",$tmpBuffer,$this->_vars["buffer"]);
        $this->parseAndApply();
        return true;
    }


    /**
    * Apply the loops, flatten the template. Used to use nested loops.
    *
    * @return null
    */
    function applyLoops() {
        foreach ($this->_vars["functionbuffer"]["loop"] as $loopName=>$loopOutput) {
            $this->_vars["buffer"] = str_replace("<loop_$loopName/>",$loopOutput,$this->_vars["buffer"]);
        }
    }

    public static function loadPlugins()
    {
        foreach (self::$plugins as $namespace => $path) {
            foreach (glob($path . '/' . $namespace . '/*.php') as $file) {
                include_once $file;
                $function = substr(basename($file), 0, -4);
                self::$tags[$namespace] []= $function;
            }
        }
    }

    function correctValue($value)
    {
        $firstChar = substr($value,0,1);
        $lastChar = substr($value,strlen($value)-1,1);
        if ($firstChar == "\"" && $lastChar == "\"") {
            // remove quotes
            $value = substr($value,1,strlen($value)-2);
        }
        $value = str_replace("[[QUOTE]]","\"",$value);
        $value = str_replace("\\>",">",$value);
        return $value;
    }

    function freadFile($file) {
        if (!$handle = fopen ($file, "r")) {
            exit;
        }
        $contents = fread ($handle, filesize($file)+1);
        fclose($handle);
        return $contents;
    }

    function STRING_get_file_ext($filename) {
        if (strrpos($filename, '.') >= 1) {
            return strtolower(substr($filename, strrpos($filename, '.') + 1));
        } else {
            return "";
        }
    }

    function debug($label, $value) {

        if (is_array($value)) {
            echo "<b>$label</b> :: <div style=\"border:1px dashed #000000;margin-left:20px;\">".nl2br(str_replace(" ","&nbsp;",str_replace("<","&lt;",print_r($value,true))))."</div><br>";
        } else {
            echo "<b>$label</b> :: <div style=\"border:1px dashed #000000;margin-left:20px;\">".nl2br(str_replace(" ","&nbsp;",str_replace("\t","    ",$value)))."</div><br>";
        }

    }

    function superTrim($in) {
        /** remove extra spaces, line breaks, tabs **/
        $in = str_replace("\t"," ",$in);
        $in = str_replace("\r"," ",$in);
        $in = preg_replace('/\s\s+/', ' ', trim($in));
        return $in;
    }

    function encodeAsArg($in) {
        $in = str_replace("\"","\\\"",$in);
        $in = str_replace("}","\\}",$in);
        return $in;
    }
}