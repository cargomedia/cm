<?php

$filename = md5('app-maintenance-local');
$file = new CM_File("clockwork/{$filename}.json", $this->getServiceManager()->getFilesystems()->getData());
$file->delete();

$filename = md5('search-maintenance');
$file = new CM_File("clockwork/{$filename}.json", $this->getServiceManager()->getFilesystems()->getData());
$file->delete();
