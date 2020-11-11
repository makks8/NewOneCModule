<?php

namespace App\Models\CRM;

use App\Models\Client;
use App\Models\Events\EventHandler;
use App\Models\Lists\ListElement;
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
    private array $params;
    private EntityBehavior $entityBehavior;

    public function __construct(array $attributes = [])
    {
        parent::__construct($this->prepareAttributesArr($attributes));
    }

    public static function startSync()
    {
        EventHandler::synchronizeEntity(get_called_class());
    }

    #region send functions
    public function sendToOneC()
    {
        $data = $this->getOneCParams();
        $response = OneC::request($this->name, $data);

        $this->guid = $response['guid'];
        $this->save();
    }

    public static function sendToCrm($data):self
    {

        $guid = $data['GUID'];
        $entity = self::getByGUID($guid);
        $entity->setParams($data);
        $entity->guid = $guid;

        $crmId = $entity->entityBehavior->sendToCrm($entity);
        if (!$entity->exists) {
            $entity->crm_id = $crmId;
        }

        if (key_exists('NAME', $data)) {
            $description = $data['NAME'];
        } else if (key_exists('TITLE', $data)) {
            $description = $data['TITLE'];
        }

        if (!empty($description)) {
            $entity->description = $description;
        }

        $entity->save();

        return $entity;
    }
    #endregion

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

    public function getParams()
    {
        return $this->params;
    }

    #endregion

    #region set functions
    protected function setEntityBehavior(EntityBehavior $entityBehavior): void
    {
        $this->entityBehavior = $entityBehavior;
    }

    private function setParams($data): void
    {
        $this->params = array();
        if ($this->exists) $this->params['id'] = $this->crm_id;

        if (key_exists('LIST_ELEMENTS', $data)) {
            foreach ($data['LIST_ELEMENTS'] as $fieldName => $fieldData) {
                if (is_array($fieldData)) {
                    foreach ($fieldData as $key => $multipleFieldData) {
                        $listElement = ListElement::get($multipleFieldData);
                        $data[$fieldName][$key] = $listElement->element_id;
                    }
                } else {
                    $listElement = ListElement::get($fieldData);
                    $data[$fieldName] = $listElement->element_id;
                }
            }
            unset($data['LIST_ELEMENTS']);
        }
        $this->params['FIELDS'] = $data;
    }
    #endregion

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
