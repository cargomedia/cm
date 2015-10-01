<?php

class CM_Log_Context_ComputerInfo {

    /** @var string */
    private $_fqdn;

    /** @var string */
    private $_phpVersion;

    public function __construct() {
        $this->_fqdn = php_uname('a');
        $this->_phpVersion = phpversion();
    }

    /**
     * @return string
     */
    public function getFullQualifiedDomainName() {
        return $this->_fqdn;
    }

    /**
     * @return string
     */
    public function getPhpVersion() {
        return $this->_phpVersion;
    }
}
