<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Http\Headers;

/**
 * ValidateService contains method to validate user token on open Am
 */
class ValidateService extends AbstractRestService
{
    /** @var CookieService */
    private $cookieSrv;

    /**
     * Create the logout service
     *
     * @return $this
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->cookieSrv = $container->get('Auth\CookieService');

        return parent::__invoke($container, $requestedName, $options);
    }

    /**
     * Validate secure token
     *
     * @param string $token Token
     *
     * @return boolean
     */
    public function validate($token)
    {
        $headers = new Headers();
        $headers->addHeaderLine($this->cookieSrv->getCookieName(), $token);

        $response = $this->post('/json/sessions/?_action=validate', [], $headers);

        if ($response->isOk()) {
            return $this->decodeContent($response);
        }

        return null;
    }
}
