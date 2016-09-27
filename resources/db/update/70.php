<?php

if (CM_Db_Db::existsColumn('cm_cli_command_manager_process', 'hostId')) {
    CM_Db_Db::exec('
        ALTER TABLE `cm_cli_command_manager_process`
          DROP KEY `hostId`,
          DROP COLUMN `hostId`,
          ADD COLUMN `machineId` varchar(100) NOT NULL AFTER `commandName`,
          ADD KEY `machineId` (`machineId`)
    ');
    CM_Db_Db::delete('cm_cli_command_manager_process');
}
