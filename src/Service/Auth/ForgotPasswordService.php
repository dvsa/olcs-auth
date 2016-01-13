<?php

/**
 * Forgot Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\I18n\Translator;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->translator = $serviceLocator->get('Translator');

        return parent::createService($serviceLocator);
    }

    /**
     * @param $username
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
