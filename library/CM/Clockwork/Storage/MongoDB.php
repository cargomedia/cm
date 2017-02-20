<?php

class CM_Clockwork_Storage_MongoDB extends CM_Clockwork_Storage_Abstract {

    /** @var CM_Clockwork_Event_Status[] */
    private $_eventList = null;

    public function getStatus(CM_Clockwork_Event $event) {
        if (null === $this->_eventList) {
            $this->fetchData();
        }
        if (!array_key_exists($event->getName(), $this->_eventList)) {
            $this->_eventList[$event->getName()] = new CM_Clockwork_Event_Status();
        }
        $status = $this->_eventList[$event->getName()];

        // TODO: remove debug-code// TODO: remove debug-code
        $time = (new DateTime())->format('H:i:s');
        (new CM_File(DIR_ROOT . '/storage-log.txt'))->appendLine("{$time} Loading event: {$event->getName()} {$status}");

        return $status;
    }

    public function setStatus(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
        $this->_eventList[$event->getName()] = $status;
        $mongoClient = CM_Service_Manager::getInstance()->getMongoDb();
        $lastRuntime = $status->getLastRuntime();
        $lastRuntime = (null !== $lastRuntime) ? new MongoDate($lastRuntime->getTimestamp()) : null;
        $lastStartTime = $status->getLastStartTime();
        $lastStartTime = (null !== $lastStartTime) ? new MongoDate($lastStartTime->getTimestamp()) : null;

        // TODO: remove debug-code
        $time = (new DateTime())->format('H:i:s');
        (new CM_File(DIR_ROOT . '/storage-log.txt'))->appendLine("{$time} Saving event: {$event->getName()} {$status}");

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
                        'events' => [
                            'name'          => $event->getName(),
                            'lastRuntime'   => $lastRuntime,
                            'lastStartTime' => $lastStartTime,
                            'running'       => $status->isRunning(),
                        ],
                    ],
                ], [
                    'upsert' => true,
                ]
            );
        }
    }

    public function fetchData() {
        $mongoClient = CM_Service_Manager::getInstance()->getMongoDb();
        $resultList = $mongoClient->find('cm_clockwork',
            ['context' => $this->_context],
            ['_id' => 0, 'events' => 1],
            [['$unwind' => '$events']]
        );
        $eventList = [];
        foreach ($resultList as $result) {
            $eventData = $result['events'];
            $eventName = $eventData['name'];
            $status = new CM_Clockwork_Event_Status();
            /** @var MongoDate|null $lastRuntime */
            $lastRuntime = $eventData['lastRuntime'];
            if (null !== $lastRuntime) {
                $status->setLastRuntime($lastRuntime->toDateTime());
            }
            /** @var MongoDate $lastStartTime */
            $lastStartTime = $eventData['lastStartTime'];
            if (null !== $lastStartTime) {
                $status->setLastStartTime($lastStartTime->toDateTime());
            }
            $status->setRunning($eventData['running']);
            $eventList[$eventName] = $status;
        }
        $this->_eventList = $eventList;
    }
}
