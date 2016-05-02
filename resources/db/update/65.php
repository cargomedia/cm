<?php

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

if (!$mongo->hasIndex('cm_log', ['level' => 1, 'createdAt' => 1])) {
    $mongo->createIndex('cm_log', ['level' => 1, 'createdAt' => 1], ['background' => true]);
}
