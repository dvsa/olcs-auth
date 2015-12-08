<?php

/**
 * Password Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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
     * @param string $label
     * @param string $name
     * @param string $value
     * @param bool|true $hash
     */
    public function __construct($label, $name, $value, $hash = true)
    {
        parent::__construct($label, $name, $value);

        // @todo Maybe remove all logic around hashing
        $this->hash = $hash;
    }

    /**
     * Get filtered value
     *
     * @return string
     */
    protected function getFilteredValue()
    {
        return $this->hash ? HashService::hashPassword($this->value) : $this->value;
    }
}
