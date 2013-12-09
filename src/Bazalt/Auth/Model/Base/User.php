<?php

namespace Bazalt\Auth\Model\Base;

/**
 * Data model for table "cms_users"
 *
 * @category  CMS
 * @package   DataModel
 *
 * @property-read int $id
 * @property-read string $login
 * @property-read string $password
 * @property-read string $firstname
 * @property-read string $secondname
 * @property-read string $patronymic
 * @property-read string $email
 * @property-read string $gender
 * @property-read string $birth_date
 * @property-read string $avatar
 * @property-read bool $is_active
 * @property-read bool $is_deleted
 * @property-read string $last_activity
 * @property-read string $created_at
 * @property-read bool $is_god
 * @property-read bool $need_edit
 */
abstract class User extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'cms_users';

    const MODEL_NAME = 'Bazalt\\Auth\\Model\\User';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('login', 'varchar(255)');
        $this->hasColumn('password', 'varchar(255)');
        $this->hasColumn('firstname', 'varchar(255)');
        $this->hasColumn('secondname', 'varchar(255)');
        $this->hasColumn('patronymic', 'varchar(255)');
        $this->hasColumn('avatar', 'varchar(255)');
        $this->hasColumn('email', 'N:varchar(60)');
        $this->hasColumn('gender', "ENUM('unknown','male','female')|'unknown'");
        $this->hasColumn('birth_date', 'N:date');
        $this->hasColumn('is_active', 'U:tinyint(1)');
        $this->hasColumn('is_deleted', 'U:tinyint(1)|0');
        $this->hasColumn('last_activity', 'N:datetime');
        $this->hasColumn('is_god', 'U:tinyint(1)');
        $this->hasColumn('need_edit', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Roles', new \Bazalt\ORM\Relation\Many2Many('Bazalt\Auth\Model\Role', 'user_id', 'Bazalt\Auth\Model\RoleRefUser', 'role_id'));
        $this->hasRelation('Settings', new \Bazalt\ORM\Relation\One2Many('Bazalt\Auth\Model\UserSetting', 'id', 'user_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\ORM\Plugin\Timestampable', ['created' => 'created_at']);
    }
}