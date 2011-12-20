<?php

class CM_Tree_Config extends CM_Tree_Abstract {

	protected function _load() {
		// Add sections
		$query = CM_Mysql::placeholder('
			SELECT `config_section_id` AS id, `section` AS `name`, `parent_section_id` AS `parent_id`
			FROM `'
			. TBL_CONFIG_SECTION . '`');
		$result = CM_Mysql::query($query);

		while ($section = $result->fetchAssoc()) {
			$this->_addNode((int) $section['id'], $section['name'], (int) $section['parent_id']);
		}

		// Add keys
		$result = CM_Mysql::exec('SELECT `name`, `value`, `config_section_id` FROM TBL_CONFIG');
		while ($config = $result->fetchAssoc()) {
			$this->_addLeaf((int) $config['config_section_id'], $config['name'], json_decode($config['value']));
		}
	}
}
