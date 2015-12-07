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
class PasswordCallback
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

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
        $this->label = $label;
        $this->name = $name;
        $this->value = $value;
        $this->hash = $hash;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => 'PasswordCallback',
            'output' => [['name' => 'prompt', 'value' => $this->label]],
            'input' => [
                [
                    'name' => $this->name,
                    'value' => $this->hash ? HashService::hashPassword($this->value) : $this->value
                ]
            ]
        ];
    }
}
