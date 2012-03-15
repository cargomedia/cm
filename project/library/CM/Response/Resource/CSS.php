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
			$presets = new CM_Css($this->getRender()->getFileThemed('presets.style')->read(), $this->getRender());
			$content = new CM_Css($this->getRender()->getFileThemed('layout.style')->read(), $this->getRender(), $presets);

			foreach ($this->getRender()->getSite()->getThemes() as $theme) {
				foreach (CM_Util::rglob('*.css', $this->getRender()->getThemeDir(true, $theme) . 'css/') as $path) {
					$file = new CM_File($path);
					$content .= new CM_Css($file->read(), $this->getRender(), $presets);
				}
			}

			$components = array();
			foreach (self::getSite()->getNamespaces() as $namespace) {
				$components = array_merge($components, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Component/'));
			}

			foreach ($this->_getClasses($components) as $class) {
				if (!preg_match('#^(\w+)_Component_(.+)$#', $class['name'], $matches)) {
					throw new CM_Exception("Cannot detect namespace from component's class-name");
				}
				$namespace = $matches[1];
				$componentName = $matches[2];
				$relativePaths = array();
				foreach ($this->getRender()->getSite()->getThemes() as $theme) {
					$basePath = $this->getRender()->getThemeDir(true, $theme, $namespace) . 'Component/' . $componentName . '/';
					foreach (CM_Util::rglob('*.style', $basePath) as $path) {
						$relativePaths[] = preg_replace('#^' . $basePath . '#', '', $path);
					}
				}
				foreach (array_unique($relativePaths) as $path) {
					$prefix = '.' . $class['name'];
					if ($path != 'default.style' && strpos($path, '/') === false) {
						$prefix .= '.' . preg_replace('#.style$#', '', $path);
					}

					$file = $this->getRender()->getFileThemed('Component/' . $componentName . '/' . $path, $namespace);
					$content .= new CM_Css($file->read(), $this->getRender(), $presets, $prefix);
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
