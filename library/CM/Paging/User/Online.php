<?php

class CM_Paging_User_Online extends CM_Paging_User_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('userId', 'cm_user_online');

        parent::__construct($source);
    }

    /**
     * @param int|CM_Model_User $user
     * @return bool
     */
    public function contains($user) {
        if ($user instanceof CM_Model_User) {
            $userId = $user->getId();
        } else {
            $userId = (int) $user;
        }
        return in_array($userId, $this->getItemsRaw());
    }
}
