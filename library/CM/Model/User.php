<?php

class CM_Model_User extends CM_Model_Abstract {

    const ONLINE_EXPIRATION = 1800;
    const OFFLINE_DELAY = 5;
    const ACTIVITY_EXPIRATION = 60;

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
     * @param int|null $upperBound
     * @return CM_Paging_Action_User
     */
    public function getActions($actionType = null, $actionVerb = null, $period = null, $upperBound = null) {
        return new CM_Paging_Action_User($this, $actionType, $actionVerb, $period, $upperBound);
    }

    /**
     * @return int
     */
    public function getCreated() {
        return (int) $this->_get('createStamp');
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
     * @return int|null
     */
    public function getLatestActivity() {
        $activityStamp = $this->_get('activityStamp');
        return null !== $activityStamp ? (int) $activityStamp : null;
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
            CM_Db_Db::replace('cm_user_online', array('userId' => $this->getId(), 'visible' => $visible));
            $this->_set(array('online' => $this->getId(), 'visible' => $visible));
        } else {
            CM_Db_Db::delete('cm_user_online', array('userId' => $this->getId()));
            $this->_set(array('online' => null, 'visible' => null));
        }
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
        $siteFactory = new CM_Site_SiteFactory();
        $siteType = $this->_get('site');
        if (null === $siteType) {
            return $siteFactory->getDefaultSite();
        }
        return $siteFactory->getSiteByType($siteType);
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
        $state = (int) $state;
        if (!$this->getOnline()) {
            throw new CM_Exception_Invalid('Must not modify visibility of a user that is offline');
        }
        CM_Db_Db::replace('cm_user_online', array('userId' => $this->getId(), 'visible' => $state));
        $this->_set(array('online' => $this->getId(), 'visible' => $state));
        return $this;
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
        CM_Db_Db::update('cm_user', array('languageId' => $language->getId()), array('userId' => $this->getId()));
        $this->_change();
    }

    /**
     * @param CM_Site_Abstract $site
     */
    public function setSite(CM_Site_Abstract $site) {
        CM_Db_Db::update('cm_user', array('site' => $site->getType()), array('userId' => $this->getId()));
        $this->_change();
    }

    /**
     * @return CM_Model_Currency|null
     */
    public function getCurrency() {
        if (!$this->_get('currencyId')) {
            return null;
        }
        return new CM_Model_Currency($this->_get('currencyId'));
    }

    /**
     * @param CM_Model_Currency $currency
     */
    public function setCurrency(CM_Model_Currency $currency) {
        CM_Db_Db::update('cm_user', array('currencyId' => $currency->getId()), array('userId' => $this->getId()));
        $this->_change();
    }

    /**
     * @return CM_Frontend_Environment
     */
    public function getEnvironment() {
        $language = $this->getLanguage();
        if (!$language) {
            $language = CM_Model_Language::findDefault();
        }
        return new CM_Frontend_Environment($this->getSite(), $this, $language, null, null, null, $this->getCurrency());
    }

    public function updateLatestActivityThrottled() {
        $activityStamp = $this->getLatestActivity();
        if (null === $activityStamp || $activityStamp < time() - self::ACTIVITY_EXPIRATION) {
            $this->_updateLatestActivity();
        }
    }

    protected function _updateLatestActivity() {
        $currentTime = time();
        CM_Db_Db::update('cm_user', array('activityStamp' => $currentTime), array('userId' => $this->getId()));
        $this->_set('activityStamp', $currentTime);
    }

    protected function _getAssets() {
        return array(new CM_ModelAsset_User_Preferences($this), new CM_ModelAsset_User_Roles($this));
    }

    protected function _loadData() {
        return CM_Db_Db::exec('
			SELECT `main`.*, `online`.`userId` AS `online`, `online`.`visible`
			FROM `cm_user` AS `main`
			LEFT JOIN `cm_user_online` AS `online` USING (`userId`)
			WHERE `main`.`userId`=?',
            array($this->getId()))->fetch();
    }

    /**
     * @param int $id
     * @return CM_Model_User|null
     */
    public static function findById($id) {
        try {
            return new static((int) $id);
        } catch (CM_Exception_Nonexistent $e) {
            return null;
        }
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
			FROM `cm_user_online` `o`
			LEFT JOIN `cm_user` `u` USING(`userId`)
			WHERE `u`.`activityStamp` IS NOT NULL AND `u`.`activityStamp` < ? OR `u`.`userId` IS NULL',
            array(time() - self::ONLINE_EXPIRATION));
        while ($userId = $res->fetchColumn()) {
            try {
                $user = CM_Model_User::factory($userId);
                $user->setOnline(false);
            } catch (CM_Exception_Nonexistent $e) {
                CM_Db_Db::delete('cm_user_online', array('userId' => $userId));
            }
        }
    }

    /**
     * @param array $data
     * @return CM_Model_User
     */
    protected static function _createStatic(array $data) {
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
        $currencyId = null;
        if (isset($data['currency'])) {
            /** @var CM_Model_Currency $currency */
            $currency = $data['currency'];
            $currencyId = $currency->getId();
        }
        $userId = CM_Db_Db::insert('cm_user', array(
            'createStamp' => time(),
            'site'        => $siteType,
            'languageId'  => $languageId,
            'currencyId'  => $currencyId,
        ));
        return new static($userId);
    }

    protected function _onDeleteBefore() {
        $this->getTransgressions()->deleteAll();
        /** @var CM_Model_Stream_Subscribe $streamSubscribe */
        foreach ($this->getStreamSubscribes() as $streamSubscribe) {
            $streamSubscribe->unsetUser();
        }
        /** @var CM_Model_Stream_Publish $streamPublish */
        foreach ($this->getStreamPublishs() as $streamPublish) {
            $streamPublish->unsetUser();
        }
        CM_Db_Db::delete('cm_user_online', array('userId' => $this->getId()));
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_user', array('userId' => $this->getId()));
    }

    public function jsonSerialize() {
        $array = parent::jsonSerialize();
        $array['displayName'] = $this->getDisplayName();
        $array['visible'] = $this->getVisible();
        return $array;
    }
}
