<?php

namespace Bazalt\Auth\Model;

class Permission extends Base\Permission
{
    public static function getById($id)
    {
        $q = Permission::select()
                ->where('id = ?', $id);

        return $q->fetch();
    }
}