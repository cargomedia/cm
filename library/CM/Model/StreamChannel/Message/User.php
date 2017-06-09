<?php

class CM_Model_StreamChannel_Message_User extends CM_Model_StreamChannel_Message {

    const SALT = 'd98*2jflq74fcr8gfoqwm&dsowrds93l';

    public function onPublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
        if ($this->hasUser()) {
            $user = $streamSubscribe->getUser();
            if ($user && !$user->getOnline()) {
                $user->setOnline(true);
            }
        }
    }

    public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
        if ($this->hasUser()) {
            $user = $streamSubscribe->getUser();
            if ($user && !$this->isSubscriber($user, $streamSubscribe)) {
                $delayedJobQueue = CM_Service_Manager::getInstance()->getDelayedJobQueue();
                $delayedJobQueue->addJob(new CM_User_OfflineJob(CM_Params::factory(['user' => $user], false)), CM_Model_User::OFFLINE_DELAY);
            }
        }
    }

    /**
     * @param CM_Model_User $user
     * @return string
     */
    public static function getKeyByUser(CM_Model_User $user) {
        return self::_encryptKey($user->getId(), self::SALT);
    }

    /**
     * @param CM_Model_User $user
     * @return CM_Model_StreamChannel_Abstract|null
     */
    public static function findByUser(CM_Model_User $user) {
        return self::findByKey(self::getKeyByUser($user));
    }

    /**
     * @return CM_Model_User
     */
    public function getUser() {
        $userId = self::_decryptKey($this->getKey(), self::SALT);
        return CM_Model_User::factory($userId);
    }

    /**
     * @return bool
     */
    public function hasUser() {
        try {
            $this->getUser();
            return true;
        } catch (CM_Exception_Nonexistent $e) {
            return false;
        }
    }

    /**
     * @param CM_Model_User $user
     * @param string        $event
     * @param mixed|null    $data
     */
    public static function publish($user, $event, $data = null) {
        if (!$user->getOnline()) {
            return;
        }
        $streamChannel = self::getKeyByUser($user);
        parent::publish($streamChannel, $event, $data);
    }

    /**
     * @param CM_Model_User      $user
     * @param CM_Action_Abstract $action
     * @param CM_Model_Abstract  $model
     * @param mixed|null         $data
     */
    public static function publishAction($user, CM_Action_Abstract $action, CM_Model_Abstract $model, $data = null) {
        if (!$user->getOnline()) {
            return;
        }
        $streamChannel = self::getKeyByUser($user);
        parent::publishAction($streamChannel, $action, $model, $data);
    }
}
