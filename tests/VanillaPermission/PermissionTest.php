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
     * @covers Rentalhost\VanillaPermission\Permission::hasChildren
     * @covers Rentalhost\VanillaPermission\PermissionRule::getData
     */
    public function testBasic()
    {
        $permission = new Permission;
        $permission->add(new PermissionRule('users'));
        $permission->add(new PermissionRule('users.list'));
        $permission->add(new PermissionRule('users.create'));
        $permission->add(new PermissionRule('users.remove'));
        $permission->add(new PermissionRule('users.remove.administrator'));

        $checkRules = [ 'users', 'users.create', 'users.list', 'users.remove', 'users.remove.administrator' ];

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
        $permission->add('users.remove.administrator.revokeRights', 'Revoke Rights', 'description', [ 'data1' => 'value1' ]);

        $permissionAdded = $permission->get('users.remove.administrator.revokeRights');

        static::assertSame('users.remove.administrator.revokeRights', $permissionAdded->name);
        static::assertSame('Revoke Rights', $permissionAdded->title);
        static::assertSame('description', $permissionAdded->description);
        static::assertSame([ 'data1' => 'value1' ], $permissionAdded->getData());
        static::assertSame('value1', $permissionAdded->getData('data1'));
        static::assertSame('value2', $permissionAdded->getData('data2', 'value2'));

        // Test if rule has children.
        static::assertSame(false, $permission->hasChildren('unknow'));
        static::assertSame(true, $permission->hasChildren('users'));
        static::assertSame(false, $permission->hasChildren('users.list'));
        static::assertSame(false, $permission->hasChildren('users.create'));
        static::assertSame(true, $permission->hasChildren('users.remove'));
        static::assertSame(true, $permission->hasChildren('users.remove.administrator'));
        static::assertSame(false, $permission->hasChildren('users.remove.administrator.revokeRights'));
    }

    /**
     * Test get method, sorting results.
     * @covers Rentalhost\VanillaPermission\Permission::getAllNames
     * @covers Rentalhost\VanillaPermission\Permission::sortRules
     */
    public function testGetSorted()
    {
        $permission = new Permission;
        $permission->add(new PermissionRule('view.remove'));
        $permission->add(new PermissionRule('users'));
        $permission->add(new PermissionRule('view.add.generate'));
        $permission->add(new PermissionRule('users.list'));
        $permission->add(new PermissionRule('users.create'));
        $permission->add(new PermissionRule('users.remove'));
        $permission->add(new PermissionRule('users.state'));
        $permission->add(new PermissionRule('users.remove.administrator'));
        $permission->add(new PermissionRule('view.add'));

        static::assertEquals([
            'users',
            'users.create',
            'users.list',
            'users.remove',
            'users.remove.administrator',
            'users.state',
            'view.add',
            'view.add.generate',
            'view.remove',
        ], $permission->getAllNames());
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

    /**
     * Test if getOnly will accept rules in any order.
     */
    public function testGetOnlyShouldAcceptMixedOrderedRules()
    {
        $permission = new Permission;
        $permission->add(new PermissionRule('users.list'));
        $permission->add(new PermissionRule('users'));

        static::assertEquals([ 'users', 'users.list' ], $permission->getOnly([ 'users', 'users.list' ])->getAllNames());

        $permission = new Permission;
        $permission->add(new PermissionRule('users'));
        $permission->add(new PermissionRule('users.list'));

        static::assertEquals([ 'users', 'users.list' ], $permission->getOnly([ 'users.list', 'users', ])->getAllNames());
    }
}
