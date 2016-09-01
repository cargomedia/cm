<?php

class CM_Mail_MailerFactory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string $transportServiceName
     * @return CM_Mail_Mailer
     */
    public function create($transportServiceName) {
        /** @var Swift_Transport $transport */
        $transport = $this->getServiceManager()->get((string) $transportServiceName, 'Swift_Transport');
        return new CM_Mail_Mailer($transport);
    }
}
