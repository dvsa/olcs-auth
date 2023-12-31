<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Laminas\Http\Headers;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\RequestInterface as Request;

/**
 * Change Password Service
 */
class ChangePasswordService extends AbstractRestService
{
    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * Create the change password service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cookieService = $serviceLocator->get('Auth\CookieService');

        return parent::createService($serviceLocator);
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
