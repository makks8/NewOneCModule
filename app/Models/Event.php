<?php

namespace App\Models;

use App\Models\Util\Util;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function MongoDB\BSON\toJSON;
use function MongoDB\BSON\toPHP;

class Event extends Model
{
    use HasFactory;

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
        $events = array(
            0 =>
                array(
                    'ID' => '365',
                    'TIMESTAMP_X' => '2020-09-14T09:34:40+03:00',
                    'EVENT_NAME' => 'ONCRMDEALUPDATE',
                    'EVENT_DATA' =>
                        array(
                            'FIELDS' =>
                                array(
                                    'ID' => 49,
                                ),
                        ),
                    'EVENT_ADDITIONAL' =>
                        array(
                            'user_id' => '1',
                        ),
                    'MESSAGE_ID' => '634959bd00d83c392c34d5e804d5682a',
                )
        );

        foreach (/*$response['events']*/ $events as $value) {
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

    public static function hasChanges($entities, $method)
    {
        if (empty($entities)) {
            echo nl2br($method . PHP_EOL . ' Нет изменений на портале.' . PHP_EOL);
            return false;
        } else {
            return true;
        }
    }
}
