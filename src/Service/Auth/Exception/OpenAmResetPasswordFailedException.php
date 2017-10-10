<?php


namespace Dvsa\Olcs\Auth\Service\Auth\Exception;


use Exception;
use Throwable;

/**
 * OpenAM Reset Password Failed
 */
class OpenAmResetPasswordFailedException extends Exception
{
    /** @var string */
    private $openAmErrorMessage;

    /**
     * Constructor
     *
     * @param string         $openAmMessage OpenAM Error message suitable for public display
     * @param string         $message       [optional] Internal error message
     * @param int            $code          [optional] Public message
     * @param Throwable|null $previous      [optional] Previous Exception
     */
    public function __construct($openAmMessage, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->openAmErrorMessage = $openAmMessage;
    }

    /**
     * Get Open AM Error Message
     *
     * @return string the Open AM Error Message
     */
    public function getOpenAmErrorMessage()
    {
        return $this->openAmErrorMessage;
    }
}
