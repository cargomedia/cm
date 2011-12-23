<?php

class CM_LanguageEdit {

	/**
	 * Get section id using section path.
	 *
	 * @param string $section_path dot separated
	 * @return integer section_id
	 */
	private static function parseSectionPath($section_path) {
		$query_c = CM_Mysql::compile_placeholder('SELECT `lang_section_id` FROM `' . TBL_CM_LANG_SECTION . '`
				WHERE `parent_section_id`=? AND `section`="?"');

		$_section_id = 0;
		$_path = explode('.', $section_path);
		foreach ($_path as $section) {
			$result = CM_Mysql::query(CM_Mysql::placeholder($query_c, $_section_id, $section));

			if (!$result->numRows()) {
				throw new CM_LanguageEditException('Unknown section "' . htmlspecialchars($section) . '" in path "' . htmlspecialchars($section_path) . '"',
					CM_LanguageEditException::SECTION_NOT_EXISTS);
			}

			$_section_id = $result->fetchOne();

			$result->free();
		}

		return $_section_id;
	}

	/**
	 * Get section id using section path.
	 *
	 * @param string $section_path
	 * @return integer
	 */
	public static function section_id($section_path) {
		return self::parseSectionPath($section_path);
	}

	/**
	 * Get all available languages data.
	 *
	 * @return array id-indexed objects list
	 */
	public static function getLanguages() {
		static $languages;

		if (isset($languages)) {
			return $languages;
		}

		$default_lang_id = CM_Language::getDefaultId();

		$result = CM_Mysql::query('SELECT * FROM `' . TBL_CM_LANG . '`');

		$languages = array();
		while ($row = $result->fetchObject()) {
			if ($row->lang_id == $default_lang_id) {
				$row->default = true;
			}

			$languages[$row->lang_id] = $row;
		}

		$result->free();

		return $languages;
	}

	/**
	 * Get sections info list.
	 *
	 * @param integer $parent_section_id
	 * @return array id-indexed objects list
	 */
	public static function getSections($parent_section_id) {
		if ($parent_section_id != 0) {
			// checking for argued section existence
			$query = CM_Mysql::placeholder('SELECT `section` FROM `' . TBL_CM_LANG_SECTION . '`
					WHERE `lang_section_id`=?', $parent_section_id);

			if (!CM_Mysql::query($query)->numRows()) {
				throw new CM_LanguageEditException('Section with argued `$parent_section_id` is not exists',
					CM_LanguageEditException::SECTION_NOT_EXISTS);
			}
		}

		// getting sections
		$query = CM_Mysql::placeholder('SELECT * FROM `' . TBL_CM_LANG_SECTION . '`
				WHERE `parent_section_id`=?', $parent_section_id);

		$result = CM_Mysql::query($query);

		if (!$result->numRows()) {
			return array();
		}

		$sections = array();

		while ($_sect = $result->fetchObject()) {
			$_sect->has_children = false;
			$sections[$_sect->lang_section_id] = $_sect;
		}

		$result->free();

		// find out a child sections existence
		$query = CM_Mysql::placeholder('SELECT DISTINCT `parent_section_id`
				FROM `'
			. TBL_CM_LANG_SECTION . '`
				WHERE `parent_section_id` IN(?@)', array_keys($sections));

		$result = CM_Mysql::query($query);

		while ($section_id = $result->fetchOne()) {
			$sections[$section_id]->has_children = true;
		}

		$result->free();

		return $sections;
	}

	/**
	 * Get a key name using $lang_key_id.
	 *
	 * @param integer $lang_key_id
	 * @return string key name or FALSE if there are no key with such id
	 */
	public static function getKey($lang_key_id) {
		$query = CM_Mysql::placeholder('SELECT `key` FROM `' . TBL_CM_LANG_KEY . '`
				WHERE `lang_key_id`=?', $lang_key_id);

		return CM_Mysql::query($query)->fetchOne();
	}

	/**
	 * Format a language value text escaping it html enrties.
	 *
	 * @param string $text
	 * @return string html formated text
	 */
	private static function formatValues($text) {
		$text = CM_Language::htmlspecialchars($text);

		$text = preg_replace('~\{\$\w+\}~',
			'<span class="text_var">\\0</span>',
			$text);

		$text = nl2br($text);

		return $text;
	}

	/**
	 * Get a key-indexed language key nodes list.
	 *
	 * @param integer $section_id
	 * @param boolean $format_values
	 * @return array lang_key_id-indexed objects list of CM_LanguageKeyNode
	 */
	public static function getKeyNodes($section, $format_values = false) {
		// getting section_id
		if (!is_numeric($section)) {
			$section_id = self::parseSectionPath($section);
		} else { // setting by section_id
			$section_id = (int) $section;
		}
		$query = CM_Mysql::placeholder('SELECT `tbl_key`.`lang_key_id`,`key`,`lang_id`,`value`
				FROM `'
			. TBL_CM_LANG_KEY . '` `tbl_key`
					LEFT JOIN `'
			. TBL_CM_LANG_VALUE . '` `tbl_val` USING(`lang_key_id`)
				WHERE `lang_section_id`=? ORDER BY `key`', $section_id);

		$result = CM_Mysql::query($query);

		$key_nodes = array();
		while ($row = $result->fetchObject()) {
			if (!isset($key_nodes[$row->lang_key_id])) {
				$node = new CM_LanguageKeyNode();
				$node->lang_key_id = $row->lang_key_id;
				$node->key = $row->key;
				$node->values = array();

				$key_nodes[$row->lang_key_id] = $node;
			}

			$key_nodes[$row->lang_key_id]->values[$row->lang_id] = !$format_values ? $row->value : self::formatValues($row->value);
		}

		return $key_nodes;
	}

	/**
	 * Get a single key node object.
	 *
	 * @param integer $lang_key_id
	 * @param boolean $format_values
	 * @return CM_LanguageKeyNode
	 */
	public static function getKeyNode($lang_key_id, $format_values = false) {
		// getting key
		$key = self::getKey($lang_key_id);

		if (!$key) {
			throw new CM_LanguageEditException('a key with $lang_key_id is not exists',
				CM_LanguageEditException::KEY_NOT_EXISTS);
		}

		// getting values
		$query = CM_Mysql::placeholder('SELECT `lang_id`, `value`
				FROM `'
			. TBL_CM_LANG_VALUE . '`
				WHERE `lang_key_id`=?', $lang_key_id);

		$result = CM_Mysql::query($query);

		$values = array();
		while ($row = $result->fetchObject()) {
			$values[$row->lang_id] = !$format_values ? $row->value : self::formatValues($row->value);
		}

		$key_node = new CM_LanguageKeyNode();
		$key_node->lang_key_id = $lang_key_id;
		$key_node->key = $key;
		$key_node->values = $values;

		return $key_node;
	}

	/**
	 * Setup a language key with values.
	 * Language key will automatically created if it's need.
	 *
	 * @param integer|string $section id or path
	 * @param string $key
	 * @param array $values lang_id=>value pairs
	 */
	public static function setKey($section, $key, array $values) {
		// checking params
		if (!$section) {
			throw new CM_LanguageEditException('argument $section value is empty or equals zero',
				CM_LanguageEditException::EMPTY_ARGUMENT_SECTION);
		}

		if (!strlen($key)) {
			throw new CM_LanguageEditException('empty argument $key',
				CM_LanguageEditException::EMPTY_ARGUMENT_KEY);
		}

		// getting section_id
		if (!is_numeric($section)) {
			$section_id = self::parseSectionPath($section);
		} else { // setting by section_id
			$section_id = (int) $section;

			$query = CM_Mysql::placeholder('SELECT `section` FROM `' . TBL_CM_LANG_SECTION . '`
					WHERE `lang_section_id`=?', $section_id);

			if (!CM_Mysql::query($query)->numRows()) {
				throw new CM_LanguageEditException('Section with argued `$section_id` is not exists',
					CM_LanguageEditException::SECTION_NOT_EXISTS);
			}
		}

		// checking lang_key for existense
		$query = CM_Mysql::placeholder('SELECT `lang_key_id` FROM `' . TBL_CM_LANG_KEY . '`
				WHERE `lang_section_id`=? AND `key`="?"', $section_id, $key);

		$result = CM_Mysql::query($query);

		if ($result->numRows()) {
			$lang_key_id = $result->fetchOne();
			$result->free();
		} else {
			$query = CM_Mysql::placeholder('INSERT INTO `' . TBL_CM_LANG_KEY . '`
					SET `lang_section_id`=?, `key`="?"', $section_id, $key);
			CM_Mysql::query($query);

			$lang_key_id = CM_Mysql::insert_id();
		}

		// setting values
		self::updateKeyValues($lang_key_id, $values);
	}

	/**
	 * Update an existent key values using $lang_key_id.
	 *
	 * @param integer $lang_key_id
	 * @throws CM_LanguageEditException
	 */
	public static function updateKeyValues($lang_key_id, array $values) {
		if (!($lang_key_id = (int) $lang_key_id)) {
			throw new CM_LanguageEditException('empty argument $lang_key_id',
				CM_LanguageEditException::EMPTY_ARGUMENT_KEY_ID);
		}

		// getting existent values
		$existent_values = array();

		$query = CM_Mysql::placeholder('SELECT `lang_id`, `value`
				FROM `'
			. TBL_CM_LANG_VALUE . '` WHERE `lang_key_id`=?',
			$lang_key_id);

		$result = CM_Mysql::query($query);
		if ($result->numRows()) {
			while ($row = $result->fetchObject()) {
				$existent_values[$row->lang_id] = $row->value;
			}
		} else {
			// checking for key existence
			if (!CM_LanguageEdit::getKey($lang_key_id)) {
				throw new CM_LanguageEditException('a key with id ' . $lang_key_id . ' is not exists',
					CM_LanguageEditException::KEY_NOT_EXISTS);
			}
		}
		$result->free();

		$insert_query = CM_Mysql::compile_placeholder("INSERT INTO `" . TBL_CM_LANG_VALUE . "`(`lang_key_id`,`value`,`lang_id`) VALUES($lang_key_id, '?', ?)"
		);

		$update_query = CM_Mysql::compile_placeholder("UPDATE `" . TBL_CM_LANG_VALUE . "` SET `value`='?' WHERE `lang_key_id`=$lang_key_id AND `lang_id`=?"
		);

		$delete_query = CM_Mysql::compile_placeholder("DELETE FROM `" . TBL_CM_LANG_VALUE . "` WHERE `lang_key_id`=$lang_key_id AND `lang_id`=?"
		);

		$lang_ids = array_keys(self::getLanguages());
		foreach ($lang_ids as $lang_id) {
			if (!isset($values[$lang_id])) {
				if (!isset($existent_values[$lang_id])) {
					continue;
				} else {
					$aff_query = CM_Mysql::placeholder($delete_query, $lang_id);
				}
			} elseif (isset($existent_values[$lang_id])) {
				if ($values[$lang_id] == $existent_values[$lang_id]) {
					continue;
				}

				$aff_query = CM_Mysql::placeholder($update_query, $values[$lang_id], $lang_id);
			} else /*( isset($values[$lang_id])
			&& !isset($existent_values[$lang_id]) )*/ {
				$aff_query = CM_Mysql::placeholder($insert_query, $values[$lang_id], $lang_id);
			}

			CM_Mysql::query($aff_query);
		}

		CM_CacheLocal::cleanLanguages();

		return isset($aff_query);
	}

	/**
	 * Delete a language key with values which it refer.
	 *
	 * @param string|integer $section path or id
	 * @param string $key
	 */
	public static function deleteKey($section, $key) {
		// checking params
		if (!$section) {
			throw new CM_LanguageEditException('argument $section value is empty or equals zero',
				CM_LanguageEditException::EMPTY_ARGUMENT_SECTION);
		} elseif (!strlen($key)) {
			throw new CM_LanguageEditException('empty argument $key',
				CM_LanguageEditException::EMPTY_ARGUMENT_KEY);
		}

		// getting section_id
		if (!is_numeric($section)) {
			$section_id = self::parseSectionPath($section);
		} else { // setting by section_id
			$section_id = (int) $section;

			$query = CM_Mysql::placeholder('SELECT `section` FROM `' . TBL_CM_LANG_SECTION . '`
					WHERE `lang_section_id`=?', $section_id);

			if (!CM_Mysql::query($query)->numRows()) {
				throw new CM_LanguageEditException('Section with argued `$section_id` is not exists',
					CM_LanguageEditException::SECTION_NOT_EXISTS);
			}
		}

		// getting $lang_key_id
		$query = CM_Mysql::placeholder('SELECT `lang_key_id` FROM `' . TBL_CM_LANG_KEY . '`
				WHERE `lang_section_id`=? AND `key`="?"', $section_id, $key);

		$result = CM_Mysql::query($query);
		if (!$result->numRows()) {
			return false;
		}

		$lang_key_id = $result->fetchOne();

		$result->free();

		return self::deleteKeyById($lang_key_id);
	}

	/**
	 * Delete a language key with values which it refer using $lang_key_id.
	 *
	 * @param integer $lang_key_id
	 */
	public static function deleteKeyById($lang_key_id) {
		$lang_key_id = (int) $lang_key_id;

		// deleting values
		CM_Mysql::query("DELETE FROM `" . TBL_CM_LANG_VALUE . "` WHERE `lang_key_id`=$lang_key_id");

		// deleting key
		CM_Mysql::query("DELETE FROM `" . TBL_CM_LANG_KEY . "` WHERE `lang_key_id`=$lang_key_id");

		if (CM_Mysql::affected_rows()) {
			CM_CacheLocal::cleanLanguages();
			return true;
		} else {
			return false;
		}
	}

	public static function createSection($parent_section, $section, $description = '') {
		if (!strlen($section)) {
			throw new CM_LanguageEditException('empty argument $section',
				CM_LanguageEditException::EMPTY_ARGUMENT_SECTION);
		}

		// getting section_id
		if (!is_numeric($parent_section)) {
			$parent_section_id = self::parseSectionPath($parent_section);
		} elseif ($parent_section_id = (int) $parent_section) { // setting by section_id
			$query = CM_Mysql::placeholder('SELECT `section` FROM `' . TBL_CM_LANG_SECTION . '`
					WHERE `lang_section_id`=?', $parent_section_id);

			if (!CM_Mysql::query($query)->numRows()) {
				throw new CM_LanguageEditException('Section with argued `$parent_section_id` is not exists',
					CM_LanguageEditException::SECTION_NOT_EXISTS);
			}
		}

		$query = CM_Mysql::placeholder('INSERT INTO `' . TBL_CM_LANG_SECTION . '`
				SET `parent_section_id`=?, `section`="?", `description`="?"', $parent_section_id, $section, $description);

		// sending insert query
		CM_Mysql::query($query);

		$object = new stdClass();
		$object->lang_section_id = CM_Mysql::insert_id();
		$object->parent_section_id = "$parent_section_id";
		$object->section = $section;
		$object->description = $description;

		CM_CacheLocal::cleanLanguages();

		return $object;
	}

	public static function getKeyId($section, $key) {
		// checking params
		if (!$section) {
			throw new CM_LanguageEditException('argument $section value is empty or equals zero',
				CM_LanguageEditException::EMPTY_ARGUMENT_SECTION);
		} elseif (!strlen($key)) {
			throw new CM_LanguageEditException('empty argument $key',
				CM_LanguageEditException::EMPTY_ARGUMENT_KEY);
		}

		// getting section_id
		if (!is_numeric($section)) {
			$section_id = self::parseSectionPath($section);
		} else { // getting by section_id
			$section_id = (int) $section;

			$query = CM_Mysql::placeholder('SELECT `section` FROM `' . TBL_CM_LANG_SECTION . '`
					WHERE `lang_section_id`=?', $section_id);

			if (!CM_Mysql::query($query)->numRows()) {
				throw new CM_LanguageEditException('Section with argued `$section_id` is not exists',
					CM_LanguageEditException::SECTION_NOT_EXISTS);
			}
		}

		// getting $lang_key_id
		$query = CM_Mysql::placeholder('SELECT `lang_key_id` FROM `' . TBL_CM_LANG_KEY . '`
				WHERE `lang_section_id`=? AND `key`="?"', $section_id, $key);

		$result = CM_Mysql::query($query);
		if (!$result->numRows()) {
			return false;
		}

		$lang_key_id = $result->fetchOne();

		$result->free();

		return $lang_key_id;
	}

	public static function renameKey($lang_key_id, $new_name) {
		if (!($lang_key_id = (int) $lang_key_id)) {
			throw new CM_LanguageEditException('empty or zero argument $lang_key_id',
				CM_LanguageEditException::EMPTY_ARGUMENT_KEY_ID);
		}

		if (!strlen($new_name)) {
			throw new CM_LanguageEditException('empty argument $new_name',
				CM_LanguageEditException::EMPTY_ARGUMENT_KEY);
		}

		$query = CM_Mysql::placeholder('UPDATE `' . TBL_CM_LANG_KEY . '` SET `key`="?" WHERE `lang_key_id`=?', $new_name, $lang_key_id);

		CM_Mysql::query($query);

		if (CM_Mysql::affected_rows()) {
			CM_CacheLocal::cleanLanguages();
			return true;
		} else {
			return false;
		}
	}

}

class CM_LanguageEditException extends Exception {
	const EMPTY_ARGUMENT_KEY = 1;
	const EMPTY_ARGUMENT_KEY_ID = 2;
	const EMPTY_ARGUMENT_SECTION = 3;
	const KEY_NOT_EXISTS = 4;
	const SECTION_NOT_EXISTS = 5;
	const DUPLICATE_ENTRY = 6;
}
