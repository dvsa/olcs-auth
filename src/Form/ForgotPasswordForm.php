<?php

namespace Dvsa\Olcs\Auth\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("forgot-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ForgotPasswordForm
{
    /**
     * @Form\Options({"label": "auth.forgot-password.username", "short-label": "auth.forgot-password.username"})
     * @Form\Attributes({"id": "auth.forgot-password.username", "tabindex": 1})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Text")
     */
    public $username = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.forgot-password.button",
     *     "value": "auth.forgot-password.button",
     *     "class": "action--primary large",
     *     "tabindex": 2
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
