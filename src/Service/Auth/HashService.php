<?php

/**
 * Hash Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

/**
 * Hash Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class HashService
{
    public static function hashPassword($password)
    {
        return $password;

        // @todo when we are in a position to hash all passwords
        //return sha1($password);
    }
}
