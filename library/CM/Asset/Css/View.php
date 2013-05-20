<?php

class CM_Asset_Css_View extends CM_Asset_Css {

	/**
	 * @param CM_Render  $render
	 * @param string     $className
	 * @throws CM_Exception
	 */
	public function __construct(CM_Render $render, $className) {
		parent::__construct($render);
		$classNameParts = explode('_', $className, 3);
		if (count($classNameParts) < 2) {
			throw new CM_Exception('Cannot detect all className parts from component\'s classNname `' . $className . '`');
		}
		$namespace = array_shift($classNameParts);
		$viewType = array_shift($classNameParts);
		$viewName = array_shift($classNameParts);

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
			if ('Component' == $viewType) {
				if ($path !== 'default.less' && strpos($path, '/') === false) {
					$prefix .= '.' . preg_replace('#.less$#', '', $path);
				}
			}
			$file = $render->getLayoutFile($viewPath . $path, $namespace);
			$this->add($file->read(), $prefix);
		}
	}
}
