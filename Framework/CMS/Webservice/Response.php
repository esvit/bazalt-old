<?php

namespace Framework\CMS\Webservice;

use Framework\CMS as CMS;

class Response extends \Tonic\Response
{
    public function __construct($code = null, $body = null, $headers = array())
    {
        $data = $body;
        if ($body instanceof CMS\ORM\Collection) {
            $data = $body->getPage();
        } else if ($data instanceof CMS\ORM\Record) {
            $data = $data->toArray();
        }
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                if ($item instanceof CMS\ORM\Record) {
                    $data[$key] = $item->toArray();
                }
            }
        }
        if ($body instanceof CMS\ORM\Collection) {
            $data = [
                'data' => $data,
                'pager' => [
                    'current' => $body->page(),
                    'count'   => $body->getPagesCount(),
                    'total'   => $body->count(),
                    'countPerPage'   => $body->countPerPage()
                ]
            ];
        }
        parent::__construct($code, $data, $headers);
    }
}