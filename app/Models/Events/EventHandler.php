<?php

namespace App\Models\Events;

use App\Models\Bitrix;
use App\Models\Util\Util;
use function MongoDB\BSON\toJSON;

class EventHandler
{

    public static function eventList($scope)
    {
        return Bitrix::request('events', ['scope' => $scope]);
    }

    public static function offlineEventGet($eventName)
    {
        $data = [
            'filter' => [
                'EVENT_NAME' => $eventName
            ]
        ];
        $response = Bitrix::request('event.offline.get', $data);
        $arrOfDealId = array();
        /*$events = array(
            0 =>
                array(
                    'ID' => '365',
                    'TIMESTAMP_X' => '2020-09-14T09:34:40+03:00',
                    'EVENT_NAME' => 'ONCRMDEALUPDATE',
                    'EVENT_DATA' =>
                        array(
                            'FIELDS' =>
                                array(
                                    'ID' => 60,
                                ),
                        ),
                    'EVENT_ADDITIONAL' =>
                        array(
                            'user_id' => '1',
                        ),
                    'MESSAGE_ID' => '634959bd00d83c392c34d5e804d5682a',
                )
        );*/

        foreach ($response['events'] as $value) {
            $arrOfDealId[] = $value['EVENT_DATA']['FIELDS']['ID'];
        }
        return $arrOfDealId;
    }

    public static function bindEvents($events)
    {
        $eventsAmount = count($events);
        echo 'Количество событий:' . $eventsAmount . PHP_EOL;
        foreach ($events as $currentEvent => $eventName) {

            $response = Bitrix::request('event.bind', ['event' => $eventName, 'event_type' => 'offline']);

            Util::drawPercentage($currentEvent, $eventsAmount);

            if ($response->successful()) {
                $eventDescription = toJSON(($response['result']));
            } else {
                $eventDescription = $response['error_description'];
            }
            echo "Событие $eventName - $eventDescription <br>";
        }
    }

    public static function entityHasChanges($entities, $method)
    {
        if (empty($entities)) {
            echo nl2br($method . PHP_EOL . ' Нет изменений на портале.' . PHP_EOL);
            return false;
        } else {
            return true;
        }
    }

    public static function synchronizeEntity($entity): void
    {
        $methods = get_class_methods($entity);
        $entityName = basename($entity);
        echo nl2br('Сущность ' . $entityName . PHP_EOL . PHP_EOL);

        flush();
        ob_flush();
        foreach ($methods as $method) {
            $result = strpos($method, 'onCrm');
            if ($result !== false && $result == 0) {
                $data = self::checkEvents($entityName, $method);

                if (empty($data)) {
                    echo nl2br($method . PHP_EOL . ' Нет изменений на портале.' . PHP_EOL);
                } else {
                    $entity::$method($data);
                }

                echo '<br>';
                flush();
                ob_flush();
            }
        }
    }

    private static function checkEvents(string $entityName, string $method): array
    {
        $method = str_replace('onCrm', '', $method);
        $eventName = 'OnCrm' . $entityName . $method;
        return EventHandler::offlineEventGet($eventName);
    }
}
