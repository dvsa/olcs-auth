<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Service\Cqrs;
use Dvsa\Olcs\Auth\Form\ForgotPasswordForm;
use Dvsa\Olcs\Auth\Service\Auth\Exception\OpenAmResetPasswordFailedException;
use Dvsa\Olcs\Auth\Service\Auth\Exception\UserCannotResetPasswordException;
use Dvsa\Olcs\Auth\Service\Auth\Exception\UserNotFoundException;
use Zend\Form\Form;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService;

/**
 * Forgot Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForgotPasswordController extends AbstractController
{
    /**
     * Forgot password page
     *
     * @return ViewModel|\Zend\Http\Response
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute('auth/login');
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
            $this->getServiceLocator()->get(ForgotPasswordService::class)->forgotPassword($form->getData()['username']);
        } catch (UserNotFoundException $exception) {
            return $this->renderFormView($form, true, 'User not found');
        } catch (Cqrs\Exception $e) {
            return $this->renderFormView($form, true, 'unknown-error');
        } catch (UserCannotResetPasswordException $exception) {
            return $this->renderFormView($form, true, 'account-not-active');
        } catch (OpenAmResetPasswordFailedException $exception) {
            return $this->renderFormView($form, true, $exception->getOpenAmErrorMessage());
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
