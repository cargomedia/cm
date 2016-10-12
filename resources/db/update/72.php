<?php

return;

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

if (!$mongo->hasIndex('cm_log', ['level' => 1])) {
    $mongo->createIndex('cm_log', ['level' => 1], ['background' => true, 'wTimeoutMS' => 0, 'socketTimeoutMS' => -1]);
}
