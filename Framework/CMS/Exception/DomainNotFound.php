<?php

namespace Framework\CMS\Exception;

class DomainNotFound extends \Framework\Core\Exception\Base
{
    protected $domain = null;

    public function getDomain()
    {
        return $this->domain;
    }

    public function __construct($domain, $innerEx = null, $code = 0)
    {
        $this->domain = $domain;

        parent::__construct('Invalid domain name "' . $domain . '"', $innerEx, $code);
    }
}