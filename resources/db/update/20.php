<?php

$isInnoDb = function ($tableName) {
    $result = CM_Db_Db::exec("SHOW TABLE STATUS WHERE name = '" . $tableName . "'")->fetch();
    return $result['Engine'] === 'InnoDB';
};

if (!$isInnoDb('cm_streamChannel_video')) {
    CM_Db_Db::exec("DELETE v FROM `cm_streamChannel_video` AS v LEFT JOIN `cm_streamChannel` AS s using(`id`) where `s`.`id` IS NULL");
    CM_Db_Db::exec("ALTER TABLE `cm_streamChannel_video` ENGINE = INNODB");
    CM_Db_Db::exec("ALTER TABLE `cm_streamChannel_video` ADD CONSTRAINT `cm_streamChannel_video-cm_streamChannel` FOREIGN KEY (`id`) REFERENCES `cm_streamChannel` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;");
}
