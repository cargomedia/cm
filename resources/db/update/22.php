<?php

$language = CM_Model_Language::findByAbbreviation('en');
$language->setTranslation('Some unexpected connection problem occurred.', 'Some unexpected connection problem occurred.');
