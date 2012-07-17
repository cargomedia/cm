<?php

/**
 * Data-tree with id=>name nodes
 * Build tree by adding "flat" data in _load()
 *
 */

abstract class CM_Tree_Abstract {
	private $params;
	private $nodeClass;

	/** @var CM_TreeNode_Abstract */
	private $_root;

	/** @var array */
	protected $_nodesCache = array();

	/** @var array */
	protected $_nodesTmp = array();

	public function __construct($nodeClass = 'CM_TreeNode_Abstract', $params = array()) {
		$this->nodeClass = $nodeClass;
		$this->params = $params;

		$this->_nodesTmp = array();
		$this->_load();
		$this->_buildTree();
		unset($this->_nodesTmp);
	}

	/**
	 * @param string $path
	 * @return CM_TreeNode_Abstract
	 * @throws CM_TreeException
	 */
	public function findNode($path) {
		if (!isset($this->_nodesCache))
			$this->_nodesCache = array();
		if (!$path) {
			return false;
		}
		if (!array_key_exists($path, $this->_nodesCache)) {
			$node = $this->_root;
			if ($path) {
				foreach (explode('.', $path) as $node_name) {
					$node = $node->getNode($node_name);
				}
			}
			$this->_nodesCache[$path] = $node;
		}
		return $this->_nodesCache[$path];
	}

	/**
	 * @return CM_TreeNode_Abstract
	 */
	public function getRoot() {
		return $this->_root;
	}

	/**
	 * Load tree data
	 * First add nodes, then leaves
	 */
	protected abstract function _load();

	/**
	 * Add a node
	 *
	 * @param mixed $id Unique
	 * @param mixed $name
	 * @param mixed $parent_id
	 */
	protected function _addNode($id, $name, $parent_id = null) {
		$this->_nodesTmp[$id] = new $this->nodeClass($id, $name, $parent_id);
	}

	protected function _addLeaf($node_id, $id, $value = null) {
		if (!array_key_exists($node_id, $this->_nodesTmp)) {
			trigger_error("Cannot add leaf `$id` because node `$node_id` does not exist.", E_USER_NOTICE);
			return;
		}
		$this->_nodesTmp[$node_id]->setLeaf($id, $value);
	}

	protected function _getParam($key) {
		if (!array_key_exists($key, $this->params))
			return null;
		return $this->params[$key];
	}

	private function _buildTree() {
		$this->_root = new $this->nodeClass(0, 'root');
		$this->_buildNode($this->_root);
	}

	private function _buildNode(CM_TreeNode_Abstract $parent) {
		foreach ($this->_nodesTmp as $id => $node) {
			if ($parent->getId() === $node->getParentId()) {
				$parent->addNode($node);
				$this->_buildNode($node);
				unset($this->_nodesTmp[$id]);
			}
		}
	}
}

class CM_TreeException extends Exception {
}
