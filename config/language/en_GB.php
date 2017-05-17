<?php

return [
    'auth.login.title' => 'Sign in',
    'auth.login.failed.title' => 'There was a problem signing in',
    // auth.login.failed.reason with "!!" suffix are for openAM 12.02
    'auth.login.failed.reason.Authentication Failed!!' => 'Please check your username and password',
    'auth.login.failed.reason.Invalid Password!!' => 'Please check your username and password',
    // auth.login.failed.reason without "!!" suffix are for openAM 12.04
    'auth.login.failed.reason.Authentication Failed' => 'Please check your username and password',
    'auth.login.failed.reason.Invalid Password' => 'Please check your username and password',
    'auth.login.failed.reason. Your account is locked. Please contact service desk to unlock your account'
        => 'Your account is locked. Email <a href="mailto:operatorlicensing@otc.gsi.gov.uk">operatorlicensing@otc.gsi.gov.uk</a> to unlock it',
    'auth.login.username' => 'Username',
    'auth.login.password' => 'Password',
    'auth.login.username.audio' => 'Enter your username',
    'auth.login.password.audio' => 'Enter your password',
    'auth.login.button' => 'Sign in',
    'auth.login.termsAgreed' => 'By using the site you agree to the <a href="%s">terms and conditions</a>.',
    'auth.forgot-username.label' => 'Forgotten your username?',
    'auth.forgot-password.label' => 'Forgotten your password?',
    'auth.forgot-username.label.audio' => 'Retrieve your username',
    'auth.forgot-password.label.audio' => 'Retrieve your password',
    'auth.forgot-username.licence-number.label.audio' => 'Enter your licence number',
    'auth.forgot-username.email.label.audio' => 'Enter your email address',
    'auth.expired-password.title' => 'Change your password',
    'auth.expired-password.failed.title' => 'We couldn\'t update your password',
    'auth.expired-password.failed.reason.New password contains fewer than minimum number of characters.'
        => 'Your new password must be at least eight characters long',
    'auth.expired-password.failed.reason.The password and the confirm password do not match.'
        => 'New re-entered password does not match the new password',
    'auth.expired-password.failed.reason.The password you entered is invalid.'
        => 'The current password you entered is incorrect',
    'auth.expired-password.failed.reason.New password does not meet the password policy requirements.'
        => 'Your password should be at least 8 characters long. It must contain at least one lower-case letter, one capital letter and one number. Your new password can\'t be the same as your last 5 passwords.<br/><br/>Never share your password with anyone.',
    'auth.forgot-password.title' => 'Reset your password',
    'auth.forgot-password.username' => 'Enter your username',
    'auth.forgot-password.button' => 'Submit',
    'auth.forgot-password.failed.title' => 'Failed to reset your password',
    'auth.forgot-password.failed.reason.User not found' => 'Please check your username',
    'auth.forgot-password.failed.reason.unknown-error' => 'An error occurred, please try again',
    'auth.forgot-password.failed.reason.Failed to send mail' => 'An error occurred, when try to send mail',
    'auth.confirm-forgot-password.title' => 'Check your email',
    'auth.forgot-password.email.subject' => 'Reset your password',
    'auth.forgot-password.email.message' => 'Please follow the link below to reset your password',
    'auth.reset-password.title' => 'Change your password',
    'auth.reset-password.new-password' => 'New password',
    'auth.reset-password.confirm-password' => 'Re-enter new password',
    'auth.reset-password.button' => 'Continue',
    'auth.forgot-password.failed.reason.account-not-active' => '<p>It looks like your account isn\'t active.</p><p>You need to activate your account before you can change your password.</p><p>If you\'ve lost your original login details please contact us for a new letter/email.</p>',
    'auth.forgot-password-expired' => 'The forgot password link has expired, please try again',
    'auth.reset-password.success' => 'Your password was reset successfully',
    'auth.reset-password.failed.title' => 'We couldn\'t update your password',
    'auth.reset-password.failed.reason.Minimum password length is 8.'
        => 'Your new password must be at least eight characters long',
    'auth.reset-password.failed.reason.Plug-in org.forgerock.openam.idrepo.ldap.DJLDAPv3Repo encountered an ldap exception 19: The provided new password was found in the password history for the user'
        => 'You\'ve used this password before. Please choose a new one.',
    'auth.expired-password.failed.reason.The password must be different. Try again.'
        => 'Your new password can\'t be the same as your old password',
    'auth.change-password.title' => 'Change your password',
    'auth.change-password.subtitle'
        => 'Your password should be at least 8 characters long. It must contain at least one lower-case letter, one capital letter and one number. Your new password can\'t be the same as your last 5 passwords.<br/><br/>Never share your password with anyone.',
    'auth.change-password.old-password' => 'Current password',
    'auth.change-password.new-password' => 'New password',
    'auth.change-password.confirm-password' => 'Re-enter new password',
    'auth.change-password.button' => 'Save',
    'auth.change-password.success' => 'Your password updated successfully',
    'auth.change-password.failed.title' => 'We couldn\'t update your password',
    'auth.change-password.failed.reason.An error occurred while trying to change the password'
        => 'Your current password may be incorrect or your new password may not follow the rules.',
    'auth.change-password.failed.reason.The password must be different. Try again.'
        => 'Your new password can\'t be the same as your old password',
    'auth.change-password.failed.reason.The password you entered is invalid.'
        => 'The current password you entered is incorrect',
];
