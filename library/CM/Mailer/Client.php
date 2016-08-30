<?php

class CM_Mailer_Client extends CM_Class_Abstract {

    /** @var Swift_Transport */
    private $_transport;

    /**
     * @param Swift_Transport $transport
     */
    public function __construct(Swift_Transport $transport) {
        $this->_transport = $transport;
    }

    /**
     * @return Swift_Transport
     */
    public function getTransport() {
        return $this->_transport;
    }
}
