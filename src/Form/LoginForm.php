<?php

namespace Dvsa\Olcs\Auth\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("login-form")
 * @Form\Attributes({"method":"post"})
 */
class LoginForm
{
    /**
     * @Form\Options({
     *     "label": "auth.login.username",
     *     "short-label": "auth.login.username",
     *     "label_attributes": {
     *         "aria-label": "Enter your username"
     *     }
     * })
     * @Form\Attributes({
     *     "id": "auth.login.username",
     *     "tabindex": "1"
     * })
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Filter({"name": "Common\Filter\StripSpaces"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     * @Form\Type("Text")
     */
    public $username = null;

    /**
     * @Form\Options({
     *     "label": "auth.login.password",
     *     "short-label": "auth.login.password",
     *     "label_attributes": {
     *         "aria-label": "Enter your password"
     *     }
     * })
     * @Form\Attributes({
     *     "id": "auth.login.password",
     *     "tabindex": "2"
     * })
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $password = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.login.button",
     *     "value": "auth.login.button",
     *     "class": "action--primary large",
     *     "tabindex": "3"
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
