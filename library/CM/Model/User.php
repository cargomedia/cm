<?php

class CM_Model_User extends CM_Model_Abstract {

	const TYPE = 13;
	const ONLINE_EXPIRATION = 1800;

	/**
	 * @return boolean
	 */
	public function canLogin() {
		return true;
	}

	/**
	 * @param int|null $actionType
	 * @param int|null $actionVerb
	 * @param int|null $period
	 * @return CM_Paging_Action_User
	 */
	public function getActions($actionType = null, $actionVerb = null, $period = null) {
		return new CM_Paging_Action_User($this, $actionType, $actionVerb, $period);
	}

	/**
	 * @return int
	 */
	public function getCreated() {
		return $this->_get('createStamp');
	}

	/**
	 * @return int[]
	 */
	public function getDefaultRoles() {
		return array();
	}

	/**
	 * @return string|null
	 */
	public function getEmail() {
		return null;
	}

	/**
	 * @return boolean
	 */
	public function getEmailVerified() {
		return true;
	}

	/**
	 * @return int
	 */
	public function getLatestactivity() {
		return (int) $this->_get('activityStamp');
	}

	/**
	 * @return boolean
	 */
	public function getOnline() {
		return (boolean) $this->_get('online');
	}

	/**
	 * @param boolean $state   OPTIONAL
	 * @param boolean $visible OPTIONAL
	 */
	public function setOnline($state = true, $visible = true) {
		$visible = (bool) $visible;
		if ($state) {
			CM_Db_Db::replace(TBL_CM_USER_ONLINE, array('userId' => $this->getId(), 'visible' => $visible));
		} else {
			CM_Db_Db::delete(TBL_CM_USER_ONLINE, array('userId' => $this->getId()));
		}
		$this->_change();
	}

	/**
	 * @return CM_ModelAsset_User_Preferences
	 */
	public function getPreferences() {
		return $this->_getAsset('CM_ModelAsset_User_Preferences');
	}

	/**
	 * @return CM_Site_Abstract
	 */
	public function getSite() {
		$siteType = $this->_get('site');
		return CM_Site_Abstract::factory($siteType);
	}

	/**
	 * @return CM_ModelAsset_User_Roles
	 */
	public function getRoles() {
		return $this->_getAsset('CM_ModelAsset_User_Roles');
	}

	/**
	 * @param int $actionType OPTIONAL
	 * @param int $actionVerb OPTIONAL
	 * @param int $limitType  OPTIONAL
	 * @param int $period     OPTIONAL
	 * @return CM_Paging_Transgression_User
	 */
	public function getTransgressions($actionType = null, $actionVerb = null, $limitType = null, $period = null) {
		return new CM_Paging_Transgression_User($this, $actionType, $actionVerb, $limitType, $period);
	}

	/**
	 * @return CM_Paging_StreamPublish_User
	 */
	public function getStreamPublishs() {
		return new CM_Paging_StreamPublish_User($this);
	}

	/**
	 * @return CM_Paging_StreamSubscribe_User
	 */
	public function getStreamSubscribes() {
		return new CM_Paging_StreamSubscribe_User($this);
	}

	/**
	 * @return CM_Paging_Useragent_User
	 */
	public function getUseragents() {
		return new CM_Paging_Useragent_User($this);
	}

	/**
	 * @return CM_Paging_StreamChannelArchiveVideo_User
	 */
	public function getStreamChannelArchiveVideoList() {
		return new CM_Paging_StreamChannelArchiveVideo_User($this);
	}

	/**
	 * @return boolean
	 */
	public function getVisible() {
		return (boolean) $this->_get('visible');
	}

	/**
	 * @param boolean $state
	 * @throws CM_Exception_Invalid
	 * @return CM_Model_User
	 */
	public function setVisible($state = true) {
		if (!$this->getOnline()) {
			throw new CM_Exception_Invalid('Must not modify visibility of a user that is offline');
		}
		CM_Db_Db::replace(TBL_CM_USER_ONLINE, array('userId' => $this->getId(), 'visible' => (int) $state));
		return $this->_change();
	}

	/**
	 * @return string
	 */
	public function getDisplayName() {
		return 'user' . $this->getId();
	}

	/**
	 * @return CM_Model_Language|null
	 */
	public function getLanguage() {
		if (!$this->_get('languageId')) {
			return null;
		}
		return new CM_Model_Language($this->_get('languageId'));
	}

	/**
	 * @param CM_Model_Language $language
	 */
	public function setLanguage(CM_Model_Language $language) {
		CM_Db_Db::update(TBL_CM_USER, array('languageId' => $language->getId()), array('userId' => $this->getId()));
		$this->_change();
	}

	/**
	 * @param CM_Site_Abstract $site
	 */
	public function setSite(CM_Site_Abstract $site) {
		CM_Db_Db::update(TBL_CM_USER, array('site' => $site->getType()), array('userId' => $this->getId()));
		$this->_change();
	}

	/**
	 * @return CM_Model_User
	 */
	public function updateLatestactivity() {
		CM_Db_Db::update(TBL_CM_USER, array('activityStamp' => time()), array('userId' => $this->getId()));
		return $this->_change();
	}

	protected function _loadAssets() {
		return array(new CM_ModelAsset_User_Preferences($this), new CM_ModelAsset_User_Roles($this));
	}

	protected function _loadData() {
		return CM_Db_Db::exec('
			SELECT `main`.*, `online`.`userId` AS `online`, `online`.`visible`
			FROM TBL_CM_USER AS `main`
			LEFT JOIN TBL_CM_USER_ONLINE AS `online` USING (`userId`)
			WHERE `main`.`userId`=?',
			array($this->getId()))->fetch();
	}

	/**
	 * @param int $id
	 * @return CM_Model_User
	 */
	public static function factory($id) {
		$className = self::_getClassName();
		return new $className($id);
	}

	public static function offlineOld() {
		$res = CM_Db_Db::exec('
			SELECT `o`.`userId`
			FROM TBL_CM_USER_ONLINE `o`
			LEFT JOIN TBL_CM_USER `u` USING(`userId`)
			WHERE `u`.`activityStamp` < ? OR `u`.`userId` IS NULL',
			array(time() - self::ONLINE_EXPIRATION));
		while ($userId = $res->fetchColumn()) {
			try {
				$user = CM_Model_User::factory($userId);
				$user->setOnline(false);
			} catch (CM_Exception_Nonexistent $e) {
				CM_Db_Db::delete(TBL_CM_USER_ONLINE, array('userId' => $userId));
			}
		}
	}

	/**
	 * @param array $data
	 * @return CM_Model_User
	 */
	protected static function _create(array $data) {
		$siteType = null;
		if (isset($data['site'])) {
			/** @var CM_Site_Abstract $site */
			$site = $data['site'];
			$siteType = $site->getType();
		}
		$languageId = null;
		if (isset($data['language'])) {
			/** @var CM_Model_Language $language */
			$language = $data['language'];
			$languageId = $language->getId();
		}
		$userId = CM_Db_Db::insert(TBL_CM_USER, array('createStamp' => time(), 'activityStamp' => time(), 'site' => $siteType,
													  'languageId'  => $languageId));
		return new static($userId);
	}

	protected function _onDelete() {
		$this->getTransgressions()->deleteAll();
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		foreach ($this->getStreamSubscribes() as $streamSubscribe) {
			$streamSubscribe->delete();
		}
		/** @var CM_Model_Stream_Publish $streamPublish */
		foreach ($this->getStreamPublishs() as $streamPublish) {
			$streamPublish->delete();
		}
		CM_Db_Db::delete(TBL_CM_USER_ONLINE, array('userId' => $this->getId()));
		CM_Db_Db::delete(TBL_CM_USER, array('userId' => $this->getId()));
	}

	public function toArray() {
		$array = parent::toArray();
		$array['displayName'] = $this->getDisplayName();
		$array['visible'] = $this->getVisible();
		return $array;
	}
}
