<?php

abstract class CM_Model_Entity_Abstract extends CM_Model_Abstract {

	/**
	 * @return string|null
	 */
	abstract public function getPath();

	/**
	 * @param boolean|null $ignoreNonexistent
	 * @return CM_Model_User|null
	 * @throws CM_Exception_Nonexistent
	 */
	public function getUser($ignoreNonexistent = null) {
		try {
			return CM_Model_User::factory($this->getUserId());
		} catch (CM_Exception_Nonexistent $ex) {
			if ($ignoreNonexistent) {
				return null;
			}
			throw $ex;
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
	 */
	final public static function factory($type, $id) {
		$className = self::_getClassName($type);
		return new $className($id);
	}

	public function toArray() {
		$array = parent::toArray();
		$array['path'] = $this->getPath();
		return $array;
	}
}
