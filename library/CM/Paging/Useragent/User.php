<?php

class CM_Paging_Useragent_User extends CM_Paging_Useragent_Abstract {
	/**
	 * @var CM_Model_User
	 */
	private $_user;

	/**
	 * @param CM_Model_User $user
	 */
	public function __construct(CM_Model_User $user) {
		$this->_user = $user;
		$source = new CM_PagingSource_Sql_Deferred('useragent, createStamp', TBL_CM_USERAGENT,
				'userId=' . $this->_user->getId(), '`createStamp` DESC');
		parent::__construct($source);
	}

	/**
	 * @param string $useragent
	 */
	public function add($useragent) {
		$useragent = (string) $useragent;
		CM_Db_Db::replaceDelayed(TBL_CM_USERAGENT, array('userId' => $this->_user->getId(), 'useragent' => $useragent, 'createStamp' => time()));
	}
}
