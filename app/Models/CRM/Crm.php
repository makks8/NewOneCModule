<?php

namespace App\Models\CRM;

use App\Models\Client;
use App\Models\Events\EventHandler;
use App\Models\OneC;
use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class Crm extends Model
{
    use ModelTrait;

    protected $fillable = [
        'name',
        'client_id',
        'data'
    ];
    protected $table = 'crm';

    private $data;
    private EntityBehavior $entityBehavior;

    public function __construct(array $attributes = [])
    {
        parent::__construct($this->prepareAttributesArr($attributes));
    }

    public static function startSync()
    {
        EventHandler::synchronizeEntity(get_called_class());
    }


    public function sendToOneC()
    {
        $data = $this->getOneCParams();
        $response = OneC::request($this->name, $data);
        $this->guid = $response['guid'];
        $this->save();
    }

    public static function sendToCrm()
    {
        $data = OneC::getData();
        $guid = $data['GUID'];
        $description = empty($data['NAME']) ? $data['TITLE'] : $data['NAME'];

        $entity = self::getByGUID($guid);
        $entity->data = $data;
        $entity->description = $description;

        $crmId = $entity->entityBehavior->sendToCrm($entity);
        if (!$entity->exists) {
            $entity->crm_id = $crmId;
            $entity->guid = $guid;
        }
        $entity->save();
    }

    #region get functions

    /**
     * @param string $guid
     * @return mixed|Crm
     */
    public static function getByGuid(string $guid)
    {
        /** @var Crm $class */
        $class = get_called_class();
        return $class::query()->where(['guid' => $guid])->firstOrNew();
    }

    /**
     * @param string $crmID
     * @return Crm
     */
    public static function getById(string $crmID): Crm
    {
        $entityName = basename(get_called_class());
        /** @var Crm $entity */
        $entity = self::query()->where(['name' => $entityName, 'crm_id' => $crmID])->firstOrNew();
        if (!$entity->exists) {
            $entity->crm_id = $crmID;
        }
        return $entity;
    }

    public static function getID(string $entityGUID): int
    {
        $entity = self::getByGUID($entityGUID);
        return $entity->crm_id;
    }

    public function getParams(): array
    {
        $params = ['FIELDS' => $this->data];
        if ($this->exists) $params['id'] = $this->crm_id;
        return $params;
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

    #endregion

    protected function setEntityBehavior(EntityBehavior $entityBehavior): void
    {
        $this->entityBehavior = $entityBehavior;
    }

    private function prepareAttributesArr($attributes = [])
    {
        return array_merge(
            $attributes,
            [
                'name' => basename(get_called_class()),
                'client_id' => Client::getID()
            ]
        );

    }
}
