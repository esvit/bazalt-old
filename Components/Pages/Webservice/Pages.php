<?php

namespace Components\Pages\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Framework\Core\Helper\Url;
use Components\Pages\Model\Page;
use Components\Pages\Model\Category;

/**
 * @uri /pages
 */
class Pages extends CMS\Webservice\Rest
{
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

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function logout()
    {
        CMS\User::logout();
        return new Response(200, true);
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function get()
    {
        if (isset($_GET['id'])) {
            $page = Page::getById($_GET['id']);
            return new Response(200, $page);
        }
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $category = null;
        if (isset($_GET['category_id'])) {
            $category = Category::getById($_GET['category_id']);
        }
        $collection = Page::getCollection(null, $category);

        if (!empty($_GET['sorting'])) {
            $collection->orderBy($_GET['sorting'] . ' ' . ($_GET['sortingDirection'] == 'true' ? 'ASC' : 'DESC'));
        }
        if (isset($_GET['filter']) && ($filter = json_decode($_GET['filter'], true))) {
            $collection->andWhereGroup();
            foreach ($filter as $field => $value) {
                if (in_array($field, ['title'])) {
                    $collection->andWhere($field . ' LIKE ?', '%' . $value . '%');
                } else {
                    $collection->andWhere($field . ' = ?', $value);
                }
            }
            $collection->endWhereGroup();
        }
        return new Response(200, $collection);
    }

    /**
     * @method GET
     * @priority 10
     * @param  int $news_id
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function getPage($page_id)
    {
        $user = CMS\User::get();
        $newsArticle = Page::getById($page_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        return new Response(200, $newsArticle);
    }
}
