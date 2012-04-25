<?php

function smarty_function_location(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Model_Location $location */
	$location = $params['location'];
	$showLink = !empty($params['link']);
	$parts = array();
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	if ($city = $location->get(CM_Model_Location::LEVEL_CITY)) {
		$part = $city->getName();
		if ($showLink) {
			$part = '<a class="nowrap" href="' . $render->getUrlPage('FB_Page_Users_Search', array('location' => $city)) . '">' . $part . '</a>';
		}
		$parts[] = $part;
	}
	if ($state = $location->get(CM_Model_Location::LEVEL_STATE)) {
		$part = $state->getName();
		if ($showLink) {
			$part = '<a class="nowrap" href="' . $render->getUrlPage('FB_Page_Users_Search', array('location' => $state)) . '">' . $part . '</a>';
		}
		$parts[] = $part;
	}
	if ($country = $location->get(CM_Model_Location::LEVEL_COUNTRY)) {
		$part = $country->getName();
		if ($showLink) {
			$part = '<a class="nowrap" href="' . $render->getUrlPage('FB_Page_Users_Search', array('location' => $country)) . '">' . $part . '</a>';
		}
		$parts[] = $part;
	}
	if (3 == count($parts)) {
		if ('US' == $location->getAbbreviation(CM_Model_Location::LEVEL_COUNTRY)) {
			unset($parts[2]);
		} else {
			unset($parts[1]);
		}
	}
	$html = implode(', ', $parts);

	if ($country = $location->get(CM_Model_Location::LEVEL_COUNTRY)) {
		$html .= ' <img class="flag" src="' . $render->getUrlStatic('img/flags/' . strtolower($country->getAbbreviation()) . '.png') . '" />';
	}
	return $html;
}
