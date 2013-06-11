<?php

namespace Components\Shop\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\Shop\Model\ProductImage;
use Framework\Core\Helper\Url;
use Components\Shop\Model\Product,
    Components\Shop\Model\Category,
    Components\Shop\Model\Image;

/**
 * @uri /shop
 */
class Products extends CMS\Webservice\Rest
{
    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function saveProduct()
    {
        $data = new Data\Validator((array)$this->request->data);
        $product = isset($data['id']) ? Product::getById((int)$data['id']) : Product::create();

        $languages = CMS\Language::getLanguages();

        $product->category_id = $data['category_id'];
        $product->price = $data['price'];
        $product->code = $data['code'];
        $product->is_published = $data['is_published'] == true;
        $product->save();

        $data->field('title')->validator('hasDefaultTranslate', function($value) use (&$product, $languages, $data) {
            //$user = CMS\Model\User::getUserByEmail($value);
            foreach ($languages as $language) {
                \Framework\CMS\ORM\Localizable::setLanguage($language);
                $product->title = $value->{$language->id};
                $product->description = $data['description']->{$language->id};
                if (!$product->url) {
                    $product->url = Url::cleanUrl(\Framework\System\Locale\Config::findLocaleByAlias($language->id)->translit($product->title));
                }
                $product->save();
            }

            return true;
        }, 'User with this email does not exists');
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $image = ProductImage::getById($image->id);
                if ($image) {
                    $product->Images->add($image);
                }
            }
        }

        return new Response(200, $product);
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function deleteProduct()
    {
        if (isset($_GET['ids'])) {
            Product::deleteByIds($_GET['ids']);
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
            $page = Product::getById($_GET['id']);
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
        $collection = Product::getCollection($category);

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
    public function getProduct($page_id)
    {
        $user = CMS\User::get();
        $newsArticle = Product::getById($page_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        return new Response(200, $newsArticle);
    }
}
