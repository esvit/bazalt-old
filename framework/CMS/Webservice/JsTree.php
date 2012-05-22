<?php

abstract class CMS_Webservice_JsTree extends CMS_Webservice
{
    protected $model = null;

    protected $args = null;

    protected $component = null;

    protected $view = null;

    protected function getModelName()
    {
        throw new Exception('You must return model name in function getModelName()');
    }

    public function __construct($component)
    {
        parent::__construct();

        $this->component = $component;
        $this->view = $component->View;

        $modelName = $this->getModelName();

        $this->model = new $modelName();
    }

    protected function objectToNode($item)
    {
        $node->attr = new stdClass();
        $node->attr->id = 'node_' . $item->id;
        if ($item->Elements->getChildrenCount() == 0) {
            $node->attr->rel = 'default';
        } else {
            $node->attr->rel = 'folder';
        }
        
        $node->data = array();
        $langs = CMS_Language::getLanguages();
        foreach($langs as $lang) {
            $itemTr = $item->getTranslation($lang);

            $nodeData = new stdClass();
            $nodeData->language = $lang->alias;
            $nodeData->title = (empty($itemTr->title)) ? '(Untitled)' : $itemTr->title;
            // $nodeData->icon = 'published';
            $node->data[] = $nodeData;
        }

        if ($node->attr->rel == 'folder') {
            $node->state = 'closed';
        }
        return $node;
    }

    public function removeNode($id)
    {
        $item = $this->item($id);

        $item->Elements->getParent()
             ->Elements->remove($item);
        return array('status' => 1);
    }

    public function renameNode($id, $title, $lang)
    {
        $lang = CMS_Language::getLanguage($lang);
        if (!$lang) {
            throw new Exception('Language not found');
        }
        $item = $this->item($id);

        CMS_ORM_Localizable::setLanguage($lang);
        $item->title = $title;
        $item->alias = Url::cleanUrl($title);
        $item->save();

        return array('status' => 1);
    }

    public function _moveNode($itemId, $prevId = null, $isInsert = false, $isCopy = false)
    {
        try {
            $isInsert = ($isInsert == 'true');
            $isCopy = ($isCopy == 'true');
            $elem = $this->item($itemId);
            if (!$elem) {
                throw new Exception('Menu element not found');
            }

            // insert as first element
            if ($prevId == null) {
                $prevElem = $elem->Elements->getRoot();
                $isInsert = true; // insert in root
            } else {
                $prevElem = $this->item($prevId);
            }

            if (!$prevElem) {
                throw new Exception('Prev menu element not found');
            }

            if ($isCopy) {
                $prevElem->Elements->add($elem);
            } else if ($isInsert) {
                if (!$prevElem->Elements->moveIn($elem, $isCopy)) {
                    //print_r(ORMRelationNestedSet::getLastErrors());exit;
                    throw new Exception('Error when procesing menu operation');
                }
            } else {
                if (!$prevElem->Elements->moveAfter($elem, $isCopy)) {
                    //print_r(ORM_Relation_NestedSet::getLastErrors());exit;
                    throw new Exception('Error when procesing menu operation');
                }
            }
        } catch (Exception $e) {
            return array('status' => 0, 'message' => $e->getMessage());
        }
        return array('status' => 1);
    }

    /*public function moveNode($args)
    {
        $item = $this->item();
        $item->Childrens = $item->Elements->get();

        $isCopy = $args['copy'] != '0';

        if (is_numeric($args['ref'])) {
            $parent = $this->model->getById(intval($args['ref']));
        } else {
            $parent = $item->Elements->getRoot();
        }

        $tree = $item->Elements->get();
        //print_r($tree);
        if ($isCopy) {
            $item->id = null;
        } else {
            //$item->Root->Categories->Elements->remove($item);
        }
        $parent->Elements->insert($item, $args['position']);

        return array('status' => 1);
    }*/

    public function createNode($parentId, $categoryId, $title, $type)
    {
        $this->model->category_id = (int)$categoryId;
        $item = $this->item($parentId);
        if (!$item) {
            throw new Exception('Item not found');
        }

        $modelName = $this->getModelName();
        $newItem = new $modelName;
        $newItem->title = $title;
        $newItem->category_id = $item->category_id;

        if (!$item->Elements->insert($newItem)) {
            throw new Exception('Error while create node');
        }

        $langs = CMS_Language::getLanguages();
        foreach ($langs as $lang) {
            CMS_ORM_Localizable::setLanguage($lang);
            $newItem->title = $title;
            $newItem->alias = Url::cleanUrl($title);
            $newItem->save();
        }
        return array('status' => 1, 'id' => $newItem->id);
    }

    public function getItem($id)
    {
        $item = $this->item($id);
        $childs = $item->Elements->get(1);

        $nodes = array();
        foreach ($childs as $child) {
            $nodes[] = $this->objectToNode($child);
        }
        return $nodes;
    }

    protected function item($id)
    {
        if ($id == '-1') {
            return $this->model->Elements->getRoot();
        }
        if (!is_numeric($id)) {
            throw new Exception('Invalid argument');
        }

        $item = $this->model->getById(intval($id));
        if (!$item) {
            throw new Exception('Item not found');
        }
        return $item;
    }

    /**
     * Return url of webservice
     */
    public function __getServiceScriptName()
    {
        return CMS_Mapper::urlFor(
            CMS_Webservice::COMPONENT_ROUTE_NAME, 
            array(
                'cms_component' => get_class($this->component),
                'service' => get_class($this)
            )
        );
    }
}
