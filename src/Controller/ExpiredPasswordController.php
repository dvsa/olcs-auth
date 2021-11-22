<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Rbac\JWTIdentityProvider;
use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Dvsa\Olcs\Transfer\Command\Auth\ChangeExpiredPassword;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Exception;
use Laminas\Authentication\Storage\Session;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\View;

/**
 * Expired Password Controller
 */
class ExpiredPasswordController extends AbstractActionController
{
    const MESSAGE_BASE = "Expired Password Change Failed: %s";
    const MESSAGE_RESULT_NOT_OK = 'Result is not ok';
    const MESSAGE_AUTH_RESULT_NOT_VALID = 'Result is not valid';
    const MESSAGE_IDENTITY_MISSING= 'Result is missing new identity';
    const MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED = 'Expected challenge name to be NEW_PASSWORD_REQUIRED';

    const ROUTE_INDEX = 'index';
    const ROUTE_LOGIN = 'auth/login/GET';

    protected Form $form;

    protected CommandSender $commandSender;

    /**
     * Expired password page
     *
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        $this->commandSender = $this->getServiceLocator()->get('CommandSender');

        $request = $this->getRequest();

        $this->form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest(ChangePasswordForm::class, $request);

        if ($request->isPost() === false) {
            return $this->renderView($this->form);
        }

        $this->form->setData($request->getPost());

        if ($this->form->isValid() === false) {
            return $this->renderView($this->form);
        }

        $data = $this->form->getData();

        $config = $this->serviceLocator->get('Config');

        // TODO: Remove check and use updatePasswordCommand once OpenAM support is dropped
        if ($config['auth']['identity_provider'] !== JWTIdentityProvider::class) {
            return $this->updatePasswordOpenAm(
                $data['oldPassword'],
                $data['newPassword'],
                $data['confirmPassword']
            );
        }

        return $this->updatePasswordCommand($data['newPassword']);
    }

    /**
     * Render the view
     *
     * @param \Laminas\Form\Form $form          Form
     * @param bool            $failed        Failed
     * @param string          $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderView(\Laminas\Form\Form $form, $failed = false, $failureReason = null)
    {
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/expired-password');

        return $view;
    }

    /**
     * Get expired password service
     *
     * @return ExpiredPasswordService
     */
    private function getExpiredPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ExpiredPasswordService');
    }

    /**
     * Get the login service
     *
     * @return LoginService
     */
    private function getLoginService()
    {
        return $this->getServiceLocator()->get('Auth\LoginService');
    }

    /**
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return Response|ViewModel
     * @deprecated Will be removed once OpenAM support is dropped
     */
    private function updatePasswordOpenAm(string $oldPassword, string $newPassword, string $confirmPassword)
    {
        $authId = $this->params('authToken');

        $result = $this->getExpiredPasswordService()->updatePassword(
            $authId,
            $oldPassword,
            $newPassword,
            $confirmPassword
        );

        if ($result['status'] != 200) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
            return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }

        if (isset($result['tokenId'])) {
            return $this->getLoginService()
                ->login($result['tokenId'], $this->getResponse(), $this->params()->fromQuery('goto'));
        }

        $failureReason = preg_replace('/(Change Password\<BR\>\<\/BR\>)/', '', $result['header']);
        return $this->renderView($this->form, true, $failureReason);
    }

    /**
     * @throws Exception
     * @returns Response|ViewModel
     */
    private function updatePasswordCommand(string $newPassword)
    {
        $authChallengeContainer = new AuthChallengeContainer();
        if ($authChallengeContainer->getChallengeName() !== AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED) {
            throw new Exception(sprintf(static::MESSAGE_BASE, static::MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED));
        }

        $command = ChangeExpiredPassword::create([
            'newPassword' => $newPassword,
            'challengeSession' => $authChallengeContainer->getChallengeSession(),
            'username' => $authChallengeContainer->getChallengedIdentity()
        ]);

        $result = $this->commandSender->send($command);

        // We OK?
        if (!$result->isOk()) {
            throw new Exception(sprintf(static::MESSAGE_BASE, static::MESSAGE_RESULT_NOT_OK));
        }

        $result = ChangeExpiredPasswordResult::fromArray($result->getResult()['flags']);
        if (!$result->isValid() || empty($result->getIdentity())) {
            return $this->handleInvalidResponse($result);
        }

        $sessionContainer = $this->getServiceLocator()->get(Session::class);
        assert($sessionContainer instanceof Session, 'Expected $sessionContainer to be instance of ' . Session::class);
        $sessionContainer->write($result->getIdentity());
        $authChallengeContainer->clear();

        return $this->redirect()->toRoute(static::ROUTE_INDEX);
    }

    /**
     * @return ViewModel|Response
     * @throws Exception
     */
    protected function handleInvalidResponse(ChangeExpiredPasswordResult $result)
    {
        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID) {
            $element = $this->form->get('newPassword');
            $element->setOption('error-message', null); //TODO: Remove once we drop OpenAM support
            $element->setMessages($result->getMessages());
            return $this->renderView($this->form);
        }
        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED) {
            foreach ($result->getMessages() as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
            return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        throw new Exception(sprintf("Invalid response from ChangeExpiredPassword Command: %s", implode('. ', $result->getMessages())));
    }
}
