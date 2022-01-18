<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Service\Cqrs;
use Dvsa\Olcs\Auth\Form\ForgotPasswordForm;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;

class ForgotPasswordController extends AbstractController
{
    /**
     * Forgot password page
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute('auth/login/GET');
        }

        /** @var Request $request */
        $request = $this->getRequest();

        /** @var Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest(ForgotPasswordForm::class, $request);

        $form->setData($request->getPost());

        if ($request->isPost() === false || $form->isValid() === false) {
            return $this->renderFormView($form);
        }

        try {
            $formData = $form->getData();
            $passwordService = $this->getServiceLocator()->get(PasswordService::class);
            assert($passwordService instanceof PasswordService);
            $result = $passwordService->forgotPassword($formData['username']);
        } catch (Cqrs\Exception $e) {
            return $this->renderFormView($form, true, 'unknown-error');
        }

        if (!$result['flags']['success']) {
            return $this->renderFormView($form, true, $result['messages'][0] ?? '');
        }

        return $this->renderConfirmationView();
    }

    /**
     * Render the form view
     *
     * @param Form   $form          Form
     * @param bool   $failed        Failed
     * @param string $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderFormView(Form $form, $failed = false, $failureReason = null)
    {
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/forgot-password');

        return $view;
    }

    /**
     * Render the confirmation view
     *
     * @return ViewModel
     */
    private function renderConfirmationView()
    {
        $this->layout('auth/layout');
        $view = new ViewModel();
        $view->setTemplate('auth/confirm-forgot-password');
        return $view;
    }
}
