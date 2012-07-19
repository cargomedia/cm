<?php

abstract class CM_Tree_Abstract {

	/** @var array */
	private $_params;

	/** @var string */
	private $_nodeClass;

	/** @var CM_TreeNode_Abstract */
	private $_root;

	/** @var array */
	protected $_nodesCache = array();

	/** @var array */
	protected $_nodesTmp = array();

	/**
	 * @param string                      $nodeClass
	 * @param array|null                  $params
	 * @throws CM_Exception_InvalidParam
	 */
	public function __construct($nodeClass, array $params = null) {
		if (!is_subclass_of($nodeClass, 'CM_TreeNode_Abstract')) {
			throw new CM_Exception_InvalidParam('`nodeClass` needs to be subclass of `CM_TreeNode_Abstract`');
		}
		$this->_nodeClass = $nodeClass;
		$this->_params = (array) $params;
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
		if (!isset($this->_nodesCache)) {
			$this->_nodesCache = array();
		}
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
		$this->_nodesTmp[$id] = new $this->_nodeClass($id, $name, $parent_id);
	}

	/**
	 * @param mixed      $nodeId
	 * @param mixed      $id
	 * @param mixed|null $value
	 */
	protected function _addLeaf($nodeId, $id, $value = null) {
		if (!array_key_exists($nodeId, $this->_nodesTmp)) {
			trigger_error("Cannot add leaf `$id` because node `$nodeId` does not exist.", E_USER_NOTICE);
			return;
		}
		$this->_nodesTmp[$nodeId]->setLeaf($id, $value);
	}

	/**
	 * @param $key
	 * @return null
	 */
	protected function _getParam($key) {
		if (!array_key_exists($key, $this->_params)) {
			return null;
		}
		return $this->_params[$key];
	}

	private function _buildTree() {
		$this->_root = new $this->_nodeClass(0, 'root');
		$this->_buildNode($this->_root);
	}

	/**
	 * @param CM_TreeNode_Abstract $parent
	 */
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
