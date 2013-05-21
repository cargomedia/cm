<?php

class CM_Asset_Css_Library extends CM_Asset_Css {

	/**
	 * @param CM_Render        $render
	 * @throws CM_Exception
	 */
	public function __construct(CM_Render $render) {
		parent::__construct($render);

		foreach (array_reverse($render->getSite()->getNamespaces()) as $namespace) {
			foreach (array_reverse($render->getSite()->getThemes()) as $theme) {
				$path = $render->getThemeDir(true, $theme, $namespace) . 'variables.less';
				if (CM_File::exists($path)) {
					$file = new CM_File($path);
					$this->add($file->read());
				}
			}
		}
		if (CM_File::exists($path = DIR_PUBLIC . 'static/css/library/icon.less')) {
			$file = new CM_File($path);
			$this->add($file->read());
		}
		foreach (array_reverse($render->getSite()->getNamespaces()) as $namespace) {
			foreach (array_reverse($render->getSite()->getThemes()) as $theme) {
				foreach (CM_Util::rglob('*.less', $render->getThemeDir(true, $theme, $namespace) . 'css/') as $path) {
					$file = new CM_File($path);
					$this->add($file->read());
				}
			}
		}

		$viewClasses = CM_View_Abstract::getClassChildren(true);
		foreach ($viewClasses as $viewClassName) {
			if ($this->_isValidViewClass($viewClassName)) {
				$asset = new CM_Asset_Css_View($this->_render, $viewClassName);
				$this->add($asset->_getContent());
			}
		}
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	private function _isValidViewClass($className) {
		$invalidClassNameList = array('CM_Mail');
		foreach ($invalidClassNameList as $invalidClassName) {
			if ($className === $invalidClassName || is_subclass_of($className, $invalidClassName)) {
				return false;
			}
		}
		return true;
	}
}
