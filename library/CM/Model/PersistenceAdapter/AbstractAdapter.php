<?php

abstract class CM_Model_PersistenceAdapter_AbstractAdapter {

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
}
