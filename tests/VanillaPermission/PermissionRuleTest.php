<?php

namespace Rentalhost\VanillaPermission;

use PHPUnit_Framework_TestCase;

/**
 * Class PermissionRuleTest
 * @package Rentalhost\VanillaPermission
 */
class PermissionRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test basic methods.
     * @covers Rentalhost\VanillaPermission\PermissionRule::__construct
     * @covers Rentalhost\VanillaPermission\PermissionRule::getLevel
     * @covers Rentalhost\VanillaPermission\PermissionRule::getData
     */
    public function testBasic()
    {
        $permissionRule = new PermissionRule('name', 'title', 'description', [ 'data1' => 'value1' ]);

        static::assertSame('name', $permissionRule->name);
        static::assertSame('title', $permissionRule->title);
        static::assertSame('description', $permissionRule->description);
        static::assertSame(0, $permissionRule->getLevel());

        static::assertSame(1, (new PermissionRule('a.b'))->getLevel());
        static::assertSame(2, (new PermissionRule('a.b.c'))->getLevel());
        static::assertSame(9, (new PermissionRule('a.b.c.d.e.f.g.h.i.j'))->getLevel());

        static::assertSame([ 'data1' => 'value1' ], $permissionRule->getData());
        static::assertSame('value1', $permissionRule->getData('data1'));
        static::assertSame('value2', $permissionRule->getData('data2', 'value2'));
    }

    /**
     * Test public properties.
     * @coversNothing
     */
    public function testPublicProperties()
    {
        static::assertClassHasAttribute('name', PermissionRule::class);
        static::assertClassHasAttribute('title', PermissionRule::class);
        static::assertClassHasAttribute('description', PermissionRule::class);
        static::assertClassHasAttribute('data', PermissionRule::class);
    }
}
