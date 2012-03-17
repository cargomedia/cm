<?php

abstract class CM_RenderAdapter_Abstract {
	/**
	 * @var CM_Render
	 */
	private $_render;

	/**
	 * @var CM_Renderable_Abstract
	 */
	private $_renderable;

	/**
	 * @param CM_Render $render
	 * @param		   $renderable
	 */
	public function __construct(CM_Render $render, CM_Renderable_Abstract $renderable) {
		$this->_render = $render;
		$this->_renderable = $renderable;
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
	 * @param string|null $tplName
	 * @return string
	 * @throws CM_Exception
	 */
	protected function _getTplPath($tplName = null) {
		foreach ($this->_getRenderable()->getClassHierarchy() as $className) {
			if (!preg_match('/^([a-zA-Z]+)_([a-zA-Z]+)_(.+)$/', $className, $matches)) {
				throw new CM_Exception('Cannot detect namespace/renderable-class/renderable-name for `' . $className . '`.');
			}
			if ($tplName) {
				$tpl = $matches[2] . DIRECTORY_SEPARATOR . $matches[3] . DIRECTORY_SEPARATOR . $tplName;
			} else {
				$tpl = $matches[2] . DIRECTORY_SEPARATOR . $matches[3] . '.tpl';
			}
			if ($tplPath = $this->getRender()->getLayoutPath($tpl, false, $matches[1], false)) {
				return $tplPath;
			}
		}

		throw new CM_Exception('Cannot find template `' . $tplName . '` for `' . get_class($this->_getRenderable()) . '`.');
	}

	/**
	 * @return CM_Renderable_Abstract
	 */
	protected function _getRenderable() {
		return $this->_renderable;
	}

	/**
	 * @param array $params
	 * @return string
	 */
	abstract public function fetch(array $params = array());

	/**
	 * @param string|null $tplName
	 * @return Smarty_Internal_Template
	 */
	public function getTemplate($tplName = null) {
		return $this->getLayout()->createTemplate($this->_getTplPath($tplName));
	}
}
