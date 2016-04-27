<?php

class CM_Log_Context_App {

    /** @var Closure */
    protected $_getUserClosure;

    /** @var array */
    private $_extra;

    /** @var Exception|null */
    private $_exception;

    /** @var CM_ExceptionHandling_SerializableException|null */
    private $_serializableException;

    /**
     * @param array              $extra
     * @param CM_Model_User|null $user
     * @param Exception|null     $exception
     */
    public function __construct(array $extra = null, CM_Model_User $user = null, Exception $exception = null) {
        $this->_extra = (array) $extra;
        if (null !== $user) {
            $this->setUser($user);
        }
        if (null !== $exception) {
            $this->_exception = $exception;
            $this->_serializableException = new CM_ExceptionHandling_SerializableException($exception);
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
     * @param Exception $exception
     */
    public function setException(Exception $exception) {
        $this->_exception = $exception;
        $this->_serializableException = new CM_ExceptionHandling_SerializableException($exception);
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
     * @return boolean
     */
    public function hasException() {
        return null !== $this->_exception;
    }

    /**
     * @return Exception
     * @throws CM_Exception_Invalid
     */
    public function getException() {
        if (!$this->hasException()) {
            throw new CM_Exception_Invalid('Exception is not set');
        }
        return $this->_exception;
    }

    /**
     * @return CM_ExceptionHandling_SerializableException
     * @throws CM_Exception_Invalid
     */
    public function getSerializableException() {
        if (!$this->hasException()) {
            throw new CM_Exception_Invalid('Exception is not set');
        }
        return $this->_serializableException;
    }

    /**
     * @param CM_Log_Context_App $appContext
     */
    public function merge(CM_Log_Context_App $appContext) {
        if ($appContext->_getUserClosure) {
            $this->setUserWithClosure($appContext->_getUserClosure);
        }
        if ($appContext->hasException()) {
            $this->_exception = $appContext->_exception;
            $this->_serializableException = $appContext->_serializableException;
        }
        $this->_extra = array_merge($this->getExtra(), $appContext->getExtra());
    }

    /**
     * @param Exception $exception
     * @return int
     */
    public static function exceptionSeverityToLevel(Exception $exception) {
        $severity = $exception instanceof CM_Exception ? $exception->getSeverity() : null;
        $map = [
            CM_Exception::WARN  => CM_Log_Logger::WARNING,
            CM_Exception::ERROR => CM_Log_Logger::ERROR,
            CM_Exception::FATAL => CM_Log_Logger::CRITICAL,
        ];
        return isset($map[$severity]) ? $map[$severity] : CM_Log_Logger::ERROR;
    }
}
