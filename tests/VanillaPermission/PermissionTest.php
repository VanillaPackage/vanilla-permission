<?php

namespace Rentalhost\VanillaPermission;

use PHPUnit_Framework_TestCase;

/**
 * Class PermissionTest
 * @package Rentalhost\VanillaPermission
 */
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
        $permission->add(new PermissionRule('users'));
        $permission->add(new PermissionRule('users.list'));
        $permission->add(new PermissionRule('users.create'));
        $permission->add(new PermissionRule('users.remove'));
        $permission->add(new PermissionRule('users.remove.administrator'));

        $checkRules = [ 'users', 'users.list', 'users.create', 'users.remove', 'users.remove.administrator' ];

        static::assertInstanceOf(PermissionRule::class, $permission->get('users.list'));
        static::assertEquals($checkRules, $permission->getAllNames());

        foreach ($checkRules as $checkRule) {
            static::assertTrue(in_array(new PermissionRule($checkRule), $permission->getAll(), false));
            static::assertTrue($permission->has($checkRule));
        }

        static::assertTrue($permission->hasAll($checkRules));
        static::assertTrue($permission->hasAll([ 'users', 'users.list' ]));
        static::assertTrue($permission->hasAll([ 'users.list', 'users' ]));
        static::assertTrue($permission->hasAll([ 'users.list' ]));
        static::assertTrue($permission->hasAll([ ]));

        static::assertTrue($permission->hasOne($checkRules));
        static::assertTrue($permission->hasOne([ 'users', 'users.list' ]));
        static::assertTrue($permission->hasOne([ 'users.list', 'users' ]));
        static::assertTrue($permission->hasOne([ 'users.list' ]));

        static::assertFalse($permission->hasAll([ 'users', 'users.list', 'users.update' ]));
        static::assertFalse($permission->hasAll([ 'users', 'users.update' ]));
        static::assertFalse($permission->hasAll([ 'users.update' ]));

        static::assertFalse($permission->hasOne([ 'users.update' ]));
        static::assertFalse($permission->hasOne([ 'users.update', 'users.truncate' ]));
        static::assertFalse($permission->hasOne([ ]));

        // Test add rule by passing strings as arguments, instead of a ParameterRule.
        $permission->add('users.remove.administrator.revokeRights', 'Revoke Rights', 'description');

        $permissionAdded = $permission->get('users.remove.administrator.revokeRights');

        static::assertSame('users.remove.administrator.revokeRights', $permissionAdded->name);
        static::assertSame('Revoke Rights', $permissionAdded->title);
        static::assertSame('description', $permissionAdded->description);
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
        $permission->add(new PermissionRule('users'));
        $permission->add(new PermissionRule('users.list'));
        $permission->add(new PermissionRule('users.create'));
        $permission->add(new PermissionRule('users.remove'));
        $permission->add(new PermissionRule('users.remove.administrator'));

        // Simple cases.
        static::assertEquals([ ], $permission->getOnly([ ])->getAllNames());
        static::assertEquals([ 'users' ], $permission->getOnly([ 'users' ])->getAllNames());
        static::assertEquals([ 'users', 'users.list' ], $permission->getOnly([ 'users', 'users.list' ])->getAllNames());
        static::assertEquals([ 'users', 'users.remove' ], $permission->getOnly([ 'users', 'users.remove' ])->getAllNames());
        static::assertEquals([ 'users', 'users.remove' ], $permission->getOnly([ 'users.remove', 'users' ])->getAllNames());

        static::assertCount(3, $permission->getOnly([ 'users', 'users.remove', 'users.remove.administrator' ])->getAll());
        static::assertCount(4, $permission->getOnly([ 'users', 'users.create', 'users.remove', 'users.remove.administrator' ])->getAll());

        // Not matched parents cases.
        static::assertEquals([ ], $permission->getOnly([ 'users.remove' ])->getAllNames());
        static::assertEquals([ ], $permission->getOnly([ 'users.remove', 'users.remove.administrator' ])->getAllNames());

        // Simulate sub-user permission.
        $userPermissions = $permission->getOnly([ 'users', 'users.list', 'users.create', 'users.remove' ]);
        static::assertCount(4, $userPermissions->getAll());

        // Note: this user not have 'users.remove.administrator' permission.
        // So it will not be allowed on sub-user permissions.
        $subUserPermissions = $userPermissions->getOnly([
            'users',
            'users.list',
            'users.create',
            'users.remove',
            'users.remove.administrator',
        ]);
        static::assertCount(4, $subUserPermissions->getAll());
    }
}
