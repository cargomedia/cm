<?php

class CM_Clockwork_Storage_MongoDB extends CM_Clockwork_Storage_Abstract {

    public function getStatus(CM_Clockwork_Event $event) {
        $mongoClient = CM_Service_Manager::getInstance()->getMongoDb();
        $result = $mongoClient->findOne('cm_clockwork',
            ['context' => $this->_context, 'events.name' => $event->getName()],
            ['_id' => 0, 'events' => 1],
            [['$unwind' => '$events'], ['$match' => ['events.name' => $event->getName()]]]
        );
        $status = new CM_Clockwork_Event_Status();
        if (null !== $result) {
            $result = $result['events'];
            /** @var MongoDate|null $lastRuntime */
            $lastRuntime = $result['lastRuntime'];
            if (null !== $lastRuntime) {
                $status->setLastRuntime($lastRuntime->toDateTime());
            }
            /** @var MongoDate $lastStartTime */
            $lastStartTime = $result['lastStartTime'];
            if (null !== $lastStartTime) {
                $status->setLastStartTime($lastStartTime->toDateTime());
            }
            $status->setRunning($result['running']);
        }
        // TODO: remove debug-code
        (new CM_File(DIR_ROOT . '/storage-log.txt'))->appendLine("Loading event: {$event->getName()} {$status}");

        return $status;
    }

    public function setStatus(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
        $mongoClient = CM_Service_Manager::getInstance()->getMongoDb();
        $lastRuntime = $status->getLastRuntime();
        $lastRuntime = (null !== $lastRuntime) ? new MongoDate($lastRuntime->getTimestamp()) : null;
        $lastStartTime = $status->getLastStartTime();
        $lastStartTime = (null !== $lastStartTime) ? new MongoDate($lastStartTime->getTimestamp()) : null;

        // TODO: remove debug-code
        (new CM_File(DIR_ROOT . '/storage-log.txt'))->appendLine("Saving event: {$event->getName()} {$status}");

        $eventData = [
            'name'          => $event->getName(),
            'lastRuntime'   => $lastRuntime,
            'lastStartTime' => $lastStartTime,
            'running'       => $status->isRunning(),
        ];

        $updated = (boolean) $mongoClient->update('cm_clockwork',
            [
                'context'     => $this->_context,
                'events.name' => $event->getName(),
            ], [
                '$set' => [
                    'events.$.lastRuntime'   => $lastRuntime,
                    'events.$.lastStartTime' => $lastStartTime,
                    'events.$.running'       => $status->isRunning(),
                ],
            ]
        );
        if (!$updated) {
            $mongoClient->update('cm_clockwork',
                [
                    'context' => $this->_context,
                ], [
                    '$push' => [
                        'events' => $eventData,
                    ],
                ], [
                    'upsert' => true,
                ]
            );
        }
    }
}
