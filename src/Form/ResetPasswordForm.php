<?php

namespace Dvsa\Olcs\Auth\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("reset-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ResetPasswordForm
{
    /**
     * @Form\Options({
     *     "label": "auth.reset-password.new-password",
     *     "short-label": "auth.reset-password.new-password"
     * })
     * @Form\Attributes({"id": "auth.expired-password.new-password", "tabindex": 1})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $newPassword = null;

    /**
     * @Form\Options({
     *     "label": "auth.reset-password.confirm-password",
     *     "short-label": "auth.reset-password.confirm-password"
     * })
     * @Form\Attributes({"id": "auth.expired-password.confirm-password", "tabindex": 2})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "Zend\Validator\Identical", "options": {"token": "newPassword"}})
     * @Form\Type("Password")
     */
    public $confirmPassword = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.reset-password.button",
     *     "value": "auth.reset-password.button",
     *     "class": "action--primary large",
     *     "tabindex": 3
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
