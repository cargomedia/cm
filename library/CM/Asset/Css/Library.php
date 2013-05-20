<?php

class CM_Asset_Css_Library extends CM_Asset_Css {

	/**
	 * @param CM_Render        $render
	 * @param CM_Site_Abstract $site
	 * @throws CM_Exception
	 */
	public function __construct(CM_Render $render, CM_Site_Abstract $site) {
		parent::__construct($render);
		foreach (array_reverse($site->getNamespaces()) as $namespace) {
			foreach (array_reverse($site->getThemes()) as $theme) {
				$path = $render->getThemeDir(true, $theme, $namespace) . 'variables.less';
				if (CM_File::exists($path)) {
					$this->add(new CM_File($path));
				}
			}
		}

		if (CM_File::exists($path = DIR_PUBLIC . 'static/css/library/icon.less')) {
			$this->add(new CM_File($path));
		}

		foreach (array_reverse($site->getNamespaces()) as $namespace) {
			foreach (array_reverse($site->getThemes()) as $theme) {
				foreach (CM_Util::rglob('*.less', $render->getThemeDir(true, $theme, $namespace) . 'css/') as $path) {
					$file = new CM_File($path);
					$this->add($file->read());
				}
			}
		}

		$viewClasses = CM_View_Abstract::getClasses($site->getNamespaces(), CM_View_Abstract::CONTEXT_CSS);
		foreach ($viewClasses as $className) {
			if (!preg_match('#^([^_]+)_([^_]+)_(.+)$#', $className, $matches)) {
				throw new CM_Exception("Cannot detect namespace from component's class-name");
			}
			list($className, $namespace, $viewType, $viewName) = $matches;
			$relativePaths = array();
			foreach ($render->getSite()->getThemes() as $theme) {
				$basePath = $render->getThemeDir(true, $theme, $namespace) . $viewType . '/' . $viewName . '/';
				foreach (CM_Util::rglob('*.less', $basePath) as $path) {
					$relativePaths[] = preg_replace('#^' . $basePath . '#', '', $path);
				}
			}
			foreach (array_unique($relativePaths) as $path) {
				$prefix = '.' . $className;
				if ('Component' == $viewType) {
					if ($path != 'default.less' && strpos($path, '/') === false) {
						$prefix .= '.' . preg_replace('#.less$#', '', $path);
					}
				}

				$file = $render->getLayoutFile($viewType . '/' . $viewName . '/' . $path, $namespace);
				$this->add($file->read(), $prefix);
			}
		}
	}
}
