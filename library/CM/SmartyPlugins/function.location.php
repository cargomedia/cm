<?php

require_once 'function.distance.php';
require_once 'function.locationFlag.php';

function smarty_function_location(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Model_Location $location */
    $location = $params['location'];
    /** @var CM_Model_Location|null $distanceFrom */
    $distanceFrom = isset($params['distanceFrom']) ? $location->getDistance($params['distanceFrom']) : null;
    /** @var Closure|null $partLabeler */
    $partLabeler = isset($params['partLabeler']) ? $params['partLabeler'] : null;
    /** @var Closure|null $flagLabeler */
    $flagLabeler = isset($params['flagLabeler']) ? $params['flagLabeler'] : null;

    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $levelList = [
        CM_Model_Location::LEVEL_CITY,
        CM_Model_Location::LEVEL_STATE,
        CM_Model_Location::LEVEL_COUNTRY,
    ];

    $partNameList = [];
    foreach ($levelList as $level) {
        if (null !== $location->getId($level)) {
            if (null !== $partLabeler) {
                $partNameList[] = $partLabeler($location->get($level), $location);
            } else {
                $partNameList[] = $location->getName($level);
            }
        }
    }

    $partNameList = Functional\filter($partNameList, function ($partName) {
        return !empty($partName);
    });
    $html = implode(', ', $partNameList);

    if (null !== $location->getId(CM_Model_Location::LEVEL_COUNTRY)) {
        if (null !== $flagLabeler) {
            $html .= $flagLabeler($location->get(CM_Model_Location::LEVEL_COUNTRY), $location);
        } else {
            $html .= smarty_function_locationFlag(array('location' => $location), $template);
        }
    }

    if (null !== $distanceFrom && $distanceFrom < 100 * 1000) {
        $distance = smarty_function_distance(array('distance' => $distanceFrom), $template);
        $distanceTitle = $render->getTranslation('Distance from your location:') . ' ' . $distance;
        $html .= '<small class="distance" title="' . $distanceTitle . '">' . $distance . '</small>';
    }

    return '<span class="function-location">' . $html . '</span>';
}
