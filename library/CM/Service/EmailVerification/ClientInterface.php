<?php

interface CM_Service_EmailVerification_ClientInterface {

    /**
     * @param string $email
     *
     * @return bool
     */
    public function isValid($email);
}
