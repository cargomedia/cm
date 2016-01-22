<?php

interface CM_StreamChannel_DisallowInterface {

    /**
     * @param CM_Model_User $user
     * @param int           $allowedUntil
     * @return int
     */
    function canPublish(CM_Model_User $user, $allowedUntil);

    /**
     * @param CM_Model_User $user
     * @param int           $allowedUntil
     * @return int
     */
    function canSubscribe(CM_Model_User $user, $allowedUntil);

    /**
     * @return boolean
     */
    function isValid();
}
