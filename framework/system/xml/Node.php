<?php

class XML_Node implements Countable, Iterator
{
    /**
     * Номер нода
     */
    protected $id = null;

    /**
     * Назва елемента
     */
    protected $name = null;

    /**
     * Значення елемента
     */
    protected $value = null;

    /**
     * Атрибути елемента
     */
    protected $attributes = array();

    /**
     * Дочірні елементи
     */
    protected $childrens = array();

    protected $childrensByName = array();

    protected $nsAttributes = array();

    protected $nsNodes = array();

    protected $nsNodesByName = array();

    /**
     * Батьківський елемент
     */
    protected $parentNode = null;

    protected function __construct($nodeName, $value)
    {
        $this->name = $nodeName;
        $this->value = $value;
    }

    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }

    public function count()
    {
        return count($this->childrens);
    }

    public function rewind()
    {
        return reset($this->childrens);
    }

    public function current()
    {
        return current($this->childrens);
    }

    public function key()
    {
        return key($this->childrens);
    }

    public function next()
    {
        return next($this->childrens);
    }

    public function valid()
    {
        return key($this->childrens) !== null;
    }

    public function attribute($name, $namespace = null)
    {
        if ($namespace != null) {
            if (!isset($this->nsAttributes[$namespace])) {
                return null;
            }
            return $this->nsAttributes[$namespace][$name];
        }
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return null;
    }

    public function attributes($namespace = null)
    {
        if ($namespace != null) {
            return $this->nsAttributes[$namespace];
        }
        return $this->attributes;
    }

    public function nodes($name = null, $namespace = null)
    {
        if ($namespace != null) {
            if ($name == null) {
                return $this->nsNodes[$namespace];
            }
            return $this->nsNodesByName[$namespace][$name];
        }
        if ($name == null) {
            return $this->childrens;
        }
        return $this->childrensByName[$name];
    }

    public function nodesByName()
    {
        return $this->childrensByName;
    }

    public function node($name, $namespace = null)
    {
        if ($namespace != null) {
            return $this->nsNodes[$namespace][$name][0];
        }
        if (array_key_exists($name, $this->childrensByName) && is_array($this->childrensByName[$name])) {
            return current($this->childrensByName[$name]);
        }
        return null;
    }

    public static function fromSimpleXml(SimpleXmlElement $elem, $class = null)
    {
        if ($class == null) {
            $class = __CLASS__;
        } else {
            if ($class != 'XML_Node' && !(typeOf($class)->isSubclassOf('XML_Node'))) {
                throw new XML_Exception('Class ' . $class . ' must be XML_Node or subclass of XML_Node');
            }
        }
        $node = new $class((string)$elem->getName(), (string)$elem);

        foreach ($elem->attributes() as $key => $attr) {
            $node->addAttribute((string)$key, (string)$attr);
        }
        foreach ($elem->children() as $child) {
            $node->addChild(self::fromSimpleXml($child, $class));
        }
        $node->loadMetaData($elem, $class);

        return $node;
    }

    protected function loadMetaData(SimpleXmlElement $elem, $class = __CLASS__)
    {
        $namespaces = $elem->getNamespaces(true);
        foreach ($namespaces as $key => $namespace) {
            foreach ($elem->attributes($namespace) as $attrKey => $attrValue) {
                $this->addNsAttribute((string)$attrKey, (string)$attrValue, $namespace);
            }
            foreach ($elem->children($namespace) as $child) {
                $this->addNsNode(self::fromSimpleXml($child, $class), $namespace);
            }
        }
    }

    protected function addNsAttribute($key, $value, $namespace)
    {
        $this->nsAttributes[$namespace][$key] = $value;
    }

    protected function addNsNode($node, $namespace)
    {
        $node->parentNode = &$this;
        $node->id = isset($this->nsNodes[$namespace]) ? count($this->nsNodes[$namespace]) : 0;
        $this->nsNodes[$namespace][] = &$node;
        $this->nsNodesByName[$namespace][$node->name()][$node->id] = &$node;
    }

    public function getNamespaceNodes($namespace)
    {
        if (array_key_exists($namespace, $this->nsNodes)) {
            return $this->nsNodes[$namespace];
        }
        return null;
    }

    public function getNamespaceAttributes($namespace)
    {
        if (array_key_exists($namespace, $this->nsAttributes)) {
            return $this->nsAttributes[$namespace];
        }
        return null;
    }

    protected function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    protected function addChild($node)
    {
        $class = __CLASS__;
        if (!$node instanceof $class) {
            throw new XML_Exception('Invalid type of xml node');
        }
        $node->parentNode = &$this;
        $node->id = count($this->childrens);
        $this->childrens[$node->id] = &$node;
        $this->childrensByName[$node->name()][$node->id] = &$node;
        return $this;
    }

    public function remove()
    {
        if (isset($this->parentNode->childrens[$this->id])) {
            unset($this->parentNode->childrens[$this->id]);
        }
        if (is_array($this->parentNode->childrensByName[$this->name()])) {
            foreach ($this->parentNode->childrensByName[$this->name()] as $n => $item) {
                if ($item->id == $this->id) {
                    unset($this->parentNode->childrensByName[$this->name()][$n]);
                    break;
                }
            }
        }
    }

    public function __sleep()
    {
        $this->parentNode = null;
        return array('name', 'value', 'attributes', 'childrens', 'nsAttributes', 'nsNodes');
    }

    public function __wakeup()
    {
        $num = 0;
        foreach ($this->childrens as &$node) {
            $node->parentNode = &$this;
            $node->id = $num++;
            $this->childrensByName[$node->name()][] = &$node;
        }
    }
    
    public static function __fromArray(array $data, $class = null)
    {
        if ($class == null) {
            $class = __CLASS__;
        } else {
            if ($class != 'XML_Node' && !(typeOf($class)->isSubclassOf('XML_Node'))) {
                throw new XML_Exception('Class ' . $class . ' must be XML_Node or subclass of XML_Node');
            }
        }
        if(!isset($data['name'])) {
            return null;
        }
        $o = new $class($data['name'], $data['value']);
        $o->setAttributes($data['attributes']);
        $o->setChildrens($data['childrens'], $class);
        $o->setNsAttributes($data['nsAttributes']);
        $o->setNsNodes($data['nsNodes'], $class);
        $o->__wakeup();
        return $o;
    }
    
    public function __toArray()
    {
        $class = get_class($this);
        $data = array();
        $data['name'] = $this->name;
        $data['value'] = $this->value;
        $data['attributes'] = $this->attributes;
        $data['childrens'] = array();
        foreach($this->childrens as $children) {
            if($children instanceof $class) {
                $data['childrens'][] = $children->__toArray();
            }
        }
        $data['nsAttributes'] = $this->nsAttributes;
        $data['nsNodes'] = array();
        foreach($this->nsNodes as $namespace => $nsNodes) {
            $data['nsNodes'][$namespace] = array();
            foreach($nsNodes as $nsNode) {
                if($nsNode instanceof $class) {
                    $data['nsNodes'][$namespace][] = $nsNode->__toArray();
                }
            }
        }
        return $data;
    }
    

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
    
    public function setNsAttributes($nsAttributes)
    {
        $this->nsAttributes = $nsAttributes;
    }
    
    public function setChildrens($childrens, $class)
    {
        foreach($childrens as $children) {
            $this->addChild(self::__fromArray($children, $class));
        }
    }
    
    public function setNsNodes($nsNodes, $class)
    {
        foreach($nsNodes as $namespace => $nsNodes) {
            foreach($nsNodes as $nsNode) {
                $this->addNsNode(self::__fromArray($nsNode, $class), $namespace);
            }
        }
    }
}