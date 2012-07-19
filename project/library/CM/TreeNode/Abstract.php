<?php

abstract class CM_TreeNode_Abstract {

	/** @var string */
	private $_id;

	/** @var string */
	private $_name;

	/** @var string|null */
	private $_parentId = null;

	/** @var CM_TreeNode_Abstract|null */
	private $_parent;

	/** @var CM_TreeNode_Abstract[] */
	private $_nodes = array();

	/** @var CM_TreeNode_Abstract[] */
	private $_leaves = array();

	/**
	 * @param strin        $id
	 * @param string       $name
	 * @param string|null  $parentId
	 */
	public function __construct($id, $name, $parentId = null) {
		$this->_id = (string) $id;
		$this->_name = (string) $name;
		$this->_parentId = (string) $parentId;
	}

	/**
	 * @param CM_TreeNode_Abstract $node
	 */
	public function addNode(CM_TreeNode_Abstract $node) {
		$this->_nodes[$node->getName()] = $node;
		$node->setParent($this);
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return string|null
	 */
	public function getParentId() {
		return $this->_parentId;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function hasNode($name) {
		return array_key_exists($name, $this->_nodes);
	}

	/**
	 * @return bool
	 */
	public function hasNodes() {
		return count($this->_nodes) > 0;
	}

	/**
	 * @return bool
	 */
	public function hasGrandNodes() {
		foreach ($this->getNodes() as $node) {
			if ($node->hasNodes()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $name
	 * @return CM_TreeNode_Abstract
	 * @throws CM_TreeException
	 */
	public function getNode($name) {
		if (!$this->hasNode($name)) {
			throw new CM_TreeException("Node `" . $this->getId() . "` does not contain node `$name`.");
		}
		return $this->_nodes[$name];
	}

	/**
	 * @return CM_TreeNode_Abstract[]
	 */
	public function getNodes() {
		return $this->_nodes;
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function hasLeaf($key) {
		if (!$key) {
			return false;
		}
		return array_key_exists($key, $this->_leaves);
	}

	/**
	 * @param string $key
	 * @return string
	 * @throws CM_TreeException
	 */
	public function getLeaf($key) {
		if (!$this->hasLeaf($key)) {
			throw new CM_TreeException("Node `" . $this->getId() . "` does not contain leaf `$key`.");
		}
		return $this->_leaves[$key];
	}

	/**
	 * @return array
	 */
	public function getLeaves() {
		return $this->_leaves;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setLeaf($key, $value) {
		$this->_leaves[$key] = $value;
	}

	/**
	 * @param CM_TreeNode_Abstract $parent
	 */
	public function setParent(CM_TreeNode_Abstract $parent) {
		$this->_parent = $parent;
	}

	/**
	 * @return CM_TreeNode_Abstract|null
	 */
	public function getParent() {
		return $this->_parent;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		$path = '';
		if ($this->getParent()) {
			$path = $this->getParent()->getPath() . '.';
		}
		return $path . $this->getName();
	}

	/**
	 * @param string $id
	 * @return CM_TreeNode_Abstract|null
	 */
	public function findById($id) {
		$id = (string) $id;
		if ($this->getId() === $id) {
			return $this;
		}
		foreach ($this->getNodes() as $node) {
			if ($node = $node->findById($id)) {
				return $node;
			}
		}
		return null;

	}
}
