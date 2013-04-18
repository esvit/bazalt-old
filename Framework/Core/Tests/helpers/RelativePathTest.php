<?php

class RelativePathTest extends Tests\BaseCase
{
    public function testPath()
    {
        $fileName = '/test/path';
        $basePath = '/test';

        $this->assertEquals(relativePath($fileName, $basePath), '/path');
    }
}