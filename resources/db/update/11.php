<?php

$nameList[] = 'Your browser is no longer supported';
$nameList[] = 'We recommend upgrading to the latest Internet Explorer, Google Chrome, Firefox, or Opera. Click here for <a href="{$url}">more information</a>';
$nameList[] = 'If you are using IE 9 or later, make sure you <a href="{$url}">turn off "Compatibility View"</a>';

foreach ($nameList as $name) {
	$id = CM_Db_Db::select('cm_languageKey', 'id', array('name' => $name))->fetchColumn();

	if ($id) {
		CM_Db_Db::update('cm_languageKey', array('name' => $name . '.'), array('id' => $id));
		CM_Db_Db::exec('UPDATE `cm_languageValue` SET `value`= CONCAT(`value`, ".") WHERE `languageKeyId` = ' . $id);
	}
}
