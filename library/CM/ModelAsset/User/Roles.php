<?php

class CM_ModelAsset_User_Roles extends CM_ModelAsset_User_Abstract {

	public function _loadAsset() {
		$this->_getAll();
	}

	public function _onModelDelete() {
		CM_Mysql::delete(TBL_ROLE, array('userId' => $this->_model->getId()));
	}

	/**
	 * @param int $role
	 * @return int|null
	 */
	public function getStartStamp($role) {
		return $this->_get($role, 'startStamp');
	}

	/**
	 * @param int $role
	 * @return int|null
	 */
	public function getExpirationStamp($role) {
		return $this->_get($role, 'expirationStamp');
	}

	/**
	 * @param int $role
	 * @return boolean
	 */
	public function contains($role) {
		return array_key_exists($role, $this->_getAll());
	}

	/**
	 * @return array
	 */
	public function get() {
		return array_keys($this->_getAll());
	}

	/**
	 * @param int $role
	 * @param int $duration OPTIONAL
	 */
	public function add($role, $duration = null) {
		self::deleteOld();
		if ($duration) {
			CM_Mysql::exec("INSERT INTO TBL_ROLE VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE `expirationStamp` = `expirationStamp` + ?",
					$this->_model->getId(), $role, time(), time() + $duration, $duration);
		} else {
			CM_Mysql::exec(
					"INSERT INTO TBL_ROLE (`userId`, `role`, `startStamp`) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE `expirationStamp` = NULL",
					$this->_model->getId(), $role, time());
		}
		$this->_change();
	}

	/**
	 * @param int $role
	 */
	public function delete($role) {
		CM_Mysql::delete(TBL_ROLE, array('userId' => $this->_model->getId(), 'role' => $role));
		$this->_change();
	}

	/**
	 * @return array
	 */
	public function getDefault() {
		return $this->_model->getDefaultRoles();
	}

	/**
	 * @param int $role
	 * @param string $key
	 * @return mixed|null
	 * @throws CM_Exception_Invalid
	 */
	private function _get($role, $key) {
		if (!$this->contains($role)) {
			throw new CM_Exception_Invalid('User `' . $this->_model->getId() . '` does not have the role `' . $role . '`');
		}
		$values = $this->_getAll();
		if (!isset($values[$role][$key])) {
			return null;
		}
		return $values[$role][$key];
	}

	private function _getAll() {
		if (($values = $this->_cacheGet('roles')) === false) {
			$values = CM_Mysql::select(TBL_ROLE, array('role', 'startStamp', 'expirationStamp'),
					'`userId`=' . $this->_model->getId() . ' AND (`expirationStamp` > ' . time() . ' OR `expirationStamp` IS NULL)')
					->fetchAllTree();
			$this->_cacheSet('roles', $values);
		}
		foreach ($this->getDefault() as $role) {
			$values[$role] = array(null, null);
		}
		return $values;
	}

	/**
	 * @param CM_Model_User $user OPTIONAL
	 */
	public static function deleteOld(CM_Model_User $user = null) {
		$userWhere = $user ? ' AND `userId` = ' . (int) $user->getId() : '';
		$result = CM_Mysql::exec("SELECT `userId`, `role` FROM TBL_ROLE WHERE `expirationStamp` < ?" . $userWhere, time());
		while ($row = $result->fetchAssoc()) {
			$user = CM_Model_User::factory($row['userId']);
			$user->getRoles()->delete($row['role']);
			$msg = new CM_Mail($user, 'membership_expired');
			try {
				$msg->setTplParam('membershipType', CM_Language::section('internals.role')->text('role_' . $row['role']));
			} catch (CM_TreeException $ex) {
			}
			$msg->send();
		}
	}
}
