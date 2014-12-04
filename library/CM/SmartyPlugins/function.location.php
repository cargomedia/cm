<?php

require_once 'function.distance.php';
require_once 'function.locationFlag.php';

function smarty_function_location(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Model_Location $location */
    $location = $params['location'];
    /** @var CM_Model_Location|null $distanceFrom */
    $distanceFrom = isset($params['distanceFrom']) ? $location->getDistance($params['distanceFrom']) : null;
    /** @var Closure|null $partNamer */
    $partNamer = isset($params['partNamer']) ? $params['partNamer'] : null;

    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $levelList = [
        CM_Model_Location::LEVEL_CITY,
        CM_Model_Location::LEVEL_STATE,
        CM_Model_Location::LEVEL_COUNTRY,
    ];

    $partNameList = [];
    foreach ($levelList as $level) {
        if (null !== $partNamer) {
            if ($partLocation = $location->get($level)) {
                $partNameList[] = $partNamer($partLocation);
            }
        } else {
            if ($partName = $location->getName($level)) {
                if (CM_Model_Location::LEVEL_COUNTRY === $level) {
                    $partName .= smarty_function_locationFlag(array('location' => $location), $template);
                }
                $partNameList[] = $partName;
            }
        }
    }

    $html = implode(', ', $partNameList);

    if (null !== $distanceFrom && $distanceFrom < 100 * 1000) {
        $distance = smarty_function_distance(array('distance' => $distanceFrom), $template);
        $distanceTitle = $render->getTranslation('Distance from your location:') . ' ' . $distance;
        $html .= '<small class="distance" title="' . $distanceTitle . '">' . $distance . '</small>';
    }

    return '<span class="function-location">' . $html . '</span>';
}
