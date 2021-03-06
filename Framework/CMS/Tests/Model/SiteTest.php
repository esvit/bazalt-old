<?php

namespace Framework\CMS\tests\Model;

use Framework\CMS\Model\Site;

class SiteTest extends \Tests\BaseCase
{
    /**
     * @var Site
     */
    protected $site = null;

    protected function setUp()
    {
        $this->site = Site::create();
        $this->site->save();
    }

    protected function tearDown()
    {
        if ($this->site)
            $this->site->delete();
    }

    public function testGetUrl()
    {
        $this->assertEquals('http://localhost/', $this->site->getUrl());

        $this->site->domain = 'test.ua';
        $this->assertEquals('http://test.ua/', $this->site->getUrl());
    }

    public function testGetSiteByDomain()
    {
        $this->site->domain = 'test.ua';
        $this->site->save();

        $item = Site::getSiteByDomain('test.ua');
        $this->assertNull($item);

        $this->site->is_active = 1;
        $this->site->save();

        $item = Site::getSiteByDomain('test.ua');
        $this->assertNotNull($item);
        $this->assertEquals($this->site->domain, $item->domain);
    }
}