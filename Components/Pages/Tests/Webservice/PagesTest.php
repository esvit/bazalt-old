<?php

use Components\Pages as Pages;

class PagesTest extends tests\BaseCase
{
    protected $client;

    public function setUp()
    {
        using('Framework.Vendors.Pest');

        $this->client = new \Pest('http://localhost:8000');
    }

    public function testGetDefaultLocale()
    {
        $this->client->setupAuth('admin', '1');

        $thing = $this->client->get('/rest.php/pages');
    }
}