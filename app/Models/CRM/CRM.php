<?php

namespace App\Models\CRM;

use App\Models\Client;
use App\Models\Event;
use App\Models\OneC;
use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Mixed_;


class CRM extends Model
{
    use ModelTrait;

    private $data;
    protected $table = 'crm';

    private EntityBehavior $entityBehavior;

    public function __construct($entityBehavior)
    {
        $this->name = basename(get_called_class());
        $this->client_id = Client::getClientId();
        $this->entityBehavior = $entityBehavior;
        $this->data = request();
        parent::__construct();
    }

    /**
     * @param string $guid
     * @return mixed|CRM
     */
    public static function getByGuid(string $guid)
    {
        /** @var CRM $class */
        $class = get_called_class();
        return $class::query()->where(['guid' => $guid])->firstOrNew();
    }

    /**
     * @param string $crmID
     * @return CRM
     */
    public static function getByID(string $crmID): CRM
    {
        $entityName = basename(get_called_class());
        /**@var CRM $entity */
        $entity = self::query()->where(['name' => $entityName, 'crm_id' => $crmID])->firstOrNew();
        if (!$entity->exists) $entity->sendToOneC();
        return $entity;
    }

    public static function getID(string $entityGUID): int
    {
        $entity = self::getByGUID($entityGUID);
        return $entity->crm_id;
    }

    public function sendToOneC()
    {
        $data = $this->getOneCParams();
        $response = OneC::request($this->name, $data);
        $this->guid = $response['guid'];
        $this->save();
    }

    public function addEntity()
    {
        $crmID = $this->entityBehavior->add($this);
        if ($this->exists) {
            $this->crm_id = $crmID;
            $this->save();
        }
    }

    public function getParams()
    {
        $params = ['FIELDS' => $this->getData()];
        if ($this->exists) $params['id'] = $this->crm_id;
        return $params;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMethod()
    {
        $method = 'crm.' . $this->name;
        if ($this->exists) return $method . '.update';
        return $method . '.add';
    }

    public function getOneCParams()
    {
        return $this->entityBehavior->getOneCParams($this);
    }


    public function startSync()
    {
        $methods = get_class_methods($this);
        echo nl2br('Сущность ' . $this->name . PHP_EOL . PHP_EOL);

        flush();
        ob_flush();
        foreach ($methods as $method) {
            $result = strpos($method, 'on');
            if ($result !== false && $result == 0) {
                $type = str_replace('on', '', $method);
                $data = $this->checkEvents($type);
                $this->$method($data);
                echo '<br>';
                flush();
                ob_flush();
            }
        }
    }

    protected function setEntityBehavior(EntityBehavior $entityBehavior): void
    {
        $this->entityBehavior = $entityBehavior;
    }

    private function checkEvents($method)
    {
        $eventName = 'OnCrm' . $this->name . $method;
        return Event::offlineEventGet($eventName);
    }

}
