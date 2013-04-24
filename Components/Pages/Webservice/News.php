<?php

namespace Components\News\Webservice;

use Framework\CMS\Webservice\Response,
    Framework\System\Session\Session,
    Framework\System\Data as Data,
    Framework\CMS as CMS;
use Components\News\Model\Article;
use Components\News\Model\Category;

/**
 * @uri /news
 * @uri /news/:news_id
 */
class News extends CMS\Webservice\Rest
{
    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function registration()
    {
        $data = new Data\Validator((array)$this->request->data);
        $data->field('email')->required()->email()->validator('exist_user', function($value) {
            $user = CMS\Model\User::getUserByEmail($value);

            return ($user == null);
        }, 'User with this email already exists');
        $password = $data->field('password')->required()->validator('password_len', function($value) {
            return strlen($value) >= 8;
        });
        $data->field('confirm')->required()->equal($password);
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user = CMS\Model\User::create();
        $user->login = $data['email'];
        $user->email = $data['email'];
        $user->password = $data['password'];

        \Components\Users\Component::registerNewUser($user);
        return new Response(200, $user->toArray());
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return \Tonic\Response
     */
    public function login()
    {
        $user = null;
        $data = new Data\Validator((array)$this->request->data);
        $data->field('nickname')->required()->validator('exist_user', function($value) use (&$user) {
            $user = CMS\Model\User::getUserByEmail($value);

            return ($user != null);
        }, 'User with this email does not exists');
        $data->field('password')->required()->validator('password', function($value) use (&$user) {
            return ($user->password == CMS\User::criptPassword($value));
        }, 'User with this email does not exists');
        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $user->login();
        return new Response(200, $user->toArray());
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
        /*$user = CMS\User::get();
        if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $category = null;
        if (isset($_GET['category_id'])) {
            $category = Category::getById($_GET['category_id']);
        }
        $collection = Article::getCollection(null, $category);

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
    public function getArticle($news_id)
    {
        $user = CMS\User::get();
        $newsArticle = Article::getById($news_id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        return new Response(200, $newsArticle);
    }
}
