<?php

/**
 *  SphinxXMLFeed - efficiently generate XML for Sphinx's xmlpipe2 data adapter
 *  Class based on Jetpack class from  article
 * @link http://jetpackweb.com/blog/2009/08/16/sphinx-xmlpipe2-in-php-part-ii/
 */
class Sphinx_XmlFeed extends XMLWriter
{
    const XML_PREFIX = 'sphinx';

    const XML_NAMESPACE = 'http://sphinxsearch.com/docs/1.10/xmlpipe2.html';

    private $fields = array();

    private $attributes = array();

    protected $kill_list = array();

    public function __construct($options = array())
    {
        $defaults = array(
            'indent' => false,
        );
        $options = array_merge($defaults, $options);

        
        // Store the xml tree in memory
        $this->openMemory();

        if ($options['indent']) {
            $this->setIndent(true);
        }
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function addKillList($kill_list)
    {
        $this->kill_list = $kill_list;
    }

    public function addDocument($id, $data)
    {
        $this->startElementNS(self::XML_PREFIX, 'document', null);
        $this->writeAttribute('id', $id);

        array_map(array($this, 'writeElement'), array_keys($data), $data);

        $this->endElement();
    }

    public function writeElement($name, $content)
    {
        $this->startElement($name);
        $this->writeCData($content);
        $this->endElement();
    }

    public function beginOutput()
    {
        $this->startDocument('1.0', 'UTF-8');
        $this->startElementNS(self::XML_PREFIX, 'docset', self::XML_NAMESPACE);
        $this->startElementNS(self::XML_PREFIX, 'schema', null);


        // add fields to the schema
        foreach ($this->fields as $field)
        {
            $this->startElementNS(self::XML_PREFIX, 'field', null);
            $this->writeAttribute('name', $field);
            $this->endElement();
        }


        // add attributes to the schema
        foreach ($this->attributes as $attributes)
        {
            $this->startElementNS(self::XML_PREFIX, 'attr', null);
            foreach ($attributes as $key => $value)
            {
                $this->writeAttribute($key, $value);
            }
            $this->endElement();
        }


        // end sphinx:schema
        $this->endElement();
        print $this->outputMemory();
    }

    public function endOutput()
    {
        // add kill list
        if (!empty($this->kill_list)) {
            $this->startElementNS(self::XML_PREFIX, 'killlist', null);

            foreach ($this->kill_list as $id) {
                $this->writeElement('id', $id);
            }
            $this->endElement();
        }


        // end sphinx:docset
        $this->endElement();
        print $this->outputMemory();
    }

}