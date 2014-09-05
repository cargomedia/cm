<?php

class CM_Action_Email extends CM_Action_Abstract {

    /** @var string */
    protected $_nameEmail;

    /**
     * @param string            $verbName
     * @param CM_Model_User|int $actor
     * @param int               $typeEmail
     */
    public function __construct($verbName, $actor, $typeEmail) {
        parent::__construct($verbName, $actor);
        $typeEmail = (int) $typeEmail;
        $className = CM_Mail::_getClassName($typeEmail);
        $this->_nameEmail = ucwords(CM_Util::uncamelize(str_replace('_', '', preg_replace('#\\A[^_]++_[^_]++_#', '', $className)), ' '));
    }

    public function getLabel() {
        return parent::getLabel() . ' ' . $this->_nameEmail;
    }

    /**
     * @param CM_Model_User $user
     */
    public function notify(CM_Model_User $user) {
        $this->_notify($user);
    }

    protected function _prepare() {
    }
}
