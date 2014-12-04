<?php

require_once 'function.distance.php';
require_once 'function.locationFlag.php';

function smarty_function_location(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Model_Location $location */
    $location = $params['location'];
    $distanceFrom = isset($params['distanceFrom']) ? $location->getDistance($params['distanceFrom']) : null;

    $parts = array();
    /** @var CM_Frontend_Render $render */
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
        $parts[] = $countryName . smarty_function_locationFlag(array('location' => $location), $template);
    }
    $html = implode(', ', $parts);

    if (null !== $distanceFrom && $distanceFrom < 100 * 1000) {
        $distance = smarty_function_distance(array('distance' => $distanceFrom), $template);
        $distanceTitle = $render->getTranslation('Distance from your location:') . ' ' . $distance;
        $html .= '<small class="distance" title="' . $distanceTitle . '">' . $distance . '</small>';
    }

    return '<span class="function-location">' . $html . '</span>';
}
