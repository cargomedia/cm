<?php

return;

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

if (!$mongo->hasIndex('cm_log', ['message' => 1])) {
    $mongo->createIndex('cm_log', ['message' => 1], ['background' => true]);
}
