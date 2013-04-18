<?php

use Framework\System\Routing\Route;

class Route_Test extends Tests\BaseCase
{
    protected function setUp()
    {
        unset($_SERVER['PATH_INFO']);
    }

    protected function tearDown()
    {
        Route::clear();
    }

    public function testName()
    {
        Route::root()->connect('Test.Test', '/test/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidName()
    {
        Route::root()->connect('Test.', '/test/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDublicateName()
    {
        Route::root()->connect('test', '/test/');
        Route::root()->connect('test', '/test/');
    }

    public function testCompile()
    {
        $this->assertEquals('#^/test/$#', Route::compileRule('/test/'));

        $this->assertEquals('#^/(?P<test>[^/]+)/$#', Route::compileRule('/{test}/'));

        $this->assertEquals('#^/{test/$#', Route::compileRule('/{test/'));

        $this->assertEquals('#^/{test/test}/$#', Route::compileRule('/{test/test}/'));

        $this->assertEquals('#^/(?P<test>\d+)/$#', Route::compileRule('/{test:\d+}/'));

        $this->assertEquals('#^/(?P<test>.+)?/$#', Route::compileRule('/[test]/'));
    }

    public function testRoot()
    {
        $root = Route::root();

        $this->assertEquals('home', $root->name());

        $route = Route::root()->connect('test', '/test/', ['init' => 1], function() {

        });

        $this->assertEquals('test', $route->name());

        $this->assertTrue($route->compareTo('/test/'));
        $this->assertTrue($route->compareTo('/test'));

        $this->assertFalse($route->compareTo('/test2'));
        $this->assertFalse($route->compareTo('/test2/'));

        $route = Route::root()->connect('test2', '/{test}/');
        $route->compareTo('/test/', $params);

        $res = ['test' => 'test'];
        $this->assertEquals($res, array_intersect($res, $params));

        $route = Route::root()->connect('test3', '/test/[test]/test3/');
        $this->assertTrue($route->compareTo('/test/test2/test3/test3/'));
        $this->assertTrue($route->compareTo('/test/test2/test3/test3', $params));

        $res = ['test' => 'test2/test3'];
        $this->assertEquals($res, array_intersect($res, $params));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRule()
    {
        Route::root()->connect('test2', '//');
    }

    public function testSubmappers()
    {
        $articlesRoute = Route::root()->connect('Article', '/article/');
        $articlesRoute->connect('Article.View', '/{id:\d+}');
        $urlRoute = $articlesRoute->connect('Article.Url', '/{url}', ['url' => 'article', 'byUrl' => true]);

        $pagesRoute = Route::root()->connect('Pages', '/page/');
        $pagesRoute->connect('Page.View', '/{id:\d+}');

        $route = Route::find('/article/12');
        $this->assertEquals('Article.View', $route->name());
        $this->assertTrue($route->params()['id'] == 12);

        $route = Route::find('/article/test');
        $this->assertEquals('Article.Url', $route->name());
        $this->assertTrue($route->params()['url'] == 'test');
        $this->assertTrue($route->params()['byUrl'] == true);

        $this->assertTrue($urlRoute->params()['url'] == 'article');

        $route = Route::find('/test/123');
        $this->assertNull($route);
    }

    public function testConditions()
    {
        $articlesRoute = Route::root()->connect('Article', '/article/');
        $urlRoute = $articlesRoute->connect('Article.Url', '/{url}');
        $urlRoute->where('url', function($url, $name, $value, $params) {
            return $params['url'] == 'test';
        });
        $route = Route::find('/article/test');
        $this->assertEquals('Article.Url', $route->name());

        $route = Route::find('/article/awdawd');
        $this->assertNull($route);

        $articlesRoute = Route::root()->connect('Page', '/page/');
        $urlRoute = $articlesRoute->connect('Page.Url', '/{url}');
        $urlRoute->where('url', '\d+');

        $route = Route::find('/page/123');
        $this->assertEquals('Page.Url', $route->name());

        $route = Route::find('/page/awdawd');
        $this->assertNull($route);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWhere()
    {
        Route::root()->connect('test', '/test/')->where('test', array());
    }

    public function testPatternFor()
    {
        Route::root()->connect('Test', '/{test}/key/');

        $this->assertEquals('/{test}/key/', Route::patternFor('Test'));
    }

    public function testUrlFor()
    {
        Route::root()->connect('Test', '/{test}/key/');

        $this->assertEquals('/test/key/', Route::urlFor('Test', ['test' => 'test']));
    }
}