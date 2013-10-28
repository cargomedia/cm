<?php

function smarty_function_select(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$htmlAttributes = array('id', 'name', 'class');

	$label = null;
	if (isset($params['label'])) {
		$label = (string) $params['label'];
	}

	$optionList = array();
	if (isset($params['optionList'])) {
		$optionList = $params['optionList'];
	}

	if (isset($params['selectedValue'])) {
		$selectedValue = $params['selectedValue'];
	}

	$translate = !empty($params['translate']);

	$translatePrefix = '';
	if (isset($params['translatePrefix'])) {
		$translatePrefix = (string) $params['translatePrefix'];
	}

	$placeholder = null;
	if (isset($params['placeholder'])) {
		if (is_string($params['placeholder'])) {
			$placeholder = $params['placeholder'];
		} else {
			$placeholder = ' -' . $render->getTranslation('Select') . '- ';
		}
	}

	if (isset($selectedValue)) {
		$selectedLabel = $optionList[$selectedValue];
	} elseif ($placeholder) {
		$selectedLabel = $placeholder;
	} else {
		$selectedLabel = reset($optionList);
	}

	$html = '<div class="select-wrapper">';

	$html .= '<select';
	foreach ($htmlAttributes as $name) {
		if (isset($params[$name])) {
			$html .= ' ' . $name . '="' . $params[$name] . '"';
		}
	}
	$html .= '>';
	if (null !== $placeholder) {
		$html .= '<option';
		if (!isset($selectedValue)) {
			$html .= ' selected';
		}
		$html .= ' value="">' . $placeholder . '</option>';
	}

	$template->smarty->loadPlugin('smarty_modifier_escape');
	foreach ($optionList as $itemValue => $itemLabel) {
		$html .= '<option value="' . smarty_modifier_escape($itemValue) . '"';
		if (isset($selectedValue) && $itemValue === $selectedValue) {
			$html .= ' selected="selected"';
		}
		$html .= '>';
		if ($translate) {
			$html .= $render->getTranslation($translatePrefix . $itemLabel, array());
		} else {
			$html .= smarty_modifier_escape($itemLabel);
		}
		$html .= '</option>';
	}
	$html .= '</select>';
	$html .= '<div class="button button-default hasLabel hasIconRight" type="button" value="Month" ><span class="label">' . $selectedLabel . '</span><span class="icon icon-arrow-down"></span></div>';
	$html .= '</div>';
	return $html;
}
