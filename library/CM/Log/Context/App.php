<?php

class CM_Log_Context_App {

    /** @var Closure */
    protected $_getUserClosure;

    /** @var array */
    private $_extra;

    /**
     * @param array              $extra
     * @param CM_Model_User|null $user
     */
    public function __construct(array $extra = null, CM_Model_User $user = null) {
        $this->_extra = (array) $extra;
        if (null !== $user) {
            $this->setUser($user);
        }
    }

    /**
     * @param Closure $getUser
     */
    public function setUserWithClosure(Closure $getUser) {
        $this->_getUserClosure = $getUser;
    }

    /**
     * @param CM_Model_User $user
     */
    public function setUser(CM_Model_User $user) {
        $this->setUserWithClosure(function () use ($user) {
            return $user;
        });
    }

    /**
     * @return CM_Model_User|null
     * @throws CM_Exception_Invalid
     */
    public function getUser() {
        if (null === $this->_getUserClosure) {
            return null;
        }
        $user = call_user_func($this->_getUserClosure);
        if ($user === null || $user instanceof CM_Model_User) {
            return $user;
        }
        throw new CM_Exception_Invalid('User need to be CM_Model_User or null');
    }

    /**
     * @return array
     */
    public function getExtra() {
        return $this->_extra;
    }

    /**
     * @param CM_Log_Context_App $appContext
     */
    public function merge(CM_Log_Context_App $appContext) {
        if ($appContext->_getUserClosure) {
            $this->setUserWithClosure($appContext->_getUserClosure);
        }
        $this->_extra = array_merge($this->getExtra(), $appContext->getExtra());
    }

}
