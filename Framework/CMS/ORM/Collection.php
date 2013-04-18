<?php
/**
 * Collection.php
 *
 * @category   CMS
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Framework\CMS\ORM;

use Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Framework\Core\Helper\Url;

/**
 * CMS_ORM_Collection
 *
 * @category   CMS
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Collection extends \ORM_Collection
{
    /**
     * Встановлює $this->currentPage, $this->countPerPage і робить вибірку данних для поточного запиту
     *
     * @param int $page         Поточна сторінка
     * @param int $countPerPage К-ть записів на сторінку
     *
     * @throws CMS_Exception_PageNotFound
     * @return array Результат вибірки
     */
    public function getPage($page = null, $countPerPage = 10)
    {
        $page = ($page === null && isset($_GET['page'])) ? (int)$_GET['page'] : $page;
        $page = ($page === null) ? CMS\Application::getPage() : $page;
        $page = (empty($page) || !is_numeric($page)) ? 1 : $page;

        $countPerPage = (isset($_GET['count']) && $_GET['count'] >= 1 && $_GET['count'] <= 500) ? (int)$_GET['count'] : 10;

        try {
            $data = $this->page($page)
                         ->countPerPage($countPerPage)
                         ->fetchPage();
        } catch (ORM_Exception_Collection $ex) {
            throw new CMS_Exception_PageNotFound();
        }
        return $data;
    }

    public function getUrl($routeName, $page = 1, $params = array())
    {
        $url = Route::urlFor($routeName, $params);
        $url = new Url($url);
        $url = $url->path($url->path() . 'page' . $page . '/');
        return $url->toString();
    }

    public function getPager($routeName, $params = array(), $quantity = 5)
    {
        $pager = array(
            'current' => $this->page(),
            'count'   => $this->getPagesCount(),
            'total'   => $this->count(),
            'countPerPage'   => $this->countPerPage()
        );

        $page = $pager['current'];
        $countPage = $pager['count'];

        $pagerMiddle = ceil($quantity / 2);
        // first is the first page listed by this pager piece (re quantity)
        $pagerFirst = $page - $pagerMiddle + 1;
        // last is the last page listed by this pager piece (re quantity)
        $pagerLast = $page + $quantity - $pagerMiddle;

        $i = $pagerFirst;
        if ($pagerLast > $countPage) {
            // Adjust "center" if at end of query.
            $i = $i + ($countPage - $pagerLast);
            $pagerLast = $countPage;
        }
        if ($i <= 0) {
            // Adjust "center" if at start of query.
            $pagerLast = $pagerLast + (1 - $i);
            $i = 1;
        }

        $items = array();
        if ($countPage > 1) {
            $items []= array(
                'role' => 'pager-prev',
                'url'  => $this->getUrl($routeName, $page - 1, $params),
                'enable' => ($page > 1),
                'page' => $page - 1
            );
            if ($page > $pagerMiddle && $quantity < $countPage) {
                $items []= array(
                    'role' => 'pager-item',
                    'url'  => $this->getUrl($routeName, 1, $params),
                    'page' => 1
                );
            }

            // When there is more than one page, create the pager list.
            if ($i != $countPage) {
                if ($i > 2) {
                    $items []= array(
                        'role' => 'pager-ellipsis'
                    );
                }

                // Now generate the actual pager piece.
                for (; $i <= $pagerLast && $i <= $countPage; $i++) {
                    if ($i < $page) {
                        $items[] = array(
                            'role' => 'pager-item',
                            'url'  => $this->getUrl($routeName, $i, $params),
                            'page' => $i
                        );
                    }
                    if ($i == $page) {
                        $items[] = array(
                            'role' => 'pager-current',
                            'url'  => $this->getUrl($routeName, $i, $params),
                            'page' => $i
                        );
                    }
                    if ($i > $page) {
                        $items[] = array(
                            'role' => 'pager-item',
                            'url'  => $this->getUrl($routeName, $i, $params),
                            'page' => $i
                        );
                    }
                }

                if ($i < $countPage) {
                    $items[] = array(
                        'role' => 'pager-ellipsis'
                    );
                }
            }

            if ($page - 2 < ($countPage - $pagerMiddle) && $quantity < $countPage) {
                $items[] = array(
                    'role' => 'pager-item',
                    'url'  => $this->getUrl($routeName, $countPage, $params),
                    'page' => $countPage
                );
            }
            
            $items []= array(
                'role' => 'pager-next',
                'url'  => $this->getUrl($routeName, $page + 1, $params),
                'enable' => ($page < $countPage),
                'page' => $page + 1
            );
        }
        $pager['pages'] = $items;
        return $pager;
    }
}