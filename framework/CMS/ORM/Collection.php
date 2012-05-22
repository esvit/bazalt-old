<?php

class CMS_ORM_Collection extends ORM_Collection
{
    public function getPage($curPage = null, $countPerPage = 10)
    {
        if ($curPage === null && isset($_GET['page'])) {
            $curPage = (int)$_GET['page'];
        }
        if ($curPage === null && class_exists('Site_Service_Paging')) {
            $curPage = Site_Service_Paging::getPage();
        }
        if (isset($_COOKIE['pager_countPerPage']) && $_COOKIE['pager_countPerPage'] >= 1 && $_COOKIE['pager_countPerPage'] <= 50) {
            $countPerPage = (int)$_COOKIE['pager_countPerPage'];
        }
        if (empty($curPage) || !is_numeric($curPage)) {
            $curPage = 1;
        }
        try {
            $data = $this->page($curPage)
                         ->countPerPage($countPerPage)
                         ->fetchPage();
        } catch (ORM_Exception_Collection $ex) {
            throw new CMS_Exception_PageNotFound();
        }
        return $data;
    }

    public function getUrl($routeName, $page = 1, $params = array())
    {
        $params['page'] = $page;
        return CMS_Mapper::urlFor($routeName, $params);
    }

    public function getPager($routeName, $params = array(), $quantity = 9)
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
                'enable' => ($page > 1)
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