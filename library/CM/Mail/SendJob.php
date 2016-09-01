<?php

class CM_Mail_SendJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        CM_Service_Manager::getInstance()->getMailer()->send($params->getMailerMessage('message'));
        if ($params->has('recipient') && $params->has('mailType')) {
            $recipient = $params->getUser('recipient');
            $mailType = $params->getInt('mailType');
            $action = new CM_Action_Email(CM_Action_Abstract::SEND, $recipient, $mailType);
            $action->prepare($recipient);
            $action->notify($recipient);
        }
    }
}
