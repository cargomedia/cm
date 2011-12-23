<?php

class CM_Language {
	private static $vars_global = array();

	/**
	 * @param int $lang_id
	 * @return CM_Tree_Language;
	 */
	public static function getTree($lang_id) {
		$lang_id = (int) $lang_id;

		$cacheKey = CM_CacheConst::Language_Tree . '_langId:' . $lang_id;
		$tree = CM_CacheLocal::get($cacheKey);
		if ($tree === false) {
			$tree = new CM_Tree_Language('CM_TreeNode_Language', array('lang' => $lang_id));
			CM_CacheLocal::set($cacheKey, $tree);
		}

		return $tree;
	}

	/**
	 * Return section
	 *
	 * @param string $path
	 * @param int	$lang_id
	 * @return CM_TreeNode_Language
	 */
	public static function section($path = null, $lang_id = null) {
		if (!($lang_id = (int) $lang_id)) {
			$lang_id = self::getDefaultId();
		}

		return self::getTree($lang_id)->findNode($path);
	}

	public static function getDefaultId() {
		return Config::get()->language_default_id;
	}

	public static function getAllIds() {
		$result = CM_Mysql::query('SELECT lang_id FROM `' . TBL_LANG . '`');
		$ids = array();
		while ($id = (int) $result->fetchOne()) {
			$ids[] = $id;
		}
		return $ids;
	}

	/**
	 * Define value for a single constant or a list of key=>value pairs for globally using in text.
	 *
	 * @param array|string $key
	 * @param mixed		$value
	 */
	public static function defineGlobal($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				self::$vars_global[$k] = $v;
			}
		} else {
			self::$vars_global[$key] = $value;
		}
	}

	/**
	 * Return whether a text-path exists
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function key_exists($path) {
		list($path, $key) = self::_parsePath($path);
		try {
			return self::section($path)->key_exists($key);
		} catch (CM_TreeException $e) {
			return false;
		}
	}

	/**
	 * Return first existing path of several arguments
	 *
	 * @param string $path...
	 * @return string
	 */
	public static function key_exists_first() {
		$paths = func_get_args();

		foreach ($paths as $path) {
			if (self::key_exists($path)) {
				return $path;
			}
		}

		return '';
	}

	/**
	 * @param string $path
	 * @param array  $vars
	 * @return string
	 */
	public static function text($path, array $vars = null) {

		try {
			list($path, $key) = self::_parsePath($path);

			$section = self::section($path);
			if (!$section) {
				return false;
			}
			return $section->text($key, $vars);

		} catch (CM_TreeException $e) {
			if (DEBUG_MODE || IS_TEST ) {
				throw $e;
			}

			return $path;
		}
	}

	/**
	 * Executes a text inplacing variables with given kay=>value pair arguments.
	 *
	 * @param string $cdata
	 * @param array  $vars
	 * @return string
	 */
	public static function exec($cdata, array $vars = null) {
		if (!$vars) {
			$vars = array();
		}
		$vars = array_merge(self::$vars_global, $vars);

		return preg_replace('~\{\$(\w+)(->\w+\(.*?\))?\}~ie', "isset(\$vars['\\1']) ? \$vars['\\1']\\2 : '\\0'", $cdata);
	}

	/**
	 * Parse a full text-path
	 *
	 * @param string $path
	 * @return array array($section, $key)
	 */
	private static function _parsePath($path) {
		if (strlen($path) > 0 && $path[0] == '%') {
			$path = substr($path, 1);
		}

		$result = array();
		$rdot_position = strrpos($path, '.');
		$result[0] = substr($path, 0, $rdot_position);
		$result[1] = substr($path, $rdot_position + 1);

		return $result;
	}

	/**
	 * Convert special characters to html entities using the $charser encoding.
	 *
	 * @param string  $string
	 * @param integer $quote_style
	 * @param string  $charset
	 * @return string
	 */
	public static function htmlspecialchars($string, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {
		return htmlspecialchars($string, $quote_style, $charset);
	}

}
