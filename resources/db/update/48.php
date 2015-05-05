<?php

$rowList = CM_Db_Db::select('cm_log', '*')->fetchAll();
foreach ($rowList as $row) {
    $metaInfo = @unserialize($row['metaInfo']);

    if (is_array($metaInfo)) {
        $metaInfo = Functional\map($metaInfo, function ($value) {
            return CM_Util::varDump($value, ['recursive' => true]);
        });
    } else {
        $metaInfo = null;
    }

    CM_Db_Db::update('cm_log', ['metaInfo' => serialize($metaInfo)], ['id' => $row['id']]);
}
