<?php

abstract class CM_RenderAdapter_Abstract {

	public function __construct(CM_Render $render, $object) {
		$this->_render = $render;
		$this->_object = $object;
	}

	/**
	 * @return CM_Render
	 */
	public function getRender() {
		return $this->_render;
	}

	/**
	 * @return Smarty
	 */
	public function getLayout() {
		return $this->_render->getLayout();
	}

	/**
	 * Return tpl path
	 *
	 * First try theme for current component
	 * try all themes
	 * Then try parents -> for all themes again
	 *
	 * @param string $tplName
	 * @return string
	 * @throws CM_Exception
	 */
	protected function _getTplPath($tplName = null) {
		$tplPath = '';
		$className = get_class($this->_getObject());

		while ($tplPath == '') {
			// Namespace_ObjectType_Name
			preg_match('/^([a-zA-Z]+)_([a-zA-Z]+)_(.+)$/', $className, $matches);

			$obj = $matches[2];

			if ($tplName) {
				$tpl = $obj . DIRECTORY_SEPARATOR . $matches[3] . DIRECTORY_SEPARATOR . $tplName;
			} else {
				$tpl = $obj . DIRECTORY_SEPARATOR . $matches[3] . '.tpl';
			}

			try {
				$tplPath = $this->getRender()->getLayoutPath($tpl, false, $matches[1]);
			} catch (CM_Exception_Invalid $e) {
				// No path found -> loads parent
				$className = get_parent_class($className);
			}
			if (empty($className)) {
				throw new CM_Exception('Cannot find template `' . $tplName . '` for `' . get_class($this->_getObject()) . '`');
			}
		}

		return $tplPath;
	}

	protected function _getObject() {
		return $this->_object;
	}

	abstract public function fetch(array $params = array());

	public function createTemplate($tplPath) {
		return $this->getLayout()->createTemplate($tplPath);
	}

	public function getTemplate($tplName = null) {
		return $this->createTemplate($this->_getTplPath($tplName));
	}
}
