<?php
/**
 * Form.php
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * Html_Form
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Html_Form extends Html_Ajax_Form
{
    protected static $registredElements = array();

    protected static $formView = null;

    protected static $onReadyScripts = array();

    protected static $onFormInit = array();

    protected static $registredJavascripts = array();

    public function __construct($name = null, $attributes = array())
    {
        if (empty($name)) {
            $name = Html_Element::generateId(get_class($this));
        }
        parent::__construct($name, $attributes);
    }

    public static function addOnReady($src)
    {
        self::$onReadyScripts []= $src;
    }

    public static function addOnFormInit($src)
    {
        self::$onFormInit []= $src;
    }

    public static function getView()
    {
        if (!self::$formView) {
            $folders = array('Html' => dirname(__FILE__) . '/templates');
            self::$formView = new View_Base($folders);
        }
        return self::$formView;
    }

    public static function registerJavascript($element)
    {
        $name = $element->id();
        $className = get_class($element);

        self::$registredJavascripts[$className] = $element->generateJavascript();
        Scripts::addInline('$.fn.formInit = function(callback){
            var formName = $(this).attr("id");
            if(window[formName] == undefined) {
                $("body").on("formInit", callback);
            } else {
                window[formName].formInit = callback;
            }
        };', 'formInit');

        if ($element->form()->id() != $element->id()) {
            self::addOnReady(sprintf('%s.addElement("%s", (new %s()), "%s");', $element->form()->id(), $name, $className, $className));
        }
    }

    public function end()
    {
        Html_Form::addOnReady('$("#'.$this->form->id().'").formInit(function(form){
            ' . implode("\n", self::$onFormInit) . '
        });');

        $js = implode("\n", self::$onReadyScripts);
        if (count(self::$registredJavascripts) > 0) {
            $view = $this->view();
            $view->assign('form', $this);
            $jsStr = $view->fetch('elements/javascript/base');

            $jsStr .= implode("\n", self::$registredJavascripts);

            $js = $jsStr . $js ."\n";
            $js .= $this->id().'.init("'.$this->id().'", "'.get_class($this).'").initElement();';
        }
        if (!empty($js)) {
            $js = 'jQuery(document).ready(function(){' . "\n" . $js . "\n" . '});';
        }
        return parent::end() . 
               sprintf(Scripts::SCRIPTINLINE_HTMLTAG, $js);
    }

    public static function registerElement($name, $class)
    {
        self::$registredElements[$name] = $class;
    }

    public static function getRegistredClass($name)
    {
        if (!isset(self::$registredElements[$name])) {
            throw new Exception('Unknown element "' . $name . '"');
        }
        $class = self::$registredElements[$name];
        if (!class_exists($class)) {
            throw new Exception('Invalid class "' . $class . '" for element "' . $name . '"');
        }
        return $class;
    }
}