<?php

CM_Model_LanguageKey::deleteByName('First');
CM_Model_LanguageKey::deleteByName('Next');
CM_Model_LanguageKey::deleteByName('Previous');
CM_Model_LanguageKey::deleteByName('Last');

if ($en = CM_Model_Language::findByAbbreviation('en')) {
    $en->setTranslation('.pagination.first', 'First');
    $en->setTranslation('.pagination.next', 'Next');
    $en->setTranslation('.pagination.previous', 'Previous');
    $en->setTranslation('.pagination.last', 'Last');
}

if ($de = CM_Model_Language::findByAbbreviation('de')) {
    $de->setTranslation('.pagination.first', 'Erste');
    $de->setTranslation('.pagination.next', 'Weiter');
    $de->setTranslation('.pagination.previous', 'Zurück');
    $de->setTranslation('.pagination.last', 'Letzte');
}
