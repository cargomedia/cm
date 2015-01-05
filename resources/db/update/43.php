<?php

CM_Model_LanguageKey::deleteByName('First');
CM_Model_LanguageKey::deleteByName('Next');
CM_Model_LanguageKey::deleteByName('Previous');
CM_Model_LanguageKey::deleteByName('Last');

$language = CM_Model_Language::findByAbbreviation('en');
$language->setTranslation('.pagination.first', 'First');
$language->setTranslation('.pagination.next', 'Next');
$language->setTranslation('.pagination.previous', 'Previous');
$language->setTranslation('.pagination.last', 'Last');
