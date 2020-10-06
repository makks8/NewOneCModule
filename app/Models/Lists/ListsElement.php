<?php

namespace App\Models\Lists;

use App\Models\Bitrix;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class ListsElement extends Model
{
    private array $params;
    private array $requestData;

    public static function create($data)
    {
        /** @var ListsElement $element */
        $element = ListsElement::query()
            ->where([
                'element_guid' => $data['FIELDS']['GUID'],
                'block_code' => $data['IBLOCK_CODE']
            ])
            ->firstOrNew();
        $element->requestData = $data;
        if ($element->exists) $element->setProperties();
        else $element->setParams();
        $element->add();
    }

    public static function get($data)
    {
        return self::query()->where([
            'bitrix_id' => $data['ELEMENT_ID'],
            'block_code' => $data['BLOCK_CODE']
        ])->first();
    }

    public function getParams()
    {
        return $this->params;
    }

    private function add()
    {
        $method = $this->exists ? 'update' : 'add';
        $params = ListsBlock::get($this)->mapFieldsWithParams();
        $response = Bitrix::request('lists.element.' . $method, $params);
        if (!empty($response)) {
            $this->save();
        }
    }

    private function getElementData()
    {
        $method = 'lists.element.get';
        $data = $this->params;
        $response = Bitrix::request($method, $data);
        return $response[0];
    }

    private function setProperties()
    {
        $blockCode = $this->requestData['IBLOCK_CODE'];
        $elementsCount = count(ListsElement::query()->where(['block_code' => $blockCode])->get());
        $elementsCount++;
        $this->fill([
            'client_id' => Client::getClientId(),
            'block_code' => $blockCode,
            'element_code' => $blockCode . '_element_' . $elementsCount,
            'element_guid' => $this->requestData['FIELDS']['GUID'],
            'name' => $this->requestData['FIELDS']['NAME']
        ]);

        $this->setParams();
    }

    private function setParams()
    {
        $this->params = array(
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $this->block_code,
            'ELEMENT_CODE' => $this->element_code,
            'FIELDS' => $this->requestData['FIELDS']
        );
    }
}
