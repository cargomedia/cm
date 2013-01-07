<?php

class CM_Tree_Language extends CM_Tree_Abstract {

	protected $_rootId = '.';

	/**
	 * @param array|null $params
	 */
	public function __construct($params = null) {
		parent::__construct('CM_TreeNode_Language', $params);
	}

	protected function _load() {
		$result = CM_Mysql::select(TBL_CM_LANGUAGEKEY, 'name', 'name LIKE ".%"', 'name ASC');
		while ($section = $result->fetchAssoc()) {
			$this->_addLanguageNode($section['name']);
		}
	}

	/**
	 * @param string $languageKey
	 * @throws CM_Exception_Invalid
	 */
	private function _addLanguageNode($languageKey) {
		if (!preg_match('#^(.*)\.([^\.]+)$#', $languageKey , $matches)) {
			throw new CM_Exception_Invalid('Invalid Language Key found: `' . $languageKey . '`');
		}
		list($id, $parentId, $name) = $matches;
		if ($parentId && !array_key_exists($parentId, $this->_nodesTmp)) {
			$this->_addLanguageNode($parentId);
		}
		if (!$parentId) {
			$parentId = $this->_rootId;
		}
		parent::_addNode($id, $name, $parentId);
	}

}
