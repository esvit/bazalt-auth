<?php

namespace Bazalt\Auth\Model;
use Bazalt\ORM;

class Role extends Base\Role
{
    public static function create()
    {
        $role = new Role();
        $role->system_acl = 0;
        $role->description = '';

        return $role;
    }

    public static function getUsersByRole($role)
    {
        $q = ORM::select('Bazalt\Auth\Model\User u')
                ->innerJoin('Bazalt\Auth\Model\RoleRefUser ref', array('user_id', 'u.id'))
                ->where('ref.role_id = ?', $role->id);

        return $q->fetchAll();
    }

    public static function getGuestRoles()
    {
        $q = ORM::select('Bazalt\Auth\Model\Role r')
            ->where('is_guest = ?', 1);

        return $q->fetchAll();
    }

    public function getByName($name)
    {
        $q = ORM::select('Role r')
                ->where('name = ?', $name);

        return $q->fetch();
    }

    public function getPermissions()
    {
        $q = ORM::select('Bazalt\\Auth\\Model\\Permission p', 'p.id')
            ->innerJoin('Bazalt\\Auth\\Model\\RoleRefPermission rp', ['permission_id', 'p.id'])
            ->where('rp.role_id = ?', $this->id);
        return $q->fetchAll();
    }

    public static function getBitmask($roles, $component)
    {
        $acls = array();

        $q = ORM::select('Bazalt\Auth\Model\RoleRefComponent a', 'a.value')
                ->andWhereIn('a.role_id', $roles)
                ->andWhere('a.component_id = ?', $component->id);

        $acls = $q->fetchAll();

        // merge roles
        $res = 0;
        foreach ($acls as $acl) {
            $res |= $acl->value;
        }
        return $res;
    }
}