<?php

namespace Bazalt\Login\Model\Base;

abstract class UserSetting extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_users_settings';

    const MODEL_NAME = 'Bazalt\Login\Model\UserSetting';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('user_id', 'P:int(10)');
        $this->hasColumn('setting', 'P:varchar(255)');
        $this->hasColumn('value', 'text');
    }

    public function initRelations()
    {
        $this->hasRelation('User', new \Bazalt\ORM\Relation\One2One('Bazalt\Login\Model\User', 'user_id',  'id'));
    }
}