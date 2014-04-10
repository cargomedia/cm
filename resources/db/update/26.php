<?php

if (!CM_Db_Db::existsColumn('cm_languageKey', 'variables')) {
    CM_Db_Db::exec("ALTER TABLE `cm_languageKey` ADD `variables` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL AFTER name");
}

$results = CM_Db_Db::select('cm_languageKey_variable', '*')->fetchAll();

$variableList = array();
foreach ($results as $result) {
    $variableList[$result['languageKeyId']][] = $result['name'];
}

foreach ($variableList as $languageKeyId => $variables) {
    CM_Db_Db::update('cm_languageKey', array('variables' => json_encode($variables)), array('id' => $languageKeyId));
}

CM_Db_Db::exec('DROP TABLE `cm_languageKey_variable`');
