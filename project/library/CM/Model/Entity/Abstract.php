<?php

abstract class CM_Model_Entity_Abstract extends CM_Model_Abstract implements CM_ArrayConvertible {

	/**
	 * @param boolean $absolute
	 * @return string
	 */
	abstract public function getLink($absolute = false);

	/**
	 * @param boolean $throwNonexistent OPTIONAL
	 * @return CM_Model_User
	 */
	public function getUser($throwNonexistent = true) {
		try {
			return CM_Model_User::factory($this->getUserId());
		} catch (CM_Exception_Nonexistent $ex) {
			if ($throwNonexistent) {
				throw $ex;
			}
			return null;
		}
	}

	/**
	 * @return int User-ID (owner, creator)
	 */
	public function getUserId() {
		return $this->_get('userId');
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

	public function toArray() {
		return array('id' => $this->getId(), 'type' => $this->getType());
	}

	public static function fromArray(array $array) {
		return new static($array['id']);
	}

	/**
	 * @param int $type
	 * @param int $id
	 * @return CM_Model_Entity_Abstract
	 */
	final public static function factory($type, $id) {
		$className = self::_getClassName($type);
		return new $className($id);
	}
}
