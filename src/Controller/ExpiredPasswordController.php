<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
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
use RuntimeException;

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

    const ROUTE_INDEX = 'dashboard';
    const ROUTE_LOGIN = 'auth/login/GET';

    protected Form $form;

    private AuthChallengeContainer $authChallengeContainer;
    protected CommandSender $commandSender;
    private FormHelperService $formHelper;
    private FlashMessengerHelperService $flashMessenger;
    private ExpiredPasswordService $expiredPasswordService;
    private LoginService $loginService;
    private bool $isOpenAM;
    private Session $sessionContainer;

    public function __construct(
        AuthChallengeContainer $authChallengeContainer,
        CommandSender $commandSender,
        ExpiredPasswordService $expiredPasswordService,
        FlashMessengerHelperService $flashMessenger,
        FormHelperService $formHelper,
        LoginService $loginService,
        Session $sessionContainer,
        bool $isOpenAM
    ) {
        $this->authChallengeContainer = $authChallengeContainer;
        $this->commandSender = $commandSender;
        $this->expiredPasswordService = $expiredPasswordService;
        $this->flashMessenger = $flashMessenger;
        $this->formHelper = $formHelper;
        $this->loginService = $loginService;
        $this->sessionContainer = $sessionContainer;
        $this->isOpenAM = $isOpenAM;
    }

    /**
     * Expired password page
     *
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $this->form = $this->formHelper->createForm(ChangePasswordForm::class);

        /**
         * TODO: VOL:2661 - Remove field from form class instead
         */
        if (!$this->isOpenAM) {
            $this->form->remove('oldPassword');
            $this->form->getInputFilter()->remove('oldPassword');
        }

        if ($request->isPost() === false) {
            return $this->renderView();
        }

        $this->form->setData($request->getPost());

        if ($this->form->isValid() === false) {
            return $this->renderView();
        }

        $data = $this->form->getData();

        // TODO: Remove check and use updatePasswordCommand once OpenAM support is dropped
        if ($this->isOpenAM) {
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
     * @param bool $failed Failed
     * @param string|null $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderView(bool $failed = false, ?string $failureReason = null): ViewModel
    {
        $this->layout('auth/layout');
        $view = new ViewModel([
            'form' => $this->form,
            'failed' => $failed,
            'failureReason' => $failureReason,
            'isOpenAM' => $this->isOpenAM
        ]);
        $view->setTemplate('auth/expired-password');

        return $view;
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
        $authId = $this->params('authId');

        $result = $this->expiredPasswordService->updatePassword(
            $authId,
            $oldPassword,
            $newPassword,
            $confirmPassword
        );

        if ($result['status'] != 200) {
            $this->flashMessenger->addUnknownError();
            return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }

        if (isset($result['tokenId'])) {
            $url =  $this->loginService
                ->login($result['tokenId'], $this->getResponse());
            return $this->redirect()->toUrl($url);
        }

        $failureReason = preg_replace('/(Change Password\<BR\>\<\/BR\>)/', '', $result['header']);
        return $this->renderView(true, $failureReason);
    }

    /**
     * @throws Exception
     * @returns Response|ViewModel
     */
    private function updatePasswordCommand(string $newPassword)
    {
        if ($this->authChallengeContainer->getChallengeName() !== AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED) {
            throw new RuntimeException(sprintf(static::MESSAGE_BASE, static::MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED));
        }

        $command = ChangeExpiredPassword::create([
            'newPassword' => $newPassword,
            'challengeSession' => $this->authChallengeContainer->getChallengeSession(),
            'username' => $this->authChallengeContainer->getChallengedIdentity()
        ]);

        $result = $this->commandSender->send($command);

        // We OK?
        if (!$result->isOk()) {
            throw new RuntimeException(sprintf(static::MESSAGE_BASE, static::MESSAGE_RESULT_NOT_OK));
        }

        $result = ChangeExpiredPasswordResult::fromArray($result->getResult()['flags']);
        if (!$result->isValid() || empty($result->getIdentity())) {
            return $this->handleInvalidResponse($result);
        }

        $this->sessionContainer->write($result->getIdentity());
        $this->authChallengeContainer->clear();

        foreach ($result->getMessages() as $message) {
            $this->flashMessenger()->addSuccessMessage($message);
        }

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
            return $this->renderView();
        }
        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED) {
            foreach ($result->getMessages() as $message) {
                $this->flashMessenger->addErrorMessage($message);
            }
            return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD) {
            $element = $this->form->get('newPassword');
            $element->setOption('error-message', null);
            $element->setMessages(['auth.expired-password.new-password-same-as-previous']);
            return $this->renderView($this->form);
        }
        throw new RuntimeException(sprintf("Invalid response from ChangeExpiredPassword Command: %s", implode('. ', $result->getMessages())));
    }
}
