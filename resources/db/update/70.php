<?php

return;

$mongo = CM_Service_Manager::getInstance()->getMongoDb();

$mongo->update(
    'cm_log',
    ['context.extra.type' => ['$exists' => false]],
    ['$set' => ['context.extra.type' => CM_Log_Handler_MongoDb::DEFAULT_TYPE]],
    ['multiple' => true, 'wTimeoutMS' => 0, 'socketTimeoutMS' => -1]
);
