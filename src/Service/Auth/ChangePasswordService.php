<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Interop\Container\ContainerInterface;
use Laminas\Http\Headers;
use Laminas\Stdlib\RequestInterface as Request;

class ChangePasswordService extends AbstractRestService
{
    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * Create the change password service
     *
     * @return $this
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->cookieService = $container->get('Auth\CookieService');

        return parent::__invoke($container, $requestedName, $options);
    }

    /**
     * Update password
     *
     * @param Request $request     Request
     * @param string  $oldPassword Old password
     * @param string  $newPassword New password
     *
     * @return array
     */
    public function updatePassword(Request $request, $oldPassword, $newPassword)
    {
        $token = $this->cookieService->getCookie($request);

        $username = $this->getIdFromSession($token);

        $data = [
            'currentpassword' => $oldPassword,
            'userpassword' => $newPassword
        ];

        $uri = sprintf('json/users/%s?_action=changePassword', $username);

        $headers = new Headers();
        $headers->addHeaderLine($this->cookieService->getCookieName(), $token);

        return $this->decodeContent($this->post($uri, $data, $headers));
    }

    /**
     * Get OpenAM Id from the session
     *
     * @param string $token Token
     *
     * @return string
     */
    private function getIdFromSession($token)
    {
        $headers = new Headers();
        $headers->addHeaderLine($this->cookieService->getCookieName(), $token);

        $data = $this->decodeContent($this->post('json/users/?_action=idFromSession', [], $headers));

        return !empty($data['id']) ? $data['id'] : null;
    }
}
