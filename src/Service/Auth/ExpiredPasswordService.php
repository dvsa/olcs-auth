<?php

/**
 * Expired Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

/**
 * Expired Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ExpiredPasswordService extends AbstractRestService
{
    /**
     * Update password
     *
     * @param $oldPassword
     * @param $newPassword
     * @param $confirmPassword
     */
    public function updatePassword($authId, $oldPassword, $newPassword, $confirmPassword)
    {
        $data = $this->buildRequestData($authId, $oldPassword, $newPassword, $confirmPassword);

        $response = $this->post('json/authenticate', $data);

        return $this->decodeContent($response);
    }

    /**
     * Build request data
     *
     * @param $authId
     * @param $oldPassword
     * @param $newPassword
     * @param $confirmPassword
     * @return array
     */
    private function buildRequestData($authId, $oldPassword, $newPassword, $confirmPassword, $hashOld = false)
    {
        return [
            'authId' => $authId,
            'stage' => 'LDAP2',
            'callbacks' => [
                [
                    'type' => 'PasswordCallback',
                    'output' => [
                        [
                            'name' => 'prompt',
                            'value' => 'Old Password'
                        ]
                    ],
                    'input' => [
                        [
                            'name' => 'IDToken1',
                            'value' => $hashOld ? HashService::hashPassword($oldPassword) : $oldPassword
                        ]
                    ]
                ],
                [
                    'type' => 'PasswordCallback',
                    'output' => [
                        [
                            'name' => 'prompt',
                            'value' => 'New Password'
                        ]
                    ],
                    'input' => [
                        [
                            'name' => 'IDToken2',
                            'value' => HashService::hashPassword($newPassword)
                        ]
                    ]
                ],
                [
                    'type' => 'PasswordCallback',
                    'output' => [
                        [
                            'name' => 'prompt',
                            'value' => 'Confirm Password'
                        ]
                    ],
                    'input' => [
                        [
                            'name' => 'IDToken3',
                            'value' => HashService::hashPassword($confirmPassword)
                        ]
                    ]
                ],
                [
                    'type' => 'ConfirmationCallback',
                    'output' => [
                        [
                            'name' => 'prompt',
                            'value' => ''
                        ],
                        [
                            'name' => 'messageType',
                            'value' => 0
                        ],
                        [
                            'name' => 'options',
                            'value' => [
                                'Submit',
                                'Cancel'
                            ]
                        ],
                        [
                            'name' => 'optionType',
                            'value' => -1
                        ],
                        [
                            'name' => 'defaultOption',
                            'value' => 0
                        ]
                    ],
                    'input' => [
                        [
                            'name' => 'IDToken4',
                            'value' => 0
                        ]
                    ]
                ]
            ]
        ];
    }
}
