<?php

interface CM_StreamChannel_DisallowInterface {

    /**
     * @param CM_Model_User|null $user
     * @param int                $allowedUntil
     * @return int
     */
    public function canPublish(CM_Model_User $user = null, $allowedUntil);

    /**
     * @param CM_Model_User|null $user
     * @param int                $allowedUntil
     * @return int
     */
    public function canSubscribe(CM_Model_User $user = null, $allowedUntil);

    /**
     * @return boolean
     */
    public function isValid();
}
