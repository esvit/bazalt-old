<?php

namespace Components\Pages\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Framework\Core\Helper\Url;
use Components\Pages\Model\Page,
    Components\Pages\Model\Category,
    Components\Pages\Model\Image;

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


        $page->category_id = $data['category_id'];
        $page->is_published = $data['is_published'] == true;
        $page->save();

        $languages = CMS\Language::getLanguages();
        $data->field('title')->validator('hasDefaultTranslate', function($value) use (&$page, $languages, $data) {
            //$user = CMS\Model\User::getUserByEmail($value);
            foreach ($languages as $language) {
                \Framework\CMS\ORM\Localizable::setLanguage($language);
                $page->title = $value->{$language->id};
                $page->body = $data['body']->{$language->id};
                if (!$page->url) {
                    $page->url = Url::cleanUrl(\Framework\System\Locale\Config::findLocaleByAlias($language->id)->translit($page->title));
                }
                $page->save();
            }

            return true;
        }, 'User with this email does not exists');
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $image = Image::getById($image->id);
                if ($image) {
                    $page->Images->add($image);
                }
            }
        }

        return new Response(200, $page);
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deletePage()
    {
        if (isset($_GET['ids'])) {
            Page::deleteByIds($_GET['ids']);
        }
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
            $category = Category::getById((int)$_GET['category_id']);
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
