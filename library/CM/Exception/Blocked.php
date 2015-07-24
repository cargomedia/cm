<?php

class CM_Exception_Blocked extends CM_Exception {

    /**
     * @param CM_Model_User $user
     */
    public function __construct(CM_Model_User $user) {
        parent::__construct('Blocked', null, null, new CM_I18n_Phrase('{$username} has blocked you.', ['username' => $user->getDisplayName()]));
    }
}
