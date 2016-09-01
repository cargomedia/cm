<?php

class CM_Mail_MailerFactory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param int|null $logLevel
     * @return CM_Mail_Mailer
     */
    public function createLogMailer($logLevel = null) {
        $transport = new CM_Mail_Transport_Log($this->getServiceManager()->getLogger(), $logLevel);
        return new CM_Mail_Mailer($transport);
    }

    /**
     * @param string|null $host
     * @param int|null    $port
     * @param string|null $security
     * @return CM_Mail_Mailer
     */
    public function createSmtpMailer($host = null, $port = null, $security = null) {
        $host = null === $host ? 'localhost' : (string) $host;
        $port = null === $port ? 25 : (int) $port;
        $security = null === $security ? (string) $security : $security;
        $transport = new Swift_SmtpTransport($host, $port, $security);
        return new CM_Mail_Mailer($transport);
    }
}
