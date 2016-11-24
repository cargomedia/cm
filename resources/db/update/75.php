<?php

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

if ($mongo->hasIndex('cm_log', ['context.extra.type' => 1, 'level' => 1, 'createdAt' => 1])) {
    $mongo->deleteIndex('cm_log', ['context.extra.type' => 1, 'level' => 1, 'createdAt' => 1]);
}
