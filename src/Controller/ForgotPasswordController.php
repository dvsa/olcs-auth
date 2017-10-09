<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Service\Cqrs;
use Dvsa\Olcs\Auth\Form\ForgotPasswordForm;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\Form\Form;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService;
use Dvsa\Olcs\Transfer\Query\User\Pid;

/**
 * Forgot Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @method Cqrs\Response handleQuery(QueryInterface $query)
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
            return $this->renderView($form);
        }

        $username = $form->getData()['username'];

        $resetPreventionReason = $this->determineResetPreventionReason($username);
        if (!is_null($resetPreventionReason)) {
            return $this->renderView($form, true, $resetPreventionReason);
        }

        $result = $this->getForgotPasswordService()->forgotPassword($username);
        if ($result['status'] != 200) {
            return $this->renderView($form, true, $result['message']);
        }

        /**
         * Rather than redirecting, we show a different view in this case, that way the screen can only be shown
         * when a successful request has occurred
         */
        $this->layout('auth/layout');
        $view = new ViewModel();
        $view->setTemplate('auth/confirm-forgot-password');

        return $view;
    }

    /**
     * Render the view
     *
     * @param Form   $form          Form
     * @param bool   $failed        Failed
     * @param string $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderView(Form $form, $failed = false, $failureReason = null)
    {
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/forgot-password');

        return $view;
    }

    /**
     * Get forgot password service
     *
     * @return ForgotPasswordService
     */
    private function getForgotPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ForgotPasswordService');
    }

    /**
     * Determine any reason why the password reset may not continue
     *
     * @param string $username The username who's password is to be reset
     *
     * @return string|null Translatable reason why a user's password can't be reset, or null if it can be
     */
    private function determineResetPreventionReason($username)
    {
        try {
            /** @var Cqrs\Response $response */
            $response = $this->handleQuery(Pid::create(['id' => $username]));
        } catch (Cqrs\Exception\NotFoundException $e) {
            // Mimic the OpenAM error message
            return 'User not found';
        } catch (Cqrs\Exception $e) {
            return 'unknown-error';
        }

        if (!$response->isOk()) {
            return 'unknown-error';
        }

        $pidResult = $response->getResult();

        if (!isset($pidResult['canResetPassword']) || $pidResult['canResetPassword'] !== true) {
            return 'account-not-active';
        }

        return null;
    }
}
