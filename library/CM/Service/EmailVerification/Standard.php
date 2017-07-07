<?php

class CM_Service_EmailVerification_Standard implements CM_Service_EmailVerification_ClientInterface {

    public function isValid($email) {
        $email = (string) $email;
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $pos = strpos($email, '@');
        if (false === $pos) {
            return false;
        }
        $domain = substr($email, $pos + 1);
        if (!$this->_checkMXRecords($domain)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $domain
     * @return bool
     */
    private function _checkMXRecords($domain) {
        $networkTools = CM_Service_Manager::getInstance()->get('network-tools', CM_Service_NetworkTools::class);
        return $networkTools->getMXRecords($domain);
    }
}
