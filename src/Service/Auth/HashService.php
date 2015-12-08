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
    /**
     * Hash password
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        return sha1($password);
    }
}
