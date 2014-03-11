<?php

$english = CM_Model_Language::create('English', 'en', true);
$english->setTranslation('Ok', 'Ok');
$english->setTranslation('Cancel', 'Cancel');
$english->setTranslation('Close', 'Close');
$english->setTranslation('{$label} is required.', '{$label} is required.', array('label'));
$english->setTranslation('Required', 'Required');
$english->setTranslation('Confirmation', 'Confirmation');
$english->setTranslation('.date.month.1', 'January');
$english->setTranslation('.date.month.2', 'February');
$english->setTranslation('.date.month.3', 'March');
$english->setTranslation('.date.month.4', 'April');
$english->setTranslation('.date.month.5', 'May');
$english->setTranslation('.date.month.6', 'June');
$english->setTranslation('.date.month.7', 'July');
$english->setTranslation('.date.month.8', 'August');
$english->setTranslation('.date.month.9', 'September');
$english->setTranslation('.date.month.10', 'October');
$english->setTranslation('.date.month.11', 'November');
$english->setTranslation('.date.month.12', 'December');
$english->setTranslation('.date.timeago.prefixAgo', '');
$english->setTranslation('.date.timeago.prefixFromNow', '');
$english->setTranslation('.date.timeago.suffixAgo', 'ago');
$english->setTranslation('.date.timeago.suffixFromNow', 'from now');
$english->setTranslation('.date.timeago.seconds', 'less than a minute');
$english->setTranslation('.date.timeago.minute', 'about a minute');
$english->setTranslation('.date.timeago.minutes', '{$count} minutes', array('count'));
$english->setTranslation('.date.timeago.hour', 'about an hour');
$english->setTranslation('.date.timeago.hours', '{$count} hours', array('count'));
$english->setTranslation('.date.timeago.day', 'a day');
$english->setTranslation('.date.timeago.days', '{$count} days', array('count'));
$english->setTranslation('.date.timeago.month', 'about a month');
$english->setTranslation('.date.timeago.months', '{$count} months', array('count'));
$english->setTranslation('.date.timeago.year', 'about a year');
$english->setTranslation('.date.timeago.years', '{$count} years', array('count'));
$english->setTranslation('You can only select {$cardinality} items.', 'You can only select {$cardinality} items.', array('cardinality'));
$english->setTranslation('{$file} has an invalid extension. Only {$extensions} are allowed.', '{$file} has an invalid extension. Only {$extensions} are allowed.', array('file',
    'extensions'));
$english->setTranslation('An unexpected connection problem occurred.', 'An unexpected connection problem occurred');

