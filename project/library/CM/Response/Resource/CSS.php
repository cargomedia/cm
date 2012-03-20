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
			$presets = new CM_Css($this->getRender()->getLayoutFile('presets.style')->read(), $this->getRender());
			$content = new CM_Css($this->getRender()->getLayoutFile('layout.style')->read(), $this->getRender(), $presets);

			foreach ($this->getRender()->getSite()->getThemes() as $theme) {
				foreach (CM_Util::rglob('*.css', $this->getRender()->getThemeDir(true, $theme) . 'css/') as $path) {
					$file = new CM_File($path);
					$content .= new CM_Css($file->read(), $this->getRender(), $presets);
				}
			}

			foreach (array('Component', 'Page') as $viewType) {
				$viewClasses = array();
				foreach (self::getSite()->getNamespaces() as $namespace) {
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
						$content .= new CM_Css($file->read(), $this->getRender(), $presets, $prefix);
					}
				}
			}
		} elseif (file_exists(DIR_PUBLIC . 'static/css/' . $this->_getFilename())) {
			$content = new CM_File(DIR_PUBLIC . 'static/css/' . $this->_getFilename());
		} else {
			throw new CM_Exception_Invalid('Invalid filename: `' . $this->_getFilename() . '`');
		}
		return $content;
	}
}
