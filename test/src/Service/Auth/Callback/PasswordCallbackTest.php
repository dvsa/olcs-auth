<?php

/**
 * Password Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth\Callback;

use Dvsa\Olcs\Auth\Service\Auth\Callback\PasswordCallback;

/**
 * Password Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PasswordCallbackTest extends \PHPUnit_Framework_TestCase
{
    public function testCallback()
    {
        $sut = new PasswordCallback('UserPassword', 'ID1', 'test', false);
        $result = $sut->toArray();
        $expected = [
            'type' => 'PasswordCallback',
            'output' => [['name' => 'prompt', 'value' => 'UserPassword']],
            'input' => [
                [
                    'Password' => 'ID1',
                    'value' => 'test'
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCallbackHashed()
    {
        $sut = new PasswordCallback('UserPassword', 'ID1', 'test');
        $result = $sut->toArray();
        $expected = [
            'type' => 'PasswordCallback',
            'output' => [['name' => 'prompt', 'value' => 'UserPassword']],
            'input' => [
                [
                    'Password' => 'ID1',
                    'value' => sha1('test')
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
