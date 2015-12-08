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
 * @todo Maybe remove all logic around hashing
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class HashService
{
    /**
     * Hash password
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        // @todo Maybe remove all logic around hashing
        return $password;
        //return sha1($password);
    }
}
