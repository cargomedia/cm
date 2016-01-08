<?php

class CM_User_OfflineJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $user = $params->getUser('user');
        if (null === CM_Model_StreamChannel_Message_User::findByUser($user)) {
            $user->setOnline(false);
        }
    }
}
