<?php

class CM_Service_EmailVerification_Standard implements CM_Service_EmailVerification_ClientInterface {

    public function isValid($email) {
        $email = (string) $email;
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}
