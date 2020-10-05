<?php

namespace App\Models\CRM;

use App\Models\Client;
use App\Models\OneC;
use Illuminate\Database\Eloquent\Model;

class CRM extends Model
{
    public $name;
    public $description;
    public $guid;
    public $bitrix_id;
    public $client_id;

    private $eventsList;
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
     * @return CRM
     */
    public static function getEntityByGuid(string $guid): CRM
    {
        /** @var CRM $class */
        $class = get_called_class();

        /** @var  CRM $entity */

        $entity = $class::query()->where(['guid' => $guid])->getModel();

    }

    /**
     * @param $bitrixID
     * @return mixed|string
     */
    public static function getEntityByID($bitrixID)
    {
        /**@var CRM $entity */
        $entityName = basename(get_called_class());
        $entity = self::find()->where(['name' => $entityName, 'bitrix_id' => $bitrixID])->one();
        if (empty($entity)) {
            $entity = get_called_class();
            $entity = new $entity;
            $entity->bitrix_id = $bitrixID;
            $entity->sendEntityToOneC();
        }
        return $entity;
    }

    public static function getBitrixID($entityGUID)
    {
        $entity = self::getEntityByGUID($entityGUID);
        return $entity->bitrix_id;
    }

    public function sendEntityToOneC()
    {
        $data = $this->getEntityData();
        $response = OneC::request($this->name, $data);
        $this->guid = $response['guid'];
        $this->save();
    }

    public function addEntity()
    {
        $bitrixID = $this->entityBehavior->add($this);
        if ($this->entityExists()) {
            $this->bitrix_id = $bitrixID;
            $this->save();
        }
    }

    public function entityExists()
    {
        return property_exists($this, 'id');
    }


    public function getParams()
    {
        return $this->entityBehavior->getParams($this);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMethod()
    {
        $entityName = $this->name;
        $method = "crm.$entityName.add";
        if ($this->entityExists()) {
            $method = "crm.$entityName.update";
        }
        return $method;
    }

    public function getEntityData()
    {
        return $this->entityBehavior->getEntityData($this);
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

}
