<?php

namespace Dvsa\Olcs\Auth\Container;

use Laminas\Session\Container;

class AuthChallengeContainer extends Container
{
    protected const CONTAINER_NAME = 'authChallenge';
    protected const KEY_CHALLENGE_NAME = 'challengeName';
    protected const KEY_CHALLENGE_SESSION = 'challengeSession';
    protected const KEY_CHALLENGE_IDENTITY = 'challengeIdentity';
    public const CHALLENEGE_NEW_PASWORD_REQUIRED = 'NEW_PASSWORD_REQUIRED';

    public function __construct()
    {
        parent::__construct(static::CONTAINER_NAME);
    }

    /**
     * @return string
     */
    public function getChallengeSession(): string
    {
        return $this->offsetGet(static::KEY_CHALLENGE_SESSION);
    }

    /**
     * @param string $challengeSession
     * @return AuthChallengeContainer
     */
    public function setChallengeSession(string $challengeSession): AuthChallengeContainer
    {
        $this->offsetSet(static::KEY_CHALLENGE_SESSION, $challengeSession);
        return $this;
    }

    /**
     * @return string
     */
    public function getChallengeName(): string
    {
        return $this->offsetGet(static::KEY_CHALLENGE_NAME);
    }

    /**
     * @param string $challengeName
     * @return AuthChallengeContainer
     */
    public function setChallengeName(string $challengeName): AuthChallengeContainer
    {
        $this->offsetSet(static::KEY_CHALLENGE_NAME, $challengeName);
        return $this;
    }

    /**
     * @return string
     */
    public function getChallengedIdentity(): string
    {
        return $this->offsetGet(static::KEY_CHALLENGE_IDENTITY);
    }

    /**
     * @param string $challengedIdentity :
     * @return AuthChallengeContainer
     */
    public function setChallengedIdentity(string $challengedIdentity): AuthChallengeContainer
    {
        $this->offsetSet(static::KEY_CHALLENGE_IDENTITY, $challengedIdentity);
        return $this;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->exchangeArray([]);
    }
}
