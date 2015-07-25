<?php

namespace Rentalhost\VanillaPermission;

use PHPUnit_Framework_TestCase;

class PermissionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test basic methods.
     * @covers Rentalhost\VanillaPermission\Permission::__construct
     * @covers Rentalhost\VanillaPermission\Permission::add
     * @covers Rentalhost\VanillaPermission\Permission::get
     * @covers Rentalhost\VanillaPermission\Permission::getAll
     * @covers Rentalhost\VanillaPermission\Permission::getAllNames
     * @covers Rentalhost\VanillaPermission\Permission::has
     * @covers Rentalhost\VanillaPermission\Permission::hasAll
     * @covers Rentalhost\VanillaPermission\Permission::hasOne
     */
    public function testBasic()
    {
        $permission = new Permission;
        $permission->add(new PermissionRule("users"));
        $permission->add(new PermissionRule("users.list"));
        $permission->add(new PermissionRule("users.create"));
        $permission->add(new PermissionRule("users.remove"));
        $permission->add(new PermissionRule("users.remove.administrator"));

        $checkRules = [ "users", "users.list", "users.create", "users.remove", "users.remove.administrator" ];

        $this->assertInstanceOf(PermissionRule::class, $permission->get("users.list"));
        $this->assertEquals($checkRules, $permission->getAllNames());

        foreach ($checkRules as $checkRule) {
            $this->assertTrue(in_array(new PermissionRule($checkRule), $permission->getAll()));
            $this->assertTrue($permission->has($checkRule));
        }

        $this->assertTrue($permission->hasAll($checkRules));
        $this->assertTrue($permission->hasAll([ "users", "users.list" ]));
        $this->assertTrue($permission->hasAll([ "users.list", "users" ]));
        $this->assertTrue($permission->hasAll([ "users.list" ]));
        $this->assertTrue($permission->hasAll([]));

        $this->assertTrue($permission->hasOne($checkRules));
        $this->assertTrue($permission->hasOne([ "users", "users.list" ]));
        $this->assertTrue($permission->hasOne([ "users.list", "users" ]));
        $this->assertTrue($permission->hasOne([ "users.list" ]));

        $this->assertFalse($permission->hasAll([ "users", "users.list", "users.update" ]));
        $this->assertFalse($permission->hasAll([ "users", "users.update" ]));
        $this->assertFalse($permission->hasAll([ "users.update" ]));

        $this->assertFalse($permission->hasOne([ "users.update" ]));
        $this->assertFalse($permission->hasOne([ "users.update", "users.truncate" ]));
        $this->assertFalse($permission->hasOne([]));
    }

    /**
     * Test getOnly method.
     * @covers Rentalhost\VanillaPermission\Permission::getOnly
     * @covers Rentalhost\VanillaPermission\Permission::filterPreAllowedRules
     * @covers Rentalhost\VanillaPermission\Permission::sortLevel
     */
    public function testGetOnly()
    {
        $permission = new Permission;
        $permission->add(new PermissionRule("users"));
        $permission->add(new PermissionRule("users.list"));
        $permission->add(new PermissionRule("users.create"));
        $permission->add(new PermissionRule("users.remove"));
        $permission->add(new PermissionRule("users.remove.administrator"));

        // Simple cases.
        $this->assertEquals([], $permission->getOnly([])->getAllNames());
        $this->assertEquals([ "users" ], $permission->getOnly([ "users" ])->getAllNames());
        $this->assertEquals([ "users", "users.list" ], $permission->getOnly([ "users", "users.list" ])->getAllNames());
        $this->assertEquals([ "users", "users.remove" ], $permission->getOnly([ "users", "users.remove" ])->getAllNames());
        $this->assertEquals([ "users", "users.remove" ], $permission->getOnly([ "users.remove", "users" ])->getAllNames());

        $this->assertCount(3, $permission->getOnly([ "users", "users.remove", "users.remove.administrator" ])->getAll());
        $this->assertCount(4, $permission->getOnly([ "users", "users.create", "users.remove", "users.remove.administrator" ])->getAll());

        // Not matched parents cases.
        $this->assertEquals([], $permission->getOnly([ "users.remove" ])->getAllNames());
        $this->assertEquals([], $permission->getOnly([ "users.remove", "users.remove.administrator" ])->getAllNames());

        // Simulate sub-user permission.
        $userPermissions = $permission->getOnly([ "users", "users.list", "users.create", "users.remove" ]);
        $this->assertCount(4, $userPermissions->getAll());

        // Note: this user not have "users.remove.administrator" permission.
        // So it will not be allowed on sub-user permissions.
        $subUserPermissions = $userPermissions->getOnly([ "users", "users.list", "users.create", "users.remove", "users.remove.administrator" ]);
        $this->assertCount(4, $subUserPermissions->getAll());
    }
}
