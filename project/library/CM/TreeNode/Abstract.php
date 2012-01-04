<?php

abstract class CM_TreeNode_Abstract {
	private $id;
	private $name;
	private $parent_id = null;
	private $nodes = array();
	private $leaves = array();

	public function __construct($id, $name, $parent_id = null) {
		$this->id = $id;
		$this->name = $name;
		$this->parent_id = $parent_id;
	}

	public function addNode(CM_TreeNode_Abstract $node) {
		$this->nodes[$node->getName()] = $node;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getParentId() {
		return $this->parent_id;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function hasNode($name) {
		return array_key_exists($name, $this->nodes);
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
		return $this->nodes[$name];
	}

	/**
	 * @param string $key
	 * @return boolean
	 */
	public function hasLeaf($key) {
		if (!$key) {
			return false;
		}
		return array_key_exists($key, $this->leaves);
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
		return $this->leaves[$key];
	}

	/**
	 * @return array
	 */
	public function getLeaves() {
		return $this->leaves;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setLeaf($key, $value) {
		$this->leaves[$key] = $value;
	}
}
