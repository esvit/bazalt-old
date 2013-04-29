<?php

namespace Components\Pages\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\Pages\Model\Category;

/**
 * @uri /pages/categories
 * @uri /pages/categories/:category_id
 */
class Categories extends CMS\Webservice\Rest
{
    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getElements()
    {
        $user = CMS\User::get();
        $category = Category::getSiteRootCategory();
        if (!$category) {
            throw new \Exception('Menu not found');
        }
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        return new Response(200, $category);
    }

    /**
     * Create menu element in menu
     *
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function createOrMoveMenuElement()
    {
        $data = (array)$this->request->data;
        $data['id'] = $_GET['id'];
        $data['insert'] = isset($_GET['insert']) ? $_GET['insert'] : false;
        $data['move'] = isset($_GET['move']) ? $_GET['move'] : false;
        $data['before'] = isset($_GET['before']) ? $_GET['before'] : false;
        $data = new Data\Validator($data);

        $category = Category::getSiteRootCategory();
        $prevElement = null;
        $isInserting = $data->getData('insert') == 'true';
        $isMoving = $data->getData('move') == 'true';

        $data->field('id')->validator('exist_element', function($value) use (&$category) {
            return empty($value) || ($category = Category::getById((int)$value));
        }, "Category dosn't exists");

        if ($isMoving) {
            $data->field('before')->required()->validator('exist_parent', function($value) use (&$category, &$prevElement) {
                $prevElement = Category::getById((int)$value);
                
                return ($prevElement != null) && ($prevElement->site_id == $category->site_id);
            }, "Category dosn't exists");
        }

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        if ($isMoving) {
            if ($isInserting) {
                if (!$prevElement->Elements->moveIn($category)) {
                    throw new \Exception('Error when procesing menu operation');
                }
            } else {
                if (!$prevElement->Elements->moveAfter($category)) {
                    throw new \Exception('Error when procesing menu operation');
                }
            }
            $newElement = $category;
        } else {
            $newElement = Category::create();
            $newElement->title = __('New category', \Components\Pages\Component::getName());

            // insert as first element
            if ($isInserting) {
                if (!$category->Elements->insert($newElement)) {
                    throw new \Exception('Insert failed: 2');
                }
            } else {
                if (!$category->Elements->insertAfter($newElement)) {
                    throw new \Exception('Insert failed: 3');
                }
            }
        }

        return new Response(200, $newElement->toArray());
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function savePage()
    {
        $data = new Data\Validator((array)$this->request->data);
        $page = isset($data['id']) ? Page::getById((int)$data['id']) : Page::create();

        $data->field('title')->validator('hasDefaultTranslate', function($value) {
            //$user = CMS\Model\User::getUserByEmail($value);

            return true;
        }, 'User with this email does not exists');
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $page->title = $data['title']->en;
        $page->body = $data['body']->en;
        $page->publish = $data['publish'] == 'true';
        $page->url = Url::cleanUrl(\Framework\System\Locale\Config::getLocale()->translit($page->title));
        $page->save();

        return new Response(200, $page);
    }
}
