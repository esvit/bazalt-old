<?php

class Html_jQuery_TinyMCE extends Html_jQuery_Textarea
{
    private static $defaultSettings = array(
        "url"        => "/assets/packages/tiny_mce/tiny_mce_gzip.php",
        "plugins"    => "",
        "themes"     => "",
        "languages"  => "",
        "disk_cache" => true,
        "expires"    => "30d",
        "cache_dir"  => "",
        "compress"   => true,
        "suffix"     => "",
        "files"      => "",
        "source"     => false,
    );

    public $toolbar1 = 'bold,italic,strikethrough,underline,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,anchor,|,spellchecker,fullscreen';
    public $toolbar2 = 'formatselect,forecolor,|,pastetext,pasteword,cleanup,|,image,charmap,|,outdent,indent,|,undo,redo,|,pagebreak,typograf,code';
    public $toolbar3 = '';

    public function javascriptValue()
    {
        return '$("#' . $this->id() . '").tinymce().getContent()';
    }

    public function toString()
    {
        $this->rows(20);
        $this->style('width: 100%;');

        Scripts::addModule('TinyMCE');
        
        $tag = self::renderTag(array(
            "url" => "/assets/packages/tiny_mce/tiny_mce_gzip.php",
            "plugins" => "spellchecker,typograf,safari,pagebreak,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template,images",
            "themes" => "advanced",
            "languages" => "en"
        ));

        $js = 'jQuery("#' . $this->id() . '").tinymce({';

        $js .= 'theme : "advanced",' . "\n";
        $js .= 'plugins : "spellchecker,typograf,safari,pagebreak,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template,images",' . "\n";

        $js .= 'theme_advanced_buttons1 : "' . $this->toolbar1 . '",';
        $js .= 'theme_advanced_buttons2 : "' . $this->toolbar2 . '",' . "\n";
        $js .= 'theme_advanced_buttons3 : "' . $this->toolbar3 . '", //"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,advhr,|,print,|,ltr,rtl,|",' . "\n";
        
        $js .= 'theme_advanced_toolbar_location : "top",' . "\n";
        $js .= 'theme_advanced_toolbar_align : "left",' . "\n";
        $js .= 'theme_advanced_statusbar_location : "bottom",' . "\n";
        $js .= 'theme_advanced_resizing : true,' . "\n";

        //$js .= 'content_css : "' . Mapper::urlFor('ComThemes.CSS') . '",' . "\n";

        //$js .= 'template_external_list_url : "lists/template_list.js",' . "\n";
        //$js .= 'external_link_list_url : "lists/link_list.js",' . "\n";
        //$js .= 'external_image_list_url : "lists/image_list.js",' . "\n";
        //$js .= 'media_external_list_url : "lists/media_list.js",' . "\n";

        $js .= 'relative_urls : false,' . "\n";

        $js .= 'remove_script_host : true,' . "\n";
        $js .= 'inlinepopups_skin : "bootstrap",' . "\n";

        $js .= 'spellchecker_languages : "English=en,+Ukrainian=uk,Russian=ru",' . "\n";
        $js .= 'spellchecker_rpc_url : "/assets/packages/tiny_mce/plugins/spellchecker/post-handler.php",' . "\n";
        $js .= 'spellchecker_word_separator_chars : \'\\s!"#$%&()*+,./:;<=>?@[\]^_|\xa7{ }\xa9\xab\xae\xb1\xb6\xb7\xb8\xbb\xbc\xbd\xbe\u00bf\xd7\xf7\xa4\u201d\u201c\',' . "\n";

        $js .= 'file_browser_callback : "elFinderBrowser",';
        $js .= 'theme_advanced_resize_horizontal:"",' . "\n";
        $js .= 'theme_advanced_blockformats: \'p,pre,h2,h3,h4,h5,h6\',' . "\n";

        $js .= 'skin: "default"' . "\n";

        $js .= '});';
        Html_Form::addOnReady($js);

        $this->resizable = false;
        return $tag . parent::toString();
    }

    public static function renderTag($tagSettings)
    {
        $settings = array_merge(self::$defaultSettings, $tagSettings);

        if (empty($settings["cache_dir"]))
            $settings["cache_dir"] = dirname(__FILE__);

        $scriptSrc = $settings["url"] . "?js=1";

        // Add plugins
        if (isset($settings["plugins"]))
            $scriptSrc .= "&plugins=" . (is_array($settings["plugins"]) ? implode(',', $settings["plugins"]) : $settings["plugins"]);

        // Add themes
        if (isset($settings["themes"]))
            $scriptSrc .= "&themes=" . (is_array($settings["themes"]) ? implode(',', $settings["themes"]) : $settings["themes"]);

        // Add languages
        if (isset($settings["languages"]))
            $scriptSrc .= "&languages=" . (is_array($settings["languages"]) ? implode(',', $settings["languages"]) : $settings["languages"]);

        // Add disk_cache
        if (isset($settings["disk_cache"]))
            $scriptSrc .= "&diskcache=" . ($settings["disk_cache"] === true ? "true" : "false");

        // Add any explicitly specified files if the default settings have been overriden by the tag ones
        /*
         * Specifying tag files will override (rather than merge with) any site-specific ones set in the 
         * TinyMCE_Compressor object creation.  Note that since the parameter parser limits content to alphanumeric
         * only base filenames can be specified.  The file extension is assumed to be ".js" and the directory is
         * the TinyMCE root directory.  A typical use of this is to include a script which initiates the TinyMCE object. 
         */
        if (isset($tagSettings["files"]))
            $scriptSrc .= "&files=" .(is_array($settings["files"]) ? implode(',', $settings["files"]) : $settings["files"]);

        // Add src flag
        if (isset($settings["source"]))
            $scriptSrc .= "&src=" . ($settings["source"] === true ? "true" : "false");

        $scriptTag = '<script type="text/javascript" src="' . htmlspecialchars($scriptSrc) . '"></script>';

        return $scriptTag;
    }
}