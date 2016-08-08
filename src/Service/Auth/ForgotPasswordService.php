<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Forgot Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForgotPasswordService extends AbstractRestService
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Configure a restful service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->translator = $serviceLocator->get('Translator');

        return parent::createService($serviceLocator);
    }

    /**
     * Forgot password
     *
     * @param string $username Username
     *
     * @return array
     */
    public function forgotPassword($username)
    {
        $data = [
            'username' => $username,
            'subject' => $this->translator->translate('auth.forgot-password.email.subject'),
            'message' => $this->translator->translate('auth.forgot-password.email.message')
        ];

        return $this->decodeContent($this->post('json/users/?_action=forgotPassword', $data));
    }
}
