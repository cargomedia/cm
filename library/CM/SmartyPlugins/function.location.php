<?php

function smarty_function_location(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    /** @var CM_Model_Location $location */
    $location = $params['location'];
    $distanceFrom = isset($params['distanceFrom']) ? $location->getDistance($params['distanceFrom']) : null;

    $parts = array();
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $cityName = $location->getName(CM_Model_Location::LEVEL_CITY);
    if (strlen($cityName)) {
        $parts[] = $cityName;
    }
    $stateName = $location->getName(CM_Model_Location::LEVEL_STATE);
    if (strlen($stateName)) {
        $parts[] = $stateName;
    }
    $countryName = $location->getName(CM_Model_Location::LEVEL_COUNTRY);
    if ($countryName) {
        $parts[] = $countryName;
    }
    $countryAbbreviation = $location->getAbbreviation(CM_Model_Location::LEVEL_COUNTRY);
    if (3 == count($parts)) {
        if ('US' === $countryAbbreviation) {
            unset($parts[2]);
        } else {
            unset($parts[1]);
        }
    }
    $html = implode(', ', $parts);

    if (strlen($countryAbbreviation)) {
        $html .= ' <img class="flag" src="' . $render->getUrlResource('layout', 'img/flags/' . strtolower($countryAbbreviation) . '.png') . '" />';
    }

    if (null !== $distanceFrom && $distanceFrom < 100 * 1000) {
        $template->smarty->loadPlugin('smarty_function_distance');
        $distance = smarty_function_distance(array('distance' => $distanceFrom), $template);
        $html .= ' <small class="distance" title="' . $render->getTranslation('Distance from your location:') . ' ' . $distance . '">' . $distance .
            '</small>';
    }

    return $html;
}
