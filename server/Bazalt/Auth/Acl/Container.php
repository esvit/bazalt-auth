<?php

namespace Bazalt\Auth\Acl;

interface Container
{
    public function getAclLevels();

    public function getUserLevels(\Bazalt\Auth\Model\User $user, &$levels);
}