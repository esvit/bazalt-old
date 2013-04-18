<?php

namespace Framework\CMS\Pagination;

use Framework\CMS as CMS;

trait ApplicationTrait
{
    protected static $paginationPrefix = 'page';

    protected static $currentPage = 1;

    public static function getPage()
    {
        return self::$currentPage;
    }

    /**
     * Парсить url
     *
     * @param string $url
     */
    public static function parsePageFromUrl(&$url)
    {
        $pageCurrent = 0;

        if (preg_match('#/' . self::$paginationPrefix . '(?P<digit>\d+)#', $url, $matches)) {
            if (!empty($matches['digit'])) {
                $url = rtrim($url, '/');
                $pageCurrent = (int)$matches['digit'];
                $url = substr($url, 0, -(5 + strlen($matches['digit'])));
            }
        }
        /*if (array_key_exists('page', $_GET)) {
            $pageCurrent = (int)$_GET['page'];
        }*/

        /*if ($pageCurrent > 1) {
            if (CMS\Option::get('CMS.PagintaionNoIndex', true)) {
                Metatags::Singleton()->noIndex();
            }
            Metatags::set('PAGINATION_TITLE', ' ' . sprintf(__('%d page', 'CMS'), $pageCurrent));
        } else {
            Metatags::set('PAGINATION_TITLE', '');
        }
        Metatags::set('PAGINATION_CURRENT', $pageCurrent);*/

        CMS\View::assignGlobal('pageCurrent', $pageCurrent);

        self::$currentPage = ($pageCurrent <= 0) ? 1 : $pageCurrent;
    }
}