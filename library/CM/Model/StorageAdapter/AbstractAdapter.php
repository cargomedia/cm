<?php

abstract class CM_Model_StorageAdapter_AbstractAdapter {

	/**
	 * @param array $id
	 * @return array|null
	 */
	abstract public function load(array $id);

	/**
	 * @param array $id
	 * @param array $data
	 */
	abstract public function save(array $id, array $data);

	/**
	 * @param array $data
	 * @return array
	 */
	abstract public function create(array $data);

	/**
	 * @param array $id
	 */
	abstract public function delete(array $id);
}
