<?php

namespace Bazalt\Auth\Model\Base;

abstract class RoleRefPermission extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_roles_permissions';

    const MODEL_NAME = 'Bazalt\\Auth\\Model\\RoleRefPermission';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('role_id', 'PU:int(10)');
        $this->hasColumn('permission_id', 'varchar(50)');
    }

    public function initRelations()
    {
    }
}