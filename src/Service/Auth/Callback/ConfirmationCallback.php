<?php

/**
 * Confirmation Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Confirmation Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConfirmationCallback
{
    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => 'ConfirmationCallback',
            'output' => [
                ['name' => 'prompt', 'value' => ''],
                ['name' => 'messageType', 'value' => 0],
                ['name' => 'options', 'value' => ['Submit', 'Cancel'] ],
                ['name' => 'optionType', 'value' => -1],
                ['name' => 'defaultOption', 'value' => 0]
            ],
            'input' => [['name' => 'IDToken4', 'value' => 0]]
        ];
    }
}
