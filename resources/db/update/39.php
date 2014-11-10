<?php

$setupScriptClassList = CM_Util::getClassChildren('CM_Provision_Script_Abstract');
foreach ($setupScriptClassList as $setupScriptClass) {
    if (in_array('CM_Provision_Script_IsLoadedOptionTrait', class_uses($setupScriptClass))) {
        /** @var CM_Provision_Script_IsLoadedOptionTrait $setupScript */
        $setupScript = new $setupScriptClass();
        $setupScript->setLoaded(true);
    }
}
