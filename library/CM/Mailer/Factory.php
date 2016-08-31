<?php

class CM_Mailer_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string $transportServiceName
     * @return CM_Mailer_Client
     */
    public function createMailer($transportServiceName) {
        /** @var Swift_Transport $transport */
        $transport = $this->getServiceManager()->get((string) $transportServiceName, 'Swift_Transport');
        if ($transport instanceof CM_Service_ManagerAwareInterface) {
            $transport->setServiceManager($this->getServiceManager());
        }
        return new CM_Mailer_Client($transport);
    }
}
