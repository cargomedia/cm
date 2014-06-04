<?php

class CM_Exception_Blocked extends CM_Exception {

    /**
     * @param CM_Model_User $user
     */
    public function __construct(CM_Model_User $user) {
        parent::__construct('Blocked', null, array(
            'messagePublic'          => '{$username} has blocked you.',
            'messagePublicVariables' => array('username' => $user->getDisplayName()),
        ));
    }
}
