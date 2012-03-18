<?php

class CM_Frontend {
	protected $_onloadHeaderJs = '';
	protected $_onloadPrepareJs = '';
	protected $_onloadJs = '';
	protected $_onloadReadyJs;

	/**
	 * Concatenate a javascript code $line with $var by reference.
	 *
	 * @param string  $line
	 * @param string  &$var	 reference
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

	/**
	 * @param string $lang_addr
	 * @param string $frontend_lang_addr OPTIONAL
	 */
	public function registerLanguageValue($lang_addr, $frontend_lang_addr = null) {
		$value = CM_Language::text($lang_addr);

		if (!isset($frontend_lang_addr)) {
			$frontend_lang_addr = $lang_addr;
		}
		if (substr($frontend_lang_addr, 0, 1) != '%') {
			$frontend_lang_addr = '%' . $frontend_lang_addr;
		}

		$this->assignLanguageValue($value, $frontend_lang_addr);
	}

	public function assignLanguageValue($value, $frontend_lang_addr) {
		$this->onloadHeaderJs("cm.language.set('$frontend_lang_addr'," . json_encode($value) . ')');
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
	 * @param CM_Form_Abstract	  $form
	 * @param CM_Component_Abstract $component
	 */
	public function registerForm(CM_Form_Abstract $form, CM_Component_Abstract $component) {
		$className = get_class($form);

		$component->forms[] = $form;

		$field_list = array();
		foreach ($form->getFields() as $field_key => $field) {
			$field_list[] =
					'"' . $field_key . '":{"className":"' . get_class($field) . '","options":' . CM_Params::encode($field->getOptions(), true) . '}';
		}
		$action_list = array();
		foreach ($form->getActions() as $action_name => $action) {
			$action_list[] = '"' . $action_name . '":' . $action->js_presentation();
		}
		$default_action = $form->getActionDefaultName();

		$auto_var = 'cm.views["' . $form->getAutoId() . '"]';
		$js = $auto_var . ' = new ' . $className . '({';
		$js .= 'el:$("#' . $form->getAutoId() . '").get(0),';
		$js .= 'parent:cm.views["' . $component->getAutoId() . '"],';
		$js .= 'name:"' . $form->getName() . '",';
		$js .= 'fields:{' . implode(',', $field_list) . '},';
		$js .= 'actions:{' . implode(',', $action_list) . '},';
		$js .= 'default_action:"' . $default_action . '"';
		$js .= '});' . PHP_EOL;

		$form->frontend_data['init_js'] = $js;
	}

	/**
	 * @param CM_Component_Abstract $component
	 * @param string				$parentAutoId OPTIONAL
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

		foreach ($component->forms as $form) {
			$cmpJs .= $form->frontend_data['init_js'] . PHP_EOL;
		}

		$this->onloadPrepareJs($cmpJs, true);
		$this->onloadJs($handler->compile_js($auto_var));
	}

	/**
	 * @param CM_Page_Abstract $page
	 */
	public function registerPage(CM_Page_Abstract $page) {
		$auto_var = 'cm.views["' . $page->getAutoId() . '"]';
		$js = '';
		$js .= $auto_var . ' = new CM_View_Abstract({';
		$js .= 'el:$("#' . $page->getAutoId() . '").get(0)';
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
