<?php

/**
 * Data-tree with id=>name nodes
 * Build tree by adding "flat" data in _load()
 *
 */

abstract class CM_Tree_Abstract {
	private $params;
	private $nodeClass;
	private $tree = array();

	public function __construct($nodeClass = 'CM_TreeNode_Abstract', $params = array()) {
		$this->nodeClass = $nodeClass;
		$this->params = $params;

		$this->tmp_nodes = array();
		$this->_load();
		$this->_buildTree();
		unset($this->tmp_nodes);
	}

	/**
	 * @param string $path
	 * @return CM_TreeNode_Abstract
	 * @throws CM_TreeException
	 */
	public function findNode($path) {
		if (!isset($this->nodes_cache))
			$this->nodes_cache = array();
		if (!$path) {
			return false;
		}
		if (!array_key_exists($path, $this->nodes_cache)) {
			$node = $this->tree;
			if ($path) {
				foreach (explode('.', $path) as $node_name) {
					$node = $node->getNode($node_name);
				}
			}
			$this->nodes_cache[$path] = $node;
		}
		return $this->nodes_cache[$path];
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
		$this->tmp_nodes[$id] = new $this->nodeClass($id, $name, $parent_id);
	}

	protected function _addLeaf($node_id, $id, $value = null) {
		if (!array_key_exists($node_id, $this->tmp_nodes)) {
			trigger_error("Cannot add leaf `$id` because node `$node_id` does not exist.", E_USER_NOTICE);
			return;
		}
		$this->tmp_nodes[$node_id]->setLeaf($id, $value);
	}

	protected function _getParam($key) {
		if (!array_key_exists($key, $this->params))
			return null;
		return $this->params[$key];
	}

	private function _buildTree() {
		$this->tree = new $this->nodeClass(0, 'root');
		$this->_buildNode($this->tree);
	}

	private function _buildNode(CM_TreeNode_Abstract $parent) {
		foreach ($this->tmp_nodes as $id => $node) {
			if ($parent->getId() === $node->getParentId()) {
				$parent->addNode($node);
				$this->_buildNode($node);
				unset($this->tmp_nodes[$id]);
			}
		}
	}
}

class CM_TreeException extends Exception {
}
