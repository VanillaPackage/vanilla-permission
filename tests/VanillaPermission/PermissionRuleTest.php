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
     */
    public function testBasic()
    {
        $permissionRule = new PermissionRule('name', 'title', 'description');

        static::assertSame('name', $permissionRule->name);
        static::assertSame('title', $permissionRule->title);
        static::assertSame('description', $permissionRule->description);
    }

    /**
     * Test public properties.
     */
    public function testPublicProperties()
    {
        static::assertClassHasAttribute('name', PermissionRule::class);
        static::assertClassHasAttribute('title', PermissionRule::class);
        static::assertClassHasAttribute('description', PermissionRule::class);
    }
}
