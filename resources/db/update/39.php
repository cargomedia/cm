<?php

$setupScriptClassList = CM_Util::getClassChildren('CM_Provision_Script_Abstract');
foreach ($setupScriptClassList as $setupScriptClass) {
    if (in_array('CM_Provision_Script_IsLoadedOptionTrait', class_uses($setupScriptClass))) {
        $optionName = 'SetupScript.' . $setupScriptClass;
        CM_Option::getInstance()->set($optionName, true);
    }
}
