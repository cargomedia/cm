<?php

$language = CM_Model_Language::findByAbbreviation('en');
$language->setTranslation('{$file} has invalid extension. Only {$extensions} are allowed.', '{$file} has invalid extension. Only {$extensions} are allowed.', array('file', 'extensions'));
