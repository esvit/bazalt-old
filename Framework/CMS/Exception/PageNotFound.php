<?php

namespace Framework\CMS\Exception;

class PageNotFound extends \Framework\Core\Exception\Base
{
    protected $page = null;

    public function getPage()
    {
        return $this->page;
    }

    public function __construct($page = null, $innerEx = null, $code = 0)
    {
        $this->page = $page;

        parent::__construct('Page not found "' . $page . '"', $innerEx, $code);
    }
}