<?php

class CM_Asset_Css_View extends CM_Asset_Css {

	/**
	 * @param CM_Render  $render
	 * @param string     $className
	 * @throws CM_Exception
	 */
	public function __construct(CM_Render $render, $className) {
		parent::__construct($render);
		if (!preg_match('#^([^_]+)_([^_]+)_?(.*)$#', $className, $matches)) {
			throw new CM_Exception('Cannot detect all className parts from view\'s classNname `' . $className . '`');
		}
		list($className, $namespace, $viewType, $viewName) = $matches;
		$viewPath = $viewType . '/';
		if ($viewName) {
			$viewPath .= $viewName . '/';
		}

		$relativePaths = array();
		foreach ($render->getSite()->getThemes() as $theme) {
			$basePath = $render->getThemeDir(true, $theme, $namespace) . $viewPath;
			foreach (CM_Util::rglob('*.less', $basePath) as $path) {
				$relativePaths[] = preg_replace('#^' . $basePath . '#', '', $path);
			}
		}
		foreach (array_unique($relativePaths) as $path) {
			$prefix = '.' . $className;
			if (is_subclass_of($className, 'CM_Component_Abstract') && !is_subclass_of($className, 'CM_Page_Abstract')) {
				if ($path !== 'default.less' && strpos($path, '/') === false) {
					$prefix .= '.' . preg_replace('#.less$#', '', $path);
				}
			}
			$file = $render->getLayoutFile($viewPath . $path, $namespace);
			$this->add($file->read(), $prefix);
		}
	}
}
