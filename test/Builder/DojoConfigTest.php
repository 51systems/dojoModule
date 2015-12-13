<?php

namespace DojoTest\Builder;

use Dojo\Builder\DojoConfig;

class DojoConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testPackages()
    {
        $c = new DojoConfig();

        $packageName = 'test';
        $packagePath = 'libs/package/path';

        $this->assertFalse($c->isPackageRegistered($packageName));

        $this->assertEquals($c,
            $c->registerPackage($packageName, $packagePath));

        $this->assertTrue($c->isPackageRegistered($packageName));

        $p = $c->getPackage($packageName);

        $this->assertEquals($packageName, $p['name']);
        $this->assertEquals($packagePath, $p['path']);
    }

    public function testSet()
    {
        $c = new DojoConfig();
        $c->setParseOnLoad(true);
        $this->assertTrue($c->getParseOnLoad());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Method "setParseOnLoad" requires at least one argument
     */
    public function testSetInvalidArgument()
    {
        $c = new DojoConfig();
        $c->setParseOnLoad();
    }

    public function testGetInvalidIndex()
    {
        $c = new DojoConfig();
        $this->assertNull($c->getParseOnLoad());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Invalid Method foo
     */
    public function testInvalidMethod()
    {
        $c = new DojoConfig();
        $this->assertNull($c->foo());
    }

    public function testHas()
    {
        $c = new DojoConfig();

        $this->assertNull($c->has('foo'));

        $this->assertEquals($c, $c->has('foo', true));

        $this->assertTrue($c->has('foo'));
    }
}