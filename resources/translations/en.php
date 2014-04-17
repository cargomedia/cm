<?php

return function (CM_Model_Language $language) {
    $language->setTranslation('Ok', 'Ok');
    $language->setTranslation('Cancel', 'Cancel');
    $language->setTranslation('Close', 'Close');
    $language->setTranslation('{$label} is required.', '{$label} is required.', array('label'));
    $language->setTranslation('Required', 'Required');
    $language->setTranslation('Confirmation', 'Confirmation');
    $language->setTranslation('.date.month.1', 'January');
    $language->setTranslation('.date.month.2', 'February');
    $language->setTranslation('.date.month.3', 'March');
    $language->setTranslation('.date.month.4', 'April');
    $language->setTranslation('.date.month.5', 'May');
    $language->setTranslation('.date.month.6', 'June');
    $language->setTranslation('.date.month.7', 'July');
    $language->setTranslation('.date.month.8', 'August');
    $language->setTranslation('.date.month.9', 'September');
    $language->setTranslation('.date.month.10', 'October');
    $language->setTranslation('.date.month.11', 'November');
    $language->setTranslation('.date.month.12', 'December');
    $language->setTranslation('.date.timeago.prefixAgo', '');
    $language->setTranslation('.date.timeago.prefixFromNow', '');
    $language->setTranslation('.date.timeago.suffixAgo', 'ago');
    $language->setTranslation('.date.timeago.suffixFromNow', 'from now');
    $language->setTranslation('.date.timeago.seconds', 'less than a minute');
    $language->setTranslation('.date.timeago.minute', 'about a minute');
    $language->setTranslation('.date.timeago.minutes', '{$count} minutes', array('count'));
    $language->setTranslation('.date.timeago.hour', 'about an hour');
    $language->setTranslation('.date.timeago.hours', '{$count} hours', array('count'));
    $language->setTranslation('.date.timeago.day', 'a day');
    $language->setTranslation('.date.timeago.days', '{$count} days', array('count'));
    $language->setTranslation('.date.timeago.month', 'about a month');
    $language->setTranslation('.date.timeago.months', '{$count} months', array('count'));
    $language->setTranslation('.date.timeago.year', 'about a year');
    $language->setTranslation('.date.timeago.years', '{$count} years', array('count'));
    $language->setTranslation('You can only select {$cardinality} items.', 'You can only select {$cardinality} items.', array('cardinality'));
    $language->setTranslation('{$file} has an invalid extension. Only {$extensions} are allowed.', '{$file} has an invalid extension. Only {$extensions} are allowed.', array('file',
        'extensions'));
    $language->setTranslation('An unexpected connection problem occurred.', 'An unexpected connection problem occurred');
};
