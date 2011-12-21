<?php

class CM_Config {
	/**
	 * @return CM_Tree_Config;
	 */
	public static function getTree() {
		$cacheKey = CM_CacheConst::Config_Tree;
		$tree = CM_Cache::get($cacheKey);
		if ($tree === false) {
			$tree = new CM_Tree_Config('CM_TreeNode_Config');
			CM_Cache::set($cacheKey, $tree);
		}

		return $tree;
	}

	/**
	 * Returns a config section object.
	 *
	 * @param string $path
	 * @return CM_TreeNode_Config
	 */
	public static function section($path) {
		return self::getTree()->findNode($path);
	}

	public static function cleanCache($section_id, $key) {
		self::clearCache();
		CM_Cache::delete(CM_CacheConst::Configs_Section . '_configValues_section:' . $section_id . '_key:' . $key);
		CM_Cache::delete(CM_CacheConst::Configs_Section . '_configsList_section:' . $section_id);
		CM_Cache::delete(CM_CacheConst::Configs_Section . '_info_section:' . $section_id);
	}
	
	public static function clearCache() {
		CM_Cache::delete(CM_CacheConst::Config_Tree);
	}
}
