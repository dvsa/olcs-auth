<?php

namespace Dvsa\Olcs\Auth\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("expired-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ExpiredPasswordForm
{
    /**
     * @Form\Options({"label": "auth.expired-password.old-password"})
     * @Form\Attributes({"id": "auth.expired-password.old-password"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $oldPassword = null;

    /**
     * @Form\Options({"label": "auth.expired-password.new-password"})
     * @Form\Attributes({"id": "auth.expired-password.new-password"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $newPassword = null;

    /**
     * @Form\Options({"label": "auth.expired-password.confirm-password"})
     * @Form\Attributes({"id": "auth.expired-password.confirm-password"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $confirmPassword = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.expired-password.button",
     *     "value": "auth.expired-password.button",
     *     "class": "action--primary large"
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
