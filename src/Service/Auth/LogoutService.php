<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Http\Headers;

class LogoutService extends AbstractRestService
{
    /**
     * @var CookieService
     */
    private $cookieService;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->cookieService = $container->get('Auth\CookieService');

        return parent::__invoke($container, $requestedName, $options);
    }

    /**
     * Delete the session from OpenAM
     *
     * @param string $token Token
     *
     * @return boolean
     */
    public function logout($token)
    {
        $headers = new Headers();
        $headers->addHeaderLine($this->cookieService->getCookieName(), $token);

        $response = $this->post('/json/sessions/?_action=logout', [], $headers);

        return $response->isOk();
    }
}
