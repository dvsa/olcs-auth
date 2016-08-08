<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

use Dvsa\Olcs\Auth\Service\Auth\HashService;

/**
 * Password Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PasswordCallback extends AbstractTextPromptCallback
{
    /**
     * @var string
     */
    protected $type = 'PasswordCallback';

    /**
     * @var boolean
     */
    private $hash;

    /**
     * Construct the object
     *
     * @param string    $label Label
     * @param string    $name  Name
     * @param string    $value Value
     * @param bool|true $hash  Whether to hash the password
     */
    public function __construct($label, $name, $value, $hash = true)
    {
        parent::__construct($label, $name, $value);

        // @todo OLCS-13439
        $this->hash = $hash;
    }

    /**
     * Get filtered value
     *
     * @return string
     */
    protected function getFilteredValue()
    {
        // @todo OLCS-13439
        return $this->hash ? HashService::hashPassword($this->value) : $this->value;
    }
}
