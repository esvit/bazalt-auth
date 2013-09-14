<?php

namespace Bazalt\Auth\Model\Base;

abstract class Role extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_roles';

    const MODEL_NAME = 'Bazalt\\Auth\\Model\\Role';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'text');
        $this->hasColumn('is_guest', 'U:tinyint(1)|0');
        $this->hasColumn('system_acl', 'U:tinyint(1)|0');
        $this->hasColumn('is_hidden', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Users', new \Bazalt\ORM\Relation\Many2Many('Bazalt\\Auth\\Model\\User', 'user_id', 'Bazalt\\Auth\\Model\\RoleRefUser', 'role_id'));
    }
}