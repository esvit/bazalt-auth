<?php

namespace Bazalt\Auth\Model\Base;

abstract class SiteRefUser extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_sites_ref_users';

    const MODEL_NAME = 'Bazalt\\Auth\\Model\\SiteRefUser';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('site_id', 'PU:int(10)');
        $this->hasColumn('user_id', 'PU:int(10)');
        $this->hasColumn('last_activity', 'datetime');
        $this->hasColumn('session_id', 'varchar(50)');
    }

    public function initRelations()
    {
    }
}