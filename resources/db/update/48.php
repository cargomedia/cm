<?php

$rowList = CM_Db_Db::select('cm_log', '*')->fetchAll();
foreach ($rowList as $row) {
    $metaInfo = @unserialize($row['metaInfo']);

    if (is_array($metaInfo)) {
        $variableInspector = new CM_Debug_VariableInspector();
        $metaInfo = Functional\map($metaInfo, function ($value) use($variableInspector) {
            return $variableInspector->getDebugInfo($value, ['recursive' => true]);
        });
    } else {
        $metaInfo = null;
    }

    CM_Db_Db::update('cm_log', ['metaInfo' => serialize($metaInfo)], ['id' => $row['id']]);
}
