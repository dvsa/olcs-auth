<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Dvsa\Olcs\Auth\Service\Auth\Exception\RuntimeException;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Cookie Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieService implements FactoryInterface
{
    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var string
     */
    private $cookieDomain;

    /**
     * @var \Zend\Http\Request
     */
    private $request;

    /**
     * Create the cookie service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $this->request = $serviceLocator->get('Request');

        if (empty($config['openam']['cookie']['name'])) {
            throw new RuntimeException('openam/cookie is required but missing from config');
        }

        $this->cookieName = $config['openam']['cookie']['name'];

        if (isset($config['openam']['cookie']['domain'])) {
            $this->cookieDomain = $config['openam']['cookie']['domain'];
        }

        return $this;
    }

    /**
     * Create the token cookie
     *
     * @param Response $response Response
     * @param string   $token    Token
     *
     * @return void
     */
    public function createTokenCookie(Response $response, $token, $expireWithinHour = false)
    {
        $expires = null;

        if ($expireWithinHour) {
            try {
                $now = new DateTimeImmutable('now');
                $nextHour = $now->add(new DateInterval("PT1H"));
                $gmtTimezone = new DateTimeZone('GMT');
                $expires = DateTime::createFromFormat("Y-m-d H:i:s", $nextHour->format("Y-m-d H:i:s"),$gmtTimezone);
                $expires = gmdate('D, d-M-Y H:i:s', $expires->getTimestamp()) . ' GMT';

            } catch (Exception $e) {
                //Couldn't calculate date, leave $expires as null - end of session
            }
        }
        $cookie = new SetCookie($this->cookieName, $token, $expires, '/', $this->getCookieDomain(), false, true);
        $headers = $response->getHeaders();
        $headers->addHeader($cookie);
    }

    /**
     * Destroy cookie
     *
     * @param Response $response Response
     *
     * @return void
     */
    public function destroyCookie(Response $response)
    {
        $cookie = new SetCookie($this->cookieName, null, strtotime('-1 year'), '/', $this->getCookieDomain());
        $headers = $response->getHeaders();
        $headers->addHeader($cookie);
    }

    /**
     * Get the cookie value
     *
     * @param Request $request Request
     *
     * @return null|string
     */
    public function getCookie(Request $request)
    {
        $cookie = $request->getHeaders()->get('Cookie');

        if (empty($cookie->{$this->cookieName})) {
            return null;
        }

        return $cookie->{$this->cookieName};
    }

    /**
     * Get the cookie name
     *
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookieName;
    }

    /**
     * Get the cookie domain
     *
     * @return null|string
     */
    protected function getCookieDomain()
    {
        if ($this->cookieDomain === null) {
            return null;
        }

        $host = $this->request->getUri()->getHost();

        if (!strstr($host, $this->cookieDomain)) {
            return null;
        }

        return $this->cookieDomain;
    }
}
