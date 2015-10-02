<?php

class CM_Log_Context_ComputerInfo {

    /** @var string */
    private $_fqdn;

    /** @var string */
    private $_phpVersion;

    /**
     * @param string $fqdn
     * @param string $phpVersion
     */
    public function __construct($fqdn, $phpVersion) {
        $this->_fqdn = (string) $fqdn;
        $this->_phpVersion = (string) $phpVersion;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedDomainName() {
        return $this->_fqdn;
    }

    /**
     * @return string
     */
    public function getPhpVersion() {
        return $this->_phpVersion;
    }
}
