<?php

interface CM_StreamChannel_DisallowInterface {

    /**
     * @param CM_Model_User|null $user
     * @param int                $allowedUntil
     * @return int
     */
    function canPublish(CM_Model_User $user = null, $allowedUntil);

    /**
     * @param CM_Model_User|null $user
     * @param int                $allowedUntil
     * @return int
     */
    function canSubscribe(CM_Model_User $user = null, $allowedUntil);

    /**
     * @return boolean
     */
    function isValid();
}
