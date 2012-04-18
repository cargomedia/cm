<?php

class CM_Response_Resource_CSS extends CM_Response_Resource_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'text/css');
		$this->enableCache();

		if ($this->_getFilename() == 'library.css') {
			$content = '';
			foreach (CM_Util::rglob('*.css', DIR_PUBLIC . 'static/css/library/') as $path) {
				$content .= new CM_File($path);
			}
		} elseif ($this->_getFilename() == 'internal.css') {
			$css = new CM_Css();
			$css->add(new CM_Css($this->getRender()->getLayoutFile('presets.less')->read()));
			$css->add(new CM_Css($this->getRender()->getLayoutFile('layout.less')->read()));

			foreach ($this->getSite()->getNamespaces() as $namespace) {
				foreach (array_reverse($this->getSite()->getThemes()) as $theme) {
					foreach (CM_Util::rglob('*.css', $this->getRender()->getThemeDir(true, $theme, $namespace) . 'css/') as $path) {
						$file = new CM_File($path);
						$css->add(new CM_Css($file->read()));
					}
				}
			}

			foreach (array('Component', 'Page', 'FormField') as $viewType) {
				$viewClasses = array();
				foreach ($this->getSite()->getNamespaces() as $namespace) {
					$viewClasses = array_merge($viewClasses, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/' . $viewType . '/'));
				}
				foreach ($this->_getClasses($viewClasses) as $viewClass) {
					if (!preg_match('#^(\w+)_' . $viewType . '_(.+)$#', $viewClass['name'], $matches)) {
						throw new CM_Exception("Cannot detect namespace from component's class-name");
					}
					$namespace = $matches[1];
					$viewName = $matches[2];
					$relativePaths = array();
					foreach ($this->getRender()->getSite()->getThemes() as $theme) {
						$basePath = $this->getRender()->getThemeDir(true, $theme, $namespace) . $viewType . '/' . $viewName . '/';
						foreach (CM_Util::rglob('*.style', $basePath) as $path) {
							$relativePaths[] = preg_replace('#^' . $basePath . '#', '', $path);
						}
					}
					foreach (array_unique($relativePaths) as $path) {
						$prefix = '.' . $viewClass['name'];
						if ('Component' == $viewType) {
							if ($path != 'default.style' && strpos($path, '/') === false) {
								$prefix .= '.' . preg_replace('#.style$#', '', $path);
							}
						}

						$file = $this->getRender()->getLayoutFile($viewType . '/' . $viewName . '/' . $path, $namespace);
						$css->add(new CM_Css($file->read(), $prefix));
					}
				}
			}
			$content = $css->compile($this->getRender());
		} elseif (file_exists(DIR_PUBLIC . 'static/css/' . $this->_getFilename())) {
			$content = new CM_File(DIR_PUBLIC . 'static/css/' . $this->_getFilename());
		} else {
			throw new CM_Exception_Invalid('Invalid filename: `' . $this->_getFilename() . '`');
		}
		return $content;
	}
}
