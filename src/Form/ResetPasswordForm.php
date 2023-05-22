<?php

namespace Dvsa\Olcs\Auth\Form;

use Laminas\Form\Annotation as Form;

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
     * @Form\Attributes({"id": "auth.reset-password.new-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $newPassword = null;

    /**
     * @Form\Options({
     *     "label": "auth.reset-password.confirm-password",
     *     "short-label": "auth.reset-password.confirm-password"
     * })
     * @Form\Attributes({"id": "auth.reset-password.confirm-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Validator({"name": "Laminas\Validator\Identical", "options": {"token": "newPassword"}})
     * @Form\Type("Password")
     */
    public $confirmPassword = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.reset-password.button",
     *     "value": "auth.reset-password.button",
     *     "class": "govuk-button",
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
