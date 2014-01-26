<?php

class CM_Frontend {

	protected $_onloadHeaderJs = '';
	protected $_onloadPrepareJs = '';
	protected $_onloadJs = '';
	protected $_onloadReadyJs;
	private $_tracking;

	/**
	 * Concatenate a javascript code $line with $var by reference.
	 *
	 * @param string  $line
	 * @param string  &$var     reference
	 * @param boolean $prepend  OPTIONAL
	 */
	public static function concat_js($line, &$var, $prepend = false) {
		$line = trim($line);

		if ($line) {
			if (substr($line, -1) != ';') {
				$line .= ';';
			}
			$line .= "\n";
			if ($prepend) {
				$var = $line . $var;
			} else {
				$var = $var . $line;
			}
		}
	}

	public function clear() {
		$this->_onloadHeaderJs = '';
		$this->_onloadPrepareJs = '';
		$this->_onloadJs = '';
		$this->_onloadReadyJs = '';
	}

	/**
	 * @return CM_Tracking
	 */
	public function getTracking() {
		if (!$this->_tracking) {
			$this->_tracking = CM_Tracking_Abstract::factory();
		}
		return $this->_tracking;
	}

	/**
	 * @param string $jsCode
	 */
	public function onloadHeaderJs($jsCode) {
		self::concat_js($jsCode, $this->_onloadHeaderJs);
	}

	/**
	 * @param string $jsCode
	 * @param bool   $prepend
	 */
	public function onloadPrepareJs($jsCode, $prepend = false) {
		self::concat_js($jsCode, $this->_onloadPrepareJs, $prepend);
	}

	/**
	 * @param string $jsCode
	 */
	public function onloadJs($jsCode) {
		self::concat_js($jsCode, $this->_onloadJs);
	}

	/**
	 * @param string $jsCode
	 */
	public function onloadReadyJs($jsCode) {
		self::concat_js($jsCode, $this->_onloadReadyJs);
	}

	/**
	 * @param CM_Form_Abstract      $form
	 * @param CM_View_Abstract      $parentView
	 */
	public function registerForm(CM_Form_Abstract $form, CM_View_Abstract $parentView) {
		$className = get_class($form);

		$field_list = array();
		foreach ($form->getFields() as $field_key => $field) {
			$field_list[] =
					'"' . $field_key . '":{"className":"' . get_class($field) . '","options":' . CM_Params::encode($field->getOptions(), true) . '}';
		}
		$action_list = array();
		foreach ($form->getActions() as $action_name => $action) {
			$action_list[] = '"' . $action_name . '":' . $action->js_presentation();
		}

		$auto_var = 'cm.views["' . $form->getAutoId() . '"]';
		$js = $auto_var . ' = new ' . $className . '({';
		$js .= 'el:$("#' . $form->getAutoId() . '").get(0),';
		$js .= 'parent:cm.views["' . $parentView->getAutoId() . '"],';
		$js .= 'name:"' . $form->getName() . '",';
		$js .= 'fields:{' . implode(',', $field_list) . '},';
		$js .= 'actions:{' . implode(',', $action_list) . '}';
		$js .= '});' . PHP_EOL;

		$this->onloadPrepareJs($js, true);
	}

	/**
	 * @param CM_Component_Abstract $component
	 * @param string                $parentAutoId OPTIONAL
	 */
	public function registerComponent(CM_Component_Abstract $component, $parentAutoId = null) {
		$auto_var = 'cm.views["' . $component->getAutoId() . '"]';
		$cmpClass = get_class($component);
		$handler = $component->getFrontendHandler();

		$cmpJs = '';
		$cmpJs .= $auto_var . ' = new ' . $cmpClass . '({';
		$cmpJs .= 'el:$("#' . $component->getAutoId() . '").get(0),';
		$cmpJs .= 'params:' . CM_Params::encode($component->getParams()->getAll(), true);
		if ($parentAutoId) {
			$cmpJs .= ',parent:cm.views["' . $parentAutoId . '"]';
		}
		$cmpJs .= '});' . PHP_EOL;

		$this->onloadPrepareJs($cmpJs, true);
		$this->onloadJs($handler->compile_js($auto_var));
	}

	/**
	 * @param CM_Layout_Abstract $layout
	 */
	public function registerLayout(CM_Layout_Abstract $layout) {
		$auto_var = 'cm.views["' . $layout->getAutoId() . '"]';
		$js = '';
		$js .= $auto_var . ' = new ' . get_class($layout) .'({';
		$js .= 'el:$("#' . $layout->getAutoId() . '").get(0)';
		$js .= '});' . PHP_EOL;
		$this->onloadHeaderJs($js, true);
	}

	/**
	 * @return array
	 */
	public function getJs() {
		$return = '';
		self::concat_js($this->_onloadHeaderJs, $return);
		self::concat_js($this->_onloadPrepareJs, $return);
		self::concat_js($this->_onloadJs, $return);
		self::concat_js($this->_onloadReadyJs, $return);
		self::concat_js($this->getTracking()->getJs(), $return);
		return $return;
	}

	/**
	 * Render a script tags with compiled js code.
	 *
	 * @return string
	 */
	public function renderScripts() {
		return '<script type="text/javascript">' . PHP_EOL . '$(function() {' . PHP_EOL . $this->_onloadHeaderJs . PHP_EOL . $this->_onloadPrepareJs .
				PHP_EOL . $this->_onloadJs . PHP_EOL . $this->_onloadReadyJs . PHP_EOL . '});' . PHP_EOL . '</script>' . PHP_EOL;
	}

}
