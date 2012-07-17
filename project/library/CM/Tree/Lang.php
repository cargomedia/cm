<?php

class CM_Tree_Lang extends CM_Tree_Abstract {

	protected function _load() {
		// Add sections
		$query = CM_Mysql::placeholder(
				'
			SELECT `lang_section_id` AS id, `section` AS `name`, `parent_section_id` AS `parent_id`
			FROM `' . TBL_CM_LANG_SECTION . '`');
		$result = CM_Mysql::query($query);

		while ($section = $result->fetchAssoc()) {
			$this->_addNode((int) $section['id'], $section['name'], (int) $section['parent_id']);
		}

		// Add keys
		$result = CM_Mysql::query('SELECT `key`, `lang_section_id` FROM `' . TBL_CM_LANG_KEY . '`');
		while ($row = $result->fetchObject()) {
			$this->_addLeaf((int) $row->lang_section_id, $row->key);
		}
		$result = CM_Mysql::query(
				'SELECT `key`, `value`, `lang_section_id`
			FROM `' . TBL_CM_LANG_KEY . '` LEFT JOIN `' . TBL_CM_LANG_VALUE . '` USING(`lang_key_id`)
			WHERE `lang_id`=' . $this->_getParam('lang'));
		while ($row = $result->fetchObject()) {
			$this->_addLeaf((int) $row->lang_section_id, $row->key, $row->value);
		}
	}

}
