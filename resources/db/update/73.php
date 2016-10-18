<?php

return;

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

if ($mongo->hasIndex('cm_log', ['level' => 1])) {
    $mongo->deleteIndex('cm_log', ['level' => 1]);
}

if (!$mongo->hasIndex('cm_log', ['context.extra.type' => 1, 'createdAt' => 1])) {
    $mongo->createIndex('cm_log', ['context.extra.type' => 1, 'createdAt' => 1], ['background' => true, 'wTimeoutMS' => 0, 'socketTimeoutMS' => -1]);
}
