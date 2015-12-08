<?php

/**
 * Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Request
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Request
{
    const STAGE_AUTHENTICATE = 'LDAP1';
    const STAGE_EXPIRED_PASSWORD = 'LDAP2';

    /**
     * @var string
     */
    private $authId;

    /**
     * @var string
     */
    private $stage;

    /**
     * @var CallbackInterface[]|array
     */
    private $callbacks = [];

    /**
     * Create a request
     *
     * @param string $authId
     * @param string $stage
     * @param CallbackInterface[]|array $callbacks
     */
    public function __construct($authId, $stage, array $callbacks = [])
    {
        $this->authId = $authId;
        $this->stage = $stage;
        $this->callbacks = $callbacks;
    }

    /**
     * Add callback
     *
     * @param CallbackInterface $callback
     */
    public function addCallback(CallbackInterface $callback)
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray()
    {
        $callbacks = [];

        /** @var CallbackInterface $callback */
        foreach ($this->callbacks as $callback) {
            $callbacks[] = $callback->toArray();
        }

        return [
            'authId' => $this->authId,
            'stage' => $this->stage,
            'callbacks' => $callbacks
        ];
    }
}
