<?php

/**
 * Abstract Text Prompt Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Abstract Text Prompt Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTextPromptCallback implements CallbackInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $name;

    /**
     * Construct the object
     *
     * @param string $label
     * @param string $name
     * @param string $value
     * @param bool|true $hash
     */
    public function __construct($label, $name, $value)
    {
        $this->label = $label;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'type' => $this->type,
            'output' => [['name' => 'prompt', 'value' => $this->label]],
            'input' => [
                [
                    'name' => $this->name,
                    'value' => $this->getFilteredValue()
                ]
            ]
        ];
    }

    /**
     * Get filtered value
     *
     * @return string
     */
    protected function getFilteredValue()
    {
        return $this->value;
    }
}
