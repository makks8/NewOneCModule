<?php

namespace App\Models\Lists;

use App\Models\Bitrix;
use App\Models\Client;
use App\Models\CRM\Crm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ListElement extends Model
{
    protected $fillable = [
        'client_id',
        'block_code',
        'element_code',
        'element_guid',
        'name'
    ];

    private array $params;
    private array $requestData;
    private static ListBlock $block;

    public static function create($elementData)
    {
        /** @var ListElement $element */
        $element = ListElement::query()
            ->where([
                'element_guid' => $elementData['FIELDS']['GUID'],
                'block_code' => $elementData['IBLOCK_CODE']
            ])
            ->firstOrNew();
        $element->name = $elementData['FIELDS']['NAME'];
        if (!$element->exists) $element->setProperties($elementData);
        else $element->setParams($elementData['FIELDS']);
        self::setBlock($element);
        $element->add();
    }

    public static function get($where)
    {
        return self::query()->where($where)->first();
    }

    public function getParams()
    {
        return $this->params;
    }

    private function add()
    {
        $method = $this->exists ? 'update' : 'add';
        $params = self::$block->mapFieldsWithParams();
        $response = Bitrix::request('lists.element.' . $method, $params);
        if (!$this->exists && $response) {
            $this->element_id = $response;
        }
        $this->save();
    }

    private function getElementData()
    {
        $method = 'lists.element.get';
        $data = $this->params;
        $response = Bitrix::request($method, $data);
        return $response[0];
    }

    private function setProperties($elementData)
    {
        $blockCode = $elementData['IBLOCK_CODE'];
        $elementsCount = count(ListElement::query()->where(['block_code' => $blockCode])->get());
        $elementsCount++;
        $this->fill([
            'client_id' => Client::getID(),
            'block_code' => $blockCode,
            'element_code' => $blockCode . '_element_' . $elementsCount,
            'element_guid' => $elementData['FIELDS']['GUID'],
        ]);

        $this->setParams($elementData['FIELDS']);
    }

    private function setParams($elementData)
    {

        if (key_exists('CRM_ENTITIES', $elementData)) {
            foreach ($elementData['CRM_ENTITIES'] as $fieldName => $entityData) {
                $entity = Crm::query()->where($entityData)->first();
                $elementData[$fieldName] = $entity->crm_id;
            }
            unset($elementData['CRM_ENTITIES']);
        }

        $this->params = array(
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $this->block_code,
            'ELEMENT_CODE' => $this->element_code,
            'FIELDS' => $elementData
        );
    }

    private static function setBlock($element): void
    {
        if (empty(self::$block) || self::$block->block_code != $element->block_code) {
            self::$block = ListBlock::get($element);
        } else {
            self::$block->setElement($element);
        }
    }
}
