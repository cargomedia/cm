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
     * @param array|null  $headers
     * @param string|null $username
     * @param string|null $password
     * @param string|null $security
     * @return CM_Mail_Mailer
     */
    public function createSmtpMailer($host = null, $port = null, array $headers = null, $username = null, $password = null, $security = null) {
        $host = null !== $host ? (string) $host : 'localhost';
        $port = null !== $port ? (int) $port : 25;
        $headers = null !== $headers ? (array) $headers : [];
        $security = null !== $security ? (string) $security : null;

        $transport = new Swift_SmtpTransport($host, $port, $security);
        if (null !== $username) {
            $transport->setUsername((string) $username);
        }
        if (null !== $password) {
            $transport->setPassword((string) $password);
        }
        return new CM_Mail_Mailer($transport, $headers);
    }

    /**
     * @param string|null $extraParams
     * @return CM_Mail_Mailer
     */
    public function createMailMailer($extraParams = null) {
        $extraParams = null !== $extraParams ? (string) $extraParams : '-f%s';
        $transport = new Swift_MailTransport($extraParams);
        return new CM_Mail_Mailer($transport);
    }
}
