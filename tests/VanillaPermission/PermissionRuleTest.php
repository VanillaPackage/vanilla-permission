<?php

namespace Rentalhost\VanillaPermission;

use PHPUnit_Framework_TestCase;

class PermissionRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test basic methods.
     * @covers Rentalhost\VanillaPermission\PermissionRule::__construct
     */
    public function testBasic()
    {
        $permissionRule = new PermissionRule("name", "title", "description");

        $this->assertSame("name", $permissionRule->name);
        $this->assertSame("title", $permissionRule->title);
        $this->assertSame("description", $permissionRule->description);
    }

    /**
     * Test public properties.
     */
    public function testPublicProperties()
    {
        $this->assertClassHasAttribute("name", PermissionRule::class);
        $this->assertClassHasAttribute("title", PermissionRule::class);
        $this->assertClassHasAttribute("description", PermissionRule::class);
    }
}
