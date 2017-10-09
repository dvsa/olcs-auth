<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Common\Service\Cqrs\Exception;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Auth\Service\Auth\Exception\OpenAmResetPasswordFailedException;
use Dvsa\Olcs\Auth\Service\Auth\Exception\UserCannotResetPasswordException;
use Dvsa\Olcs\Auth\Service\Auth\Exception\UserNotFoundException;
use Dvsa\Olcs\Transfer\Query\User\Pid;
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
     * @var QuerySender
     */
    private $querySender;

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
        $this->querySender = $serviceLocator->get('QuerySender');

        return parent::createService($serviceLocator);
    }

    /**
     * Forgot password
     *
     * @param string $username Username
     *
     * @throws UserNotFoundException
     * @throws Exception
     * @throws UserCannotResetPasswordException
     * @throws OpenAmResetPasswordFailedException
     * @return void
     */
    public function forgotPassword($username)
    {
        $this->guardAgainstNonResettableUser($username);

        $data = [
            'username' => $username,
            'subject' => $this->translator->translate('auth.forgot-password.email.subject'),
            'message' => $this->translator->translate('auth.forgot-password.email.message')
        ];

        $result = $this->decodeContent($this->post('json/users/?_action=forgotPassword', $data));

        if ($result['status'] != 200) {
            throw new OpenAmResetPasswordFailedException($result['message']);
        }
    }

    /**
     * Raise an exception if a user password cannot be reset
     *
     * @param string $username Username
     *
     * @throws Exception
     * @throws UserCannotResetPasswordException
     * @throws UserNotFoundException
     * @return void
     */
    private function guardAgainstNonResettableUser($username)
    {
        try {
            $response = $this->querySender->send(Pid::create(['id' => $username]));
        } catch (NotFoundException $e) {
            throw new UserNotFoundException();
        }

        if (!$response->isOk()) {
            throw new Exception('Pid service failed: ' . $response->getBody());
        }

        $pidResult = $response->getResult();

        if (!isset($pidResult['canResetPassword']) || $pidResult['canResetPassword'] !== true) {
            throw new UserCannotResetPasswordException();
        }
    }
}
