<?php

$language = CM_Model_Language::findByAbbreviation('en');
$language->setTranslation('You can only select {$cardinality} items.', 'You can only select {$cardinality} items.', array('cardinality'));
