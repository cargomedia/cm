<?php

abstract class CM_Model_Entity_Abstract extends CM_Model_Abstract {

    /**
     * @return string|null
     */
    abstract public function getPath();

    /**
     * @return CM_Model_User
     */
    public function getUser() {
        return CM_Model_User::factory($this->getUserId());
    }

    /**
     * @return CM_Model_User|null
     */
    public function getUserIfExists() {
        try {
            return $this->getUser();
        } catch (CM_Exception_Nonexistent $e) {
            return null;
        }
    }

    /**
     * @return int User-ID (owner, creator)
     */
    public function getUserId() {
        return (int) $this->_get('userId');
    }

    /**
     * Checks if a given user is the entity owner
     *
     * @param CM_Model_User $user OPTIONAL
     * @return bool
     */
    final public function isOwner(CM_Model_User $user = null) {
        try {
            return $this->getUser()->equals($user);
        } catch (CM_Exception_Nonexistent $ex) {
            return false;
        }
    }

    /**
     * @param int $type
     * @param int $id
     * @return CM_Model_Entity_Abstract
     * @throws CM_Entity_InvalidTypeException
     */
    final public static function factory($type, $id) {
        $className = self::_getClassName($type);
        if (!is_subclass_of($className, static::class)) {
            throw new CM_Entity_InvalidTypeException('Invalid type provided', null, ['type' => $type, 'typeClass' => $className, 'calledClass' => static::class]);
        }
        return new $className($id);
    }

    public function jsonSerialize() {
        $array = parent::jsonSerialize();
        $array['path'] = $this->getPath();
        return $array;
    }
}
