<?php
/**
 * Post.php
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * Html_DataSource_Post
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Html_DataSource_Post extends Html_DataSource_Abstract
{
    protected $isPostBack = false;

    public function __construct(Html_ContainerElement $container, $values = array())
    {
        $this->container = $container;

        $post = $_POST;
        $this->isPostBack = (strToLower($_SERVER['REQUEST_METHOD']) == 'post') && isset($post[$this->container->name()]);

        $post = $this->addFiles($post);

        $post = isset($post[$this->container->name()]) ? $post[$this->container->name()] : array();

        $this->values = (count($values) == 0) ? $post : array_merge($post, $values);
    }

    public function isPostBack()
    {
        return $this->isPostBack;
    }

    protected function implodeFileInfo($values, $elements, $paramName)
    {
        foreach ($elements as $name => $element) {
            if (is_array($element)) {
                if (!isset($values[$name])) {
                    $values[$name] = array();
                }
                $values[$name] = $this->implodeFileInfo($values[$name], $element, $paramName);
            } else {
                $values[$name][$paramName] = $element;
            }
        }
        return $values;
    }

    protected function addFiles($values)
    {
        foreach ($_FILES as $key => $files) {
            foreach ($files as $name => $elements) {
                if (!isset($values[$key])) {
                    $values[$key] = array();
                }
                $values[$key] = $this->implodeFileInfo($values[$key], $elements, $name);
            }
        }
        return $values;
    }
}