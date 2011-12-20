<?php

class CM_RequestHandler_Resource_CSS extends CM_RequestHandler_Resource_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'text/css');
		$this->enableCache();

		if ($this->_getFilename() == 'library.css') {
			$content = '';
			foreach (rglob('*.css', DIR_PUBLIC . 'static/css/library/') as $path) {
				$content .= new CM_File($path);
			}
		} elseif ($this->_getFilename() == 'sk.css') {
			$presets = new CM_Css($this->getRender()->getFileThemed('presets.style')->read(), $this->getRender());
			$content = new CM_Css($this->getRender()->getFileThemed('layout.style')->read(), $this->getRender(), $presets);

			$themePath = $this->getRender()->getThemeDir(true);

			foreach (rglob('*.css', $themePath . 'css/') as $path) {
				$file = new CM_File($path);
				$content .= new CM_Css($file->read(), $this->getRender(), $presets);
			}

			$namespaces = self::getSite()->getNamespaces();

			$components = array();
			foreach ($namespaces as $namespace) {
				$components = array_merge($components, rglob('*.php', DIR_INTERNALS . $namespace . '/Component/'));
			}

			$classes = $this->_getClasses($components);

			foreach ($classes as $class) {
				if (!preg_match('/^(\w+)_Component_(.+)$/', $class['name'], $matches)) {
					throw new CM_Exception("Cannot detect namespace from component's class-name");
				}

				$basePath = $this->getRender()->getThemeDir(true, null, $matches[1]);

				foreach (rglob('*.style', $basePath . 'Component/' . $matches[2]) as $path) {

					if (preg_match('~' . $themePath . '(Component/(.+?)/(.+)\.style)~', $path, $match)) {
						$prefix = '.' . $class['name'];

						if ($match[3] != 'default' && strpos($match[3], '/') === false) {
							$prefix .= '.' . $match[3];
						}

						$file = new CM_File($path);
						$content .= new CM_Css($file->read(), $this->getRender(), $presets, $prefix);
					}
				}
			}
		} elseif (file_exists(DIR_PUBLIC . 'static/css/' . $this->_getFilename())) {
			$content = new CM_File(DIR_PUBLIC . 'static/css/' . $this->_getFilename());
		} else {
			throw new CM_Exception_Invalid('Invalid filename');
		}
		return $content;
	}
}
