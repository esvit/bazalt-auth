<?php

namespace Bazalt\Auth;

class Acl
{
    /**
     * user is allowed to view the domain object / field
     */
    const MASK_VIEW = 1;

    /**
     * user is allowed to create new instances of the domain object / fields
     */
    const MASK_CREATE = 2;

    /**
     * user is allowed to edit existing instances of the domain object / field
     */
    const MASK_EDIT = 4;

    /**
     * user is allowed to delete domain objects
     */
    const MASK_DELETE = 8;

    /**
     * user is allowed to recover domain objects from trash
     */
    const MASK_UNDELETE = 16;

    /**
     * user is allowed to perform any action on the domain object except for granting others permissions
     */
    const MASK_OPERATOR = 32;

    /**
     * user is allowed to perform any action on the domain object,
     * and is allowed to grant other users any permission except for MASTER and OWNER permissions
     */
    const MASK_MASTER = 64;

    /**
     * user is owning the domain object in question and can perform any action on the domain object
     * as well as grant any permission
     */
    const MASK_OWNER = 128;

    /**
     * user has all permissions
     */
    const MASK_GOD = 256;
}