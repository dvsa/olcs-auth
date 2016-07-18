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
     * @Form\Options({
     *     "label": "auth.forgot-password.username",
     *     "short-label": "auth.forgot-password.username"
     * })
     * @Form\Attributes({"id": "auth.forgot-password.username"})
     * @Form\Filter({"name": "Zend\Filter\StringTrim"})
     * @Form\Filter({"name": "Common\Filter\StripSpaces"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     * @Form\Type("Text")
     */
    public $username = null;

    /**
     * @Form\Attributes({
     *     "id": "auth.forgot-password.button",
     *     "value": "auth.forgot-password.button",
     *     "class": "action--primary large"
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"id":"cancel","type":"submit","class":"action--secondary large"})
     * @Form\Options({
     *     "label": "cancel.button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
