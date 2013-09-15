<?php

namespace Bazalt\Auth\Model\Base;

abstract class Permission extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_permissions';

    const MODEL_NAME = 'Bazalt\\Auth\\Model\\Permission';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'varchar(50)');
        $this->hasColumn('description', 'varchar(255)');
    }

    public function initRelations()
    {
    }
}