<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

/**
 * Hash Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class HashService
{
    /**
     * Hash password
     *
     * @param string $password Password
     *
     * @return string
     */
    public static function hashPassword($password)
    {
        // @todo OLCS-13439
        return $password;
        //return sha1($password);
    }
}
