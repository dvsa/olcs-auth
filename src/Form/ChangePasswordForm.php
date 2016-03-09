<?php

namespace Dvsa\Olcs\Auth\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("change-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ChangePasswordForm
{
    /**
     * @Form\Options({
     *     "label": "auth.change-password.old-password",
     *     "short-label": "auth.change-password.old-password"
     * })
     * @Form\Attributes({"id": "auth.change-password.old-password"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $oldPassword = null;

    /**
     * @Form\Options({
     *     "label": "auth.change-password.new-password",
     *     "short-label": "auth.change-password.new-password"
     * })
     * @Form\Attributes({"id": "auth.change-password.new-password"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":8, "max":160}})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\PasswordConfirm","options":{"token":"confirmPassword"}})
     * @Form\Type("Password")
     */
    public $newPassword = null;

    /**
     * @Form\Options({
     *     "label": "auth.change-password.confirm-password",
     *     "short-label": "auth.change-password.confirm-password"
     * })
     * @Form\Attributes({"id": "auth.change-password.confirm-password"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $confirmPassword = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.change-password.button",
     *     "value": "auth.change-password.button",
     *     "class": "action--primary large"
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
