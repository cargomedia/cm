<?php

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

if (!$mongo->hasIndex('cm_log', ['context.extra.type' => 1, 'level' => 1])) {
    $mongo->createIndex('cm_log', ['context.extra.type' => 1, 'level' => 1], ['background' => true]);
}
