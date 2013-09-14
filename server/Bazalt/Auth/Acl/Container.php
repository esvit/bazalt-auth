<?php

namespace Bazalt\Auth\Acl;

interface Container
{
    public function getAclLevels();

    public function fillUserLevels(\Bazalt\Auth\Model\User $user);
}