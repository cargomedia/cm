<?php

class CM_Tree_Language extends CM_Tree_Abstract {

	/**
	 * @param array|null $params
	 *
	 */
	public function __construct($params = null) {
		if (!$params) {
			$params = array();
		}
		parent::__construct('CM_TreeNode_Language', $params);
	}

	protected function _load() {
		$query = CM_Mysql::placeholder('SELECT `name` AS `key` FROM `' . TBL_CM_LANGUAGEKEY . '` WHERE `name` LIKE  ".%"');
		$result = CM_Mysql::query($query);
		while ($section = $result->fetchAssoc()) {
			$this->_addLanguageNode($section['key']);
		}
	}

	/**
	 * @param string $languageKey
	 * @throws CM_Exception_Invalid
	 */
	protected function _addLanguageNode($languageKey) {
		if (!preg_match('#^(.*)\.([^\.]+)$#', $languageKey , $matches)) {
			throw new CM_Exception_Invalid('Invalid Language Key found: `' . $languageKey . '`');
		}
		list($id, $parentId, $name) = $matches;
		if (!array_key_exists($id, $this->_nodesTmp)) {
			if ($parentId) {
				$this->_addLanguageNode($parentId);
			} else {
				$parentId = 0;
			}
			parent::_addNode($id, $name, $parentId);
		}
	}

}
