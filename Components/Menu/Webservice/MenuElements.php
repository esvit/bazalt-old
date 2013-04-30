<?php

namespace Components\Menu\Webservice;

use Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS,
    Framework\CMS\Webservice\Response;
use Components\Menu\Model\Element;

/**
 * @uri /menu/:item_id
 */
class MenuElements extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getElements($item_id)
    {
        $user = CMS\User::get();
        $element = Element::getById((int)$item_id);
        if (!$element) {
            throw new \Exception('Menu not found');
        }
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        return new Response(200, $element);
    }

    /**
     * Create menu element in menu
     *
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createOrMoveMenuElement($item_id)
    {
        $data = (array)$this->request->data;
        $data['item_id'] = $item_id;
        $data['insert'] = isset($_GET['insert']) ? $_GET['insert'] : false;
        $data['move'] = isset($_GET['move']) ? $_GET['move'] : false;
        $data['before'] = isset($_GET['before']) ? $_GET['before'] : false;
        $data = new Data\Validator($data);

        $element = null;
        $prevElement = null;
        $isInserting = $data->getData('insert') == 'true';
        $isMoving = $data->getData('move') == 'true';

        $data->field('item_id')->required()->validator('exist_element', function($value) use (&$element) {
            $element = Element::getById((int)$value);
            
            return ($element != null);
        }, "Menu element dosn't exists");

        if ($isMoving) {
            $data->field('before')->required()->validator('exist_parent', function($value) use (&$element, &$prevElement) {
                $prevElement = Element::getById((int)$value);

                return ($prevElement != null) && ($prevElement->site_id == $element->site_id) && ($prevElement->root_id && $element->root_id);
            }, "Menu element dosn't exists");
        }

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        if ($isMoving) {
            if ($isInserting) {
                if (!$prevElement->Elements->moveIn($element)) {
                    throw new \Exception('Error when procesing menu operation: 1');
                }
            } else {
                if (!$prevElement->Elements->moveAfter($element)) {
                    throw new \Exception('Error when procesing menu operation: 2');
                }
            }
            $newElement = $element;
        } else {
            $newElement = Element::create($element->root_id);
            $newElement->title = __('New item', \Components\Menu\Component::getName());

            // insert as first element
            if ($isInserting) {
                if (!$element->Elements->insert($newElement)) {
                    throw new \Exception('Insert failed: 2');
                }
            } else {
                if (!$element->Elements->insertAfter($newElement)) {
                    throw new \Exception('Insert failed: 3');
                }
            }
        }

        //$newElement->Childrens = array();
        //$this->view->assign('menu_components', ComMenu_Model_Menu::getMenuTypes());
        //$this->view->assign('menuitem', $element);
        //$settings = $this->view->fetch('admin/element.menuitem');

        return new Response(200, $newElement);
    }
}
