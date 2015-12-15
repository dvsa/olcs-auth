<?php

/**
 * Change Password Service
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Headers;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface as Request;

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
     * @param ServiceLocatorInterface $serviceLocator
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
     * @param Request $request
     * @param string $oldPassword
     * @param string $newPassword
     * @return array
     */
    public function updatePassword(Request $request, $oldPassword, $newPassword)
    {
        $token = $this->cookieService->getCookie($request);

        $username = $this->getIdFromSession($token);

        $data = [
            // @todo Maybe remove all logic around hashing
            'currentpassword' => HashService::hashPassword($oldPassword),
            'userpassword' => HashService::hashPassword($newPassword)
        ];

        $uri = sprintf('json/users/%s?_action=changePassword', $username);

        $headers = new Headers();
        $headers->addHeaderLine($this->cookieService->getCookieName(), $token);

        return $this->decodeContent($this->post($uri, $data, $headers));
    }

    /**
     * Get OpenAM Id from the session
     *
     * @param string $token
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
