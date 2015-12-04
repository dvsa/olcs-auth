<?php

/**
 * Default Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Zend\Http\Client;
use Zend\Http\Header\SetCookie;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Default Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DefaultController extends AbstractActionController
{
    public function loginAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $post = $request->getPost()->toArray();

            if (isset($post['username']) && isset($post['password'])) {
                if ($this->authenticate($post['username'], $post['password'])) {

                    $goto = $this->params()->fromQuery('goto');

                    if ($goto !== null) {
                        return $this->redirect()->toUrl($goto);
                    }

                    return $this->redirect()->toRoute('index');
                }
            }
        }

//        $this->beginAuthentication();

        $view = new ViewModel();
        $view->setTemplate('pages/auth/login');

        return $view;
    }

    /**
     * The webagent should redirect this to OpenAM logout page
     */
    public function logoutAction()
    {
        die('webagent needs config');
    }

    public function confirmLogoutAction()
    {
        return $this->redirect()->toRoute('index');
    }

    public function forgotAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $post = $request->getPost();

            if ($this->forgotPassword($post['username'], $post['email'])) {


            }
        }

        $view = new ViewModel();
        $view->setTemplate('pages/auth/forgot');

        return $view;
    }

    protected function authenticate($username, $password)
    {
        $options = [
            'adapter' => Client\Adapter\Curl::class,
        ];

        $headers = new Headers();
        $headers->addHeaderLine('X-OpenAM-Username', $username);
        $headers->addHeaderLine('X-OpenAM-Password', $password);
        $headers->addHeaderLine('Content-Type', 'application/json');

        $client = new Client('http://olcs-selfserve.olcs.gov.uk/secure/json/authenticate?realm=selfserve', $options);
        $client->setHeaders($headers);
        $client->setMethod(Request::METHOD_POST);

        $response = $client->send();

        /**
         * @todo check json decode is ok
         */
        $result = json_decode($response->getContent(), true);

        if ($response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addCurrentErrorMessage('Authentication failed: ' . $result['message']);
            return false;
        }

        $cookie = new SetCookie('secureToken', $result['tokenId'], null, '/', '.olcs.gov.uk');
        $headers = $this->getResponse()->getHeaders();
        $headers->addHeader($cookie);

        return true;
    }

    protected function forgotPassword($username, $email)
    {
        $data = [
            'subject' => 'Some forgotten password subject',
            'message' => 'Some message about clicking the link'
        ];

        if (!empty($username)) {
            $data['username'] = $username;
        } else {
            $data['email'] = $email;
        }

        $options = [
            'adapter' => Client\Adapter\Curl::class,
        ];

        $headers = new Headers();
        $headers->addHeaderLine('Content-Type', 'application/json');

        $client = new Client('http://olcs-selfserve.olcs.gov.uk/secure/json/users/?realm=selfserve&_action=forgotPassword', $options);
        $client->setRawBody(json_encode($data));
        $client->setHeaders($headers);
        $client->setMethod(Request::METHOD_POST);

        $response = $client->send();

        /**
         * @todo check json decode is ok
         */
        $result = json_decode($response->getContent(), true);

        print '<pre>';
        print_r($result);
        exit;

        return true;
    }

    protected function encodePassword($password)
    {
        return $password;
        //return '{MD5}' . base64_encode(hex2bin(md5($password)));
    }
}
